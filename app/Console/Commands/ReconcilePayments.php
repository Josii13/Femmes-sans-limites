<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Services\GeniusPayService;
use App\Services\PaymentFulfiller;
use Illuminate\Console\Command;

class ReconcilePayments extends Command
{
    protected $signature = 'payments:reconcile {--hours=48 : Fenêtre de paiements à vérifier}';

    protected $description = 'Synchronise les paiements en attente avec GeniusPay (filet en cas de webhook manqué)';

    public function handle(GeniusPayService $genius, PaymentFulfiller $fulfiller): int
    {
        $confirmed = 0;
        $closed = 0;

        Payment::whereIn('status', ['pending', 'processing'])
            ->whereNotNull('provider_reference')
            ->where('created_at', '>=', now()->subHours((int) $this->option('hours')))
            ->chunkById(100, function ($payments) use ($genius, $fulfiller, &$confirmed, &$closed) {
                foreach ($payments as $payment) {
                    $remote = $genius->getPayment($payment->provider_reference);
                    if (! $remote) {
                        continue;
                    }

                    $status = $remote['status'] ?? null;

                    if (Payment::isSuccessfulStatus($status)) {
                        $paid = (float) ($remote['amount'] ?? 0);
                        if ($paid > 0 && abs($paid - (float) $payment->amount) > 0.01) {
                            $payment->update(['status' => 'amount_mismatch']);

                            continue;
                        }
                        $payment->update(['status' => 'completed', 'paid_at' => now()]);
                        $fulfiller->fulfill($payment->fresh('payable'));
                        $confirmed++;
                    } elseif (in_array($status, ['failed', 'cancelled', 'expired'], true)) {
                        $payment->update(['status' => $status]);
                        $closed++;
                    }
                }
            });

        $this->info("Réconciliation : {$confirmed} confirmé(s), {$closed} clôturé(s).");

        return self::SUCCESS;
    }
}
