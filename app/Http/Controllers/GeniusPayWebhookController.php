<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Payment;
use App\Models\Registration;
use App\Services\GeniusPayService;
use App\Services\PaymentFulfiller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GeniusPayWebhookController extends Controller
{
    public function __construct(
        private GeniusPayService $genius,
        private PaymentFulfiller $fulfiller,
    ) {}

    public function handle(Request $request)
    {
        $raw = $request->getContent();
        $signature = $request->header('X-Webhook-Signature');
        $timestamp = $request->header('X-Webhook-Timestamp');

        if (! $this->genius->verifyWebhookSignature($raw, $signature, $timestamp)) {
            Log::warning('GeniusPay webhook: signature invalide', ['ip' => $request->ip()]);

            return response()->json(['message' => 'invalid signature'], 401);
        }

        $event = (string) $request->input('event');
        $reference = (string) data_get($request->all(), 'data.reference');
        $status = (string) data_get($request->all(), 'data.status');

        // GeniusPay envoie SA référence ; on retrouve aussi via la métadonnée locale en secours.
        $payment = Payment::where('provider_reference', $reference)
            ->orWhere('reference', (string) data_get($request->all(), 'data.metadata.reference'))
            ->first();

        if (! $payment) {
            // On acquitte quand même (200) pour ne pas déclencher de re-livraisons en boucle.
            Log::warning('GeniusPay webhook: paiement inconnu', ['reference' => $reference, 'event' => $event]);

            return response()->json(['message' => 'unknown payment, acknowledged']);
        }

        // Idempotence : si déjà honoré, on acquitte sans rien refaire.
        if ($payment->isPaid()) {
            return response()->json(['message' => 'already processed']);
        }

        if (Payment::isSuccessfulStatus($status)) {
            // Vérification du montant : on refuse de confirmer si le montant payé ne correspond pas.
            $paidAmount = (float) data_get($request->all(), 'data.amount');
            if ($paidAmount > 0 && abs($paidAmount - (float) $payment->amount) > 0.01) {
                Log::warning('GeniusPay webhook: montant incohérent', [
                    'reference' => $reference, 'attendu' => $payment->amount, 'recu' => $paidAmount,
                ]);
                $payment->update(['status' => 'amount_mismatch']);

                return response()->json(['message' => 'amount mismatch'], 422);
            }

            $payment->update(['status' => 'completed', 'paid_at' => now()]);
            $this->fulfiller->fulfill($payment->fresh('payable'));
        } elseif (in_array($status, ['failed', 'cancelled', 'expired'], true)) {
            $payment->update(['status' => $status]);
        } elseif ($status === 'refunded') {
            $this->handleRefund($payment->fresh('payable'));
        }

        return response()->json(['message' => 'ok']);
    }

    /** Remboursement : on révoque l'accès lié (inscription) et on journalise. */
    private function handleRefund(Payment $payment): void
    {
        $payment->update(['status' => 'refunded']);

        $payable = $payment->payable;
        if ($payable instanceof Registration && $payable->status !== 'cancelled') {
            $payable->update(['status' => 'cancelled', 'qr_token' => null]);
            ActivityLog::record('payment.refunded', $payable->event, [
                'registration_id' => $payable->id, 'reference' => $payment->reference,
            ]);
        }
    }
}
