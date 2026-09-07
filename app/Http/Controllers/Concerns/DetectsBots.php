<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Détection de soumissions automatisées via un champ piège (honeypot).
 *
 * Le nom du champ vit dans Honeypot::FIELD et reste volontairement opaque :
 * l'ancien nom « website » était rempli automatiquement par les navigateurs et
 * les gestionnaires de mots de passe, ce qui faisait passer de vrais visiteurs
 * pour des robots — paiement impossible, candidature d'adhésion perdue sans
 * message.
 *
 * Tout déclenchement est journalisé : un pic de « bot.blocked » sur une même
 * adresse IP signale un robot, des cas isolés signalent un faux positif.
 */
trait DetectsBots
{
    protected function isBotSubmission(Request $request, string $context): bool
    {
        if (! $request->filled(Honeypot::FIELD)) {
            return false;
        }

        Log::info('bot.blocked', [
            'context' => $context,
            'ip' => $request->ip(),
            'agent' => substr((string) $request->userAgent(), 0, 200),
        ]);

        return true;
    }
}
