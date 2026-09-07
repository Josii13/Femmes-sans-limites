<?php

namespace App\Http\Controllers\Concerns;

/**
 * Nom du champ piège anti-bot, partagé par le composant Blade <x-honeypot />,
 * le trait DetectsBots et les tests.
 *
 * Il vit dans une classe et non dans le trait : PHP interdit l'accès direct à
 * une constante de trait (Honeypot::FIELD reste lisible depuis une vue).
 *
 * Le nom doit rester OPAQUE. L'ancien, « website », correspondait aux
 * heuristiques de remplissage automatique des navigateurs et des gestionnaires
 * de mots de passe : de vrais visiteurs étaient pris pour des robots.
 */
final class Honeypot
{
    public const FIELD = 'hp_ref';
}
