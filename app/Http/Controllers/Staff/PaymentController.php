<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Mail\BookingStatusUpdated;
use App\Models\Booking;
use App\Models\Owner;
use App\Models\Payment;
use App\Services\EasypayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        $this->syncPendingPayments();

        $tab = $request->input('tab', 'pendente') === 'pago' ? 'pago' : 'pendente';

        if ($tab === 'pago') {
            $owners = Owner::with([
                'bookings' => fn($q) => $q->where('status', 'aprovado')
                    ->whereHas('payments', fn($q) => $q->where('status', 'pago'))
                    ->with('dog', 'payment'),
            ])->whereHas('bookings', fn($q) => $q->where('status', 'aprovado')
                ->whereHas('payments', fn($q) => $q->where('status', 'pago')))
              ->orderBy('name')
              ->get();
        } else {
            $owners = Owner::with([
                'bookings' => fn($q) => $q->where('status', 'aprovado')
                    ->whereDoesntHave('payments', fn($q) => $q->where('status', 'pago'))
                    ->with('dog', 'payment'),
            ])->whereHas('bookings', fn($q) => $q->where('status', 'aprovado')
                ->whereDoesntHave('payments', fn($q) => $q->where('status', 'pago')))
              ->orderBy('name')
              ->get();
        }

        // Remove owners with no bookings after filtering
        $owners = $owners->filter(fn($o) => $o->bookings->isNotEmpty())->values();

        $counts = [
            'pendente' => Booking::where('status', 'aprovado')
                ->whereDoesntHave('payments', fn($q) => $q->where('status', 'pago'))->count(),
            'pago'     => Booking::where('status', 'aprovado')
                ->whereHas('payments', fn($q) => $q->where('status', 'pago'))->count(),
        ];

        return Inertia::render('Staff/Payments/Index', [
            'owners'    => $owners,
            'tab'       => $tab,
            'counts'    => $counts,
            'isMock'    => empty(config('services.easypay.account_id')),
            'isSandbox' => config('services.easypay.sandbox', true),
        ]);
    }

    public function generate(Request $request, Booking $booking)
    {
        $request->validate([
            'method'       => 'required|in:mbway,multibanco',
            'mbway_phone'  => 'required_if:method,mbway|nullable|string',
        ]);

        // Cancel any existing pending payment for this booking
        Payment::where('booking_id', $booking->id)->whereNotIn('status', ['pago'])->update(['status' => 'expirado']);

        $easypay  = app(EasypayService::class);
        $amount   = $this->calculateAmount($booking);
        $desc     = "Cão: {$booking->dog->name} | " . ucfirst($booking->type);

        if ($request->method === 'mbway') {
            $result = $easypay->createMBWayPayment($request->mbway_phone, $amount, $desc);
        } else {
            $result = $easypay->createMultibancoPayment($amount, $desc);
        }

        if (!$result['success']) {
            return back()->withErrors(['payment' => $result['error']]);
        }

        Payment::create([
            'owner_id'     => $booking->owner_id,
            'booking_id'   => $booking->id,
            'amount'       => $amount,
            'status'       => 'pendente',
            'method'       => $request->method,
            'mbway_phone'  => $request->mbway_phone ?? null,
            'mb_entity'    => $result['mb_entity'] ?? null,
            'mb_reference' => $result['mb_reference'] ?? null,
            'easypay_id'   => $result['easypay_id'],
        ]);

        // Send payment details by email
        $email = $booking->owner->email ?? $booking->owner->user?->email;
        if ($email) {
            Mail::to($email)->send(new \App\Mail\PaymentRequested($booking->load('payment', 'dog', 'owner')));
        }

        return back();
    }

    public function resend(Payment $payment)
    {
        abort_unless($payment->method === 'mbway' && $payment->status === 'pendente', 422);

        $easypay = app(EasypayService::class);
        $easypay->resendMBWay($payment->easypay_id);

        return back();
    }

    private function syncPendingPayments(): void
    {
        $pending = Payment::where('status', 'pendente')
            ->whereNotNull('easypay_id')
            ->where('easypay_id', 'not like', 'mock-%')
            ->get();

        if ($pending->isEmpty()) {
            return;
        }

        $easypay = app(EasypayService::class);

        foreach ($pending as $payment) {
            $status = $easypay->checkPaymentStatus($payment->easypay_id);
            if ($status && $status !== $payment->status) {
                $update = ['status' => $status];
                if ($status === 'pago') {
                    $update['paid_at'] = now();
                }
                $payment->update($update);
            }
        }
    }

    public function check(Payment $payment)
    {
        abort_unless($payment->status === 'pendente', 422);

        $easypay = app(EasypayService::class);
        $status  = $easypay->checkPaymentStatus($payment->easypay_id);

        if ($status && $status !== $payment->status) {
            $update = ['status' => $status];
            if ($status === 'pago') {
                $update['paid_at'] = now();
            }
            $payment->update($update);
        }

        return back();
    }

    public function simulate(Payment $payment)
    {
        abort_unless(config('services.easypay.sandbox', true), 403, 'Apenas disponível em modo sandbox.');
        abort_unless($payment->status === 'pendente', 422);

        $payment->update([
            'status'  => 'pago',
            'paid_at' => now(),
        ]);

        return back();
    }

    private function calculateAmount(Booking $booking): float
    {
        $settings = \App\Models\Setting::all()->keyBy('key');
        $regular  = $booking->is_regular ?? true;

        $amount = match($booking->type) {
            'hotel' => (function () use ($booking, $settings, $regular) {
                $key    = $regular ? 'hotel_noite' : 'hotel_noite_nao_regular';
                $nightly = (float) ($settings[$key]?->value ?? 0);
                $nights  = ($booking->end_date && $booking->start_date)
                    ? max(1, $booking->start_date->diffInDays($booking->end_date))
                    : 1;
                return $nightly * $nights;
            })(),
            'atl' => (float) ($settings[$regular ? 'atl' : 'atl_nao_regular']?->value ?? 0),
            'aula' => (function () use ($booking, $settings) {
                $key = match($booking->subtype) {
                    'domicilio'               => 'aula_domicilio',
                    'grupo'                   => 'aula_grupo',
                    'avaliacao_comportamental' => 'avaliacao_comportamental',
                    default                   => 'aula',
                };
                return (float) ($settings[$key]?->value ?? 0);
            })(),
            'integracao'   => (float) ($settings['integracao']?->value ?? 0),
            'pack_creche'  => (float) ($settings['pack_' . $booking->subtype]?->value ?? 0),
            'pet_sitting'  => (float) ($settings['pet_sitting']?->value ?? 0),
            'dog_walking'  => (float) ($settings['dog_walking']?->value ?? 0),
            'banho'        => (float) ($settings['banho']?->value ?? 0),
            default        => 0.0,
        };

        if ($booking->pet_taxi) {
            $amount += (float) ($settings['pet_taxi']?->value ?? 0);
        }

        return round($amount, 2);
    }
}
