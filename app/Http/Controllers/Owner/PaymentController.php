<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\EasypayService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(): Response
    {
        $owner = auth()->user()->owner;

        if (!$owner) {
            return Inertia::render('Owner/Payments/Index', ['payments' => []]);
        }

        $payments = Payment::where('owner_id', $owner->id)
            ->with('booking.dog')
            ->latest()
            ->get();

        return Inertia::render('Owner/Payments/Index', [
            'payments' => $payments,
        ]);
    }

    public function resend(Payment $payment)
    {
        abort_unless($payment->owner_id === auth()->user()->owner->id, 403);
        abort_unless($payment->method === 'mbway' && $payment->status === 'pendente', 422);

        $easypay = app(EasypayService::class);
        $easypay->resendMBWay($payment->easypay_id);

        return back()->with('success', 'Pedido MBWay reenviado.');
    }

    public function changeMethod(Request $request, Payment $payment)
    {
        $owner = auth()->user()->owner;
        abort_unless($payment->owner_id === $owner->id, 403);
        abort_unless(in_array($payment->status, ['pendente', 'falhado']), 422);

        $request->validate([
            'method'      => 'required|in:mbway,multibanco',
            'mbway_phone' => 'required_if:method,mbway|nullable|string',
        ]);

        // Cancel current payment
        $payment->update(['status' => 'expirado']);

        $easypay  = app(EasypayService::class);
        $booking  = $payment->booking->load('dog');
        $desc     = "Cão: {$booking->dog->name} | " . ucfirst($booking->type);

        if ($request->method === 'mbway') {
            $phone  = $request->mbway_phone ?? $owner->phone;
            $result = $easypay->createMBWayPayment($phone, (float) $payment->amount, $desc);
        } else {
            $result = $easypay->createMultibancoPayment((float) $payment->amount, $desc);
        }

        if (!$result['success']) {
            return back()->withErrors(['payment' => $result['error']]);
        }

        Payment::create([
            'owner_id'     => $owner->id,
            'booking_id'   => $booking->id,
            'amount'       => $payment->amount,
            'status'       => 'pendente',
            'method'       => $request->method,
            'mbway_phone'  => $request->mbway_phone ?? null,
            'mb_entity'    => $result['mb_entity'] ?? null,
            'mb_reference' => $result['mb_reference'] ?? null,
            'easypay_id'   => $result['easypay_id'],
        ]);

        return back()->with('success', 'Método de pagamento alterado.');
    }
}
