<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EasypayWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $easypayId = $request->input('id');

        if (!$easypayId) {
            return response('missing id', 400);
        }

        $payment = Payment::where('easypay_id', $easypayId)->first();

        if (!$payment) {
            return response('not found', 404);
        }

        $type   = $request->input('type');
        $status = $request->input('status');

        if ($type === 'payment' && $status === 'success') {
            $payment->update([
                'status'         => 'pago',
                'paid_at'        => now(),
                'transaction_id' => $request->input('transaction_key'),
            ]);
        } elseif (in_array($status, ['error', 'failed'])) {
            $payment->update(['status' => 'falhado']);
        } elseif ($status === 'expired') {
            $payment->update(['status' => 'expirado']);
        }

        return response('ok', 200);
    }
}
