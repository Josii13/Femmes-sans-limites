<?php

namespace App\Console\Commands;

use App\Models\Registration;
use Illuminate\Console\Command;

class ExpireUnpaidRegistrations extends Command
{
    protected $signature = 'registrations:expire-unpaid {--minutes=30 : Délai avant expiration}';

    protected $description = 'Annule les inscriptions à des événements payants restées impayées (libère la place)';

    public function handle(): int
    {
        $deadline = now()->subMinutes((int) $this->option('minutes'));
        $cancelled = 0;

        Registration::where('status', 'pending')
            ->where('created_at', '<', $deadline)
            ->whereHas('event', fn ($q) => $q->where('is_paid', true)->where('price', '>', 0))
            ->with('latestPayment')
            ->chunkById(200, function ($registrations) use (&$cancelled) {
                foreach ($registrations as $registration) {
                    // Sécurité : ne pas annuler si un paiement a finalement abouti.
                    if ($registration->latestPayment && $registration->latestPayment->isPaid()) {
                        continue;
                    }

                    $registration->update(['status' => 'cancelled']);
                    $registration->payments()
                        ->whereIn('status', ['pending', 'processing'])
                        ->update(['status' => 'expired']);
                    $cancelled++;
                }
            });

        $this->info("Inscriptions impayées expirées : {$cancelled}.");

        return self::SUCCESS;
    }
}
