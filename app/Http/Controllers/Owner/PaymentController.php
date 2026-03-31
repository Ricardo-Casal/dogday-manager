<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\EasypayService;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(): Response
    {
        $owner = auth()->user()->owner;

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
}
