<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\GeniusPayService;
use App\Services\PaymentFulfiller;

class PaymentController extends Controller
{
    public function __construct(
        private GeniusPayService $genius,
        private PaymentFulfiller $fulfiller,
    ) {}

    /**
     * Page de retour après paiement réussi (success_url).
     * Filet de sécurité : si le webhook n'est pas encore arrivé, on synchronise
     * le statut auprès de GeniusPay et on honore le paiement immédiatement.
     */
    public function success(Payment $payment)
    {
        if (! $payment->isPaid() && $payment->provider_reference) {
            $remote = $this->genius->getPayment($payment->provider_reference);
            if ($remote && Payment::isSuccessfulStatus($remote['status'] ?? null)) {
                // Vérifie le montant avant de confirmer (cohérence financière).
                $paid = (float) ($remote['amount'] ?? 0);
                if ($paid <= 0 || abs($paid - (float) $payment->amount) <= 0.01) {
                    $payment->update(['status' => 'completed', 'paid_at' => now()]);
                    $this->fulfiller->fulfill($payment->fresh('payable'));
                }
            }
        }

        return view('public.payment.success', [
            'payment' => $payment->fresh(),
            'type' => $payment->payable_type,
        ]);
    }

    public function cancel(Payment $payment)
    {
        if (in_array($payment->status, ['pending', 'processing'], true)) {
            $payment->update(['status' => 'cancelled']);
        }

        return view('public.payment.cancel', ['payment' => $payment]);
    }
}
