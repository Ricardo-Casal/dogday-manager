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
    public function index(): Response
    {
        $owners = Owner::with([
            'bookings' => fn($q) => $q->where('status', 'aprovado')->with('dog', 'payment'),
        ])->whereHas('bookings', fn($q) => $q->where('status', 'aprovado'))
          ->orderBy('name')
          ->get();

        return Inertia::render('Staff/Payments/Index', [
            'owners' => $owners,
            'isMock' => empty(config('services.easypay.account_id')),
        ]);
    }

    public function generate(Request $request, Booking $booking)
    {
        $request->validate([
            'method'       => 'required|in:mbway,multibanco',
            'mbway_phone'  => 'required_if:method,mbway|nullable|string',
        ]);

        // Cancel any existing pending payment for this booking
        $booking->payment?->update(['status' => 'expirado']);

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

    private function calculateAmount(Booking $booking): float
    {
        $settings = \App\Models\Setting::all()->keyBy('key');
        $amount   = (float) ($settings[$booking->type]?->value ?? 0);

        if ($booking->type === 'hotel' && $booking->end_date && $booking->start_date) {
            $nights = $booking->start_date->diffInDays($booking->end_date);
            $amount = (float) ($settings['hotel_noite']?->value ?? 0) * max(1, $nights);
        }

        if ($booking->pet_taxi) {
            $amount += (float) ($settings['pet_taxi']?->value ?? 0);
        }

        return round($amount, 2);
    }
}
