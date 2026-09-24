<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * Charte de la communauté.
 *
 * Le texte vit dans le code plutôt qu'en base : une charte change rarement, et
 * quand elle change on veut savoir quand, quoi et par qui — ce que l'historique
 * du dépôt donne gratuitement. Un écran d'édition offrirait l'inverse : une
 * modification silencieuse d'un texte que les membres se sont engagées à respecter.
 *
 * La VERSION est enregistrée avec chaque acceptation. En cas de révision, on sait
 * exactement qui a accepté quelle version, et qui doit être réinvitée à l'accepter.
 * Toute modification de fond doit donc s'accompagner d'une montée de version.
 */
final class CommunityRules
{
    public const VERSION = '1.0';

    private const EFFECTIVE_DATE = '2026-09-24';

    /**
     * Les sept règles, dans l'ordre de la charte.
     *
     * @return array<int, array{title: string, summary: string, body: array<int, string>}>
     */
    public static function all(): array
    {
        return [
            [
                'title' => 'Respect',
                'summary' => 'Aucun manque de respect n’est accepté',
                'body' => [
                    'Chaque membre doit respecter les autres membres, leurs parcours, leurs opinions, leurs choix et leurs différences.',
                ],
            ],
            [
                'title' => 'Confidentialité',
                'summary' => 'Ce qui est partagé ici reste ici',
                'body' => [
                    'Les échanges personnels, témoignages, confidences, photos, captures d’écran ou informations concernant une membre ne doivent pas être diffusés en dehors de la communauté sans son autorisation.',
                ],
            ],
            [
                'title' => 'Participation',
                'summary' => 'Être membre, c’est aussi s’impliquer',
                'body' => [
                    'Chaque membre est invitée à participer activement à la vie de la communauté, aux rencontres, aux activités et aux échanges.',
                    'Lorsque la présence à une activité a été confirmée, la membre s’engage à respecter son engagement ou à prévenir en cas d’empêchement.',
                ],
            ],
            [
                'title' => 'Communication',
                'summary' => 'On échange avec bienveillance et responsabilité',
                'body' => [
                    'Les désaccords sont autorisés, le manque de respect ne l’est pas.',
                    'Toute remarque, critique ou opinion doit être exprimée de manière constructive. Les conflits personnels ne doivent pas être exposés ou entretenus dans les espaces de la communauté.',
                ],
            ],
            [
                'title' => 'Partage',
                'summary' => 'On contribue sans imposer',
                'body' => [
                    'Chaque membre peut partager ses connaissances, expériences, ressources, opportunités et conseils lorsqu’ils peuvent être utiles à la communauté.',
                    'Toutefois, aucun conseil, opinion ou expérience personnelle ne doit être imposé aux autres membres comme une vérité absolue.',
                ],
            ],
            [
                'title' => 'Intégrité',
                'summary' => 'Pas de manipulation, de démarchage abusif ou d’utilisation de la communauté à des fins personnelles',
                'body' => [
                    'La communauté FSL ne doit pas être utilisée pour tromper, manipuler, exploiter ou nuire à une autre membre.',
                    'La publicité, le démarchage commercial, la sollicitation financière, la collecte de données ou la promotion répétitive de services personnels ne sont autorisés que dans les cadres prévus par FSL.',
                ],
            ],
            [
                'title' => 'Responsabilité',
                'summary' => 'Chacune est responsable de son comportement',
                'body' => [
                    'Chaque membre est responsable de ses paroles, de ses actes et de leur impact sur la communauté.',
                    'Tout comportement répété allant à l’encontre de cette charte pourra faire l’objet d’un rappel à l’ordre, d’une restriction de participation ou, selon la gravité des faits, d’une exclusion de la communauté.',
                ],
            ],
        ];
    }

    public static function effectiveDate(): Carbon
    {
        return Carbon::parse(self::EFFECTIVE_DATE);
    }
}
