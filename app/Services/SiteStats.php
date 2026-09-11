<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Member;
use App\Models\Registration;

/**
 * Chiffres réels de l'association, affichés sur le site public et sur la page de
 * connexion.
 *
 * Ils sont calculés à la demande à partir de la base : aucun nombre n'est saisi
 * ni figé dans une vue. Un chiffre qu'on ne sait pas mesurer n'est pas affiché —
 * « heures de formation » a été retiré faute de durée enregistrée sur les
 * événements, plutôt que d'annoncer une valeur inventée.
 */
class SiteStats
{
    /** @var array<string, int> */
    private array $memo = [];

    /** Adhésions actives à cet instant. */
    public function activeMembers(): int
    {
        return $this->count('members', fn () => Member::active()->count());
    }

    /** Pays distincts déclarés par les membres actives. */
    public function countries(): int
    {
        return $this->count('countries', fn () => Member::active()
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->count('country'));
    }

    /** Événements réellement organisés : ni brouillons, ni annulés. */
    public function events(): int
    {
        return $this->count('events', fn () => Event::whereIn('status', ['published', 'completed'])->count());
    }

    /** Participations confirmées à un événement (payées ou présentes). */
    public function participations(): int
    {
        return $this->count('participations', fn () => Registration::whereIn('status', ['paid', 'attended'])->count());
    }

    /**
     * Chiffres à afficher dans la bande, dans l'ordre. Les valeurs nulles sont
     * écartées : « 0 pays représentés » dessert le propos plus qu'il ne l'illustre.
     *
     * @return array<int, array{value: int, label: string, accent: string}>
     */
    public function banner(): array
    {
        $stats = [
            ['value' => $this->activeMembers(), 'label' => 'Membres actives', 'accent' => 'var(--rose)'],
            ['value' => $this->countries(), 'label' => 'Pays représentés', 'accent' => 'var(--gold)'],
            ['value' => $this->events(), 'label' => 'Événements organisés', 'accent' => 'var(--rose)'],
            ['value' => $this->participations(), 'label' => 'Participations', 'accent' => 'var(--gold)'],
        ];

        return array_values(array_filter($stats, fn (array $stat) => $stat['value'] > 0));
    }

    /**
     * La bande n'a d'intérêt qu'avec au moins deux chiffres à montrer : sur une
     * base quasi vide, elle donnerait une impression d'inachevé.
     */
    public function hasBanner(): bool
    {
        return count($this->banner()) >= 2;
    }

    /**
     * Mémorise le résultat sur l'instance : une même page affiche plusieurs fois
     * les mêmes chiffres (bande, page de connexion) sans relancer les requêtes.
     * L'instance étant liée à la requête, un chiffre n'est jamais périmé.
     *
     * Une base injoignable ne doit jamais faire tomber la page publique : on
     * retourne 0, ce qui escamote le chiffre plutôt que d'afficher une erreur.
     */
    private function count(string $key, callable $query): int
    {
        if (array_key_exists($key, $this->memo)) {
            return $this->memo[$key];
        }

        try {
            return $this->memo[$key] = (int) $query();
        } catch (\Throwable $e) {
            return $this->memo[$key] = 0;
        }
    }
}
