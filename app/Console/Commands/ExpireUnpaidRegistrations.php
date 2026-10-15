<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Registration;
use App\Services\WaitingListNotifier;
use Illuminate\Console\Command;

class ExpireUnpaidRegistrations extends Command
{
    protected $signature = 'registrations:expire-unpaid {--minutes=30 : Délai avant expiration}';

    protected $description = 'Annule les inscriptions à des événements payants restées impayées (libère la place)';

    public function handle(WaitingListNotifier $notifier): int
    {
        $deadline = now()->subMinutes((int) $this->option('minutes'));
        $cancelled = 0;
        // Événements dont une place vient de se libérer : on prévient après coup,
        // une fois toutes les annulations faites, pour ne pas notifier par à-coups.
        $freed = [];

        Registration::where('status', 'pending')
            ->where('created_at', '<', $deadline)
            ->whereHas('event', fn ($q) => $q->where('is_paid', true)->where('price', '>', 0))
            ->with('latestPayment')
            ->chunkById(200, function ($registrations) use (&$cancelled, &$freed) {
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
                    $freed[$registration->event_id] = ($freed[$registration->event_id] ?? 0) + 1;
                }
            });

        $notified = 0;
        foreach ($freed as $eventId => $spots) {
            $event = Event::find($eventId);
            if ($event) {
                $notified += $notifier->notifyNext($event, $spots);
            }
        }

        $this->info("Inscriptions impayées expirées : {$cancelled}. Liste d'attente prévenue : {$notified}.");

        return self::SUCCESS;
    }
}
