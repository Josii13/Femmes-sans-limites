<?php

namespace App\Services;

use App\Mail\WaitingListSpotMail;
use App\Models\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Prévient la liste d'attente quand une place se libère.
 *
 * Extrait du back-office : l'annulation manuelle prévenait la liste, mais pas
 * l'expiration automatique des inscriptions impayées — or c'est précisément
 * elle qui libère le plus de places, et au pire moment pour l'ignorer, juste
 * après un lancement où l'événement affiche « complet ».
 */
class WaitingListNotifier
{
    /**
     * Prévient autant de personnes qu'il reste de places, dans l'ordre d'arrivée.
     *
     * @return int nombre de personnes prévenues
     */
    public function notifyNext(Event $event, int $limit = 1): int
    {
        // Pas de capacité = pas de liste d'attente possible.
        if (! $event->capacity) {
            return 0;
        }

        $fresh = $event->fresh();

        if ($fresh === null || $fresh->hasTakenPlace() || $fresh->is_sold_out) {
            return 0;
        }

        // Ne jamais prévenir plus de personnes qu'il n'y a de places libres :
        // promettre une place à dix personnes pour une seule est pire que se taire.
        $spots = min($limit, (int) $fresh->spots_left);

        if ($spots < 1) {
            return 0;
        }

        $notified = 0;

        foreach ($fresh->waitingList()->where('notified', false)->oldest()->limit($spots)->get() as $entry) {
            try {
                Mail::to($entry->email)->queue(new WaitingListSpotMail($fresh, $entry));
                $entry->update(['notified' => true]);
                $notified++;
            } catch (\Throwable $e) {
                // Non marquée comme prévenue : elle sera retentée au prochain passage.
                Log::warning('Liste d\'attente : échec de notification', [
                    'event_id' => $fresh->id, 'email' => $entry->email, 'error' => $e->getMessage(),
                ]);
            }
        }

        return $notified;
    }
}
