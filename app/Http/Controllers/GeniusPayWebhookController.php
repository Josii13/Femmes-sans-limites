<?php

namespace App\Http\Controllers;

use App\Models\Payment;
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
            $payment->update(['status' => 'completed', 'paid_at' => now()]);
            $this->fulfiller->fulfill($payment->fresh('payable'));
        } elseif (in_array($status, ['failed', 'cancelled', 'expired'], true)) {
            $payment->update(['status' => $status]);
        }

        return response()->json(['message' => 'ok']);
    }
}
