<?php

use App\Models\Member;
use App\Models\SiteImage;
use App\Services\SiteStats;
use Illuminate\Support\Facades\Storage;

if (! function_exists('mb_ucfirst')) {
    /**
     * Met en majuscule la première lettre d'une chaîne, en gérant l'UTF-8
     * (les prénoms accentués notamment). PHP ne fournit pas de version mb_ native.
     */
    function mb_ucfirst(?string $string, string $encoding = 'UTF-8'): string
    {
        $string = (string) $string;
        if ($string === '') {
            return '';
        }
        $first = mb_substr($string, 0, 1, $encoding);
        $rest = mb_substr($string, 1, null, $encoding);

        return mb_strtoupper($first, $encoding).$rest;
    }
}

if (! function_exists('fsl_remember')) {
    /**
     * Mémorise une valeur le temps de la requête.
     *
     * Volontairement stockée dans le conteneur plutôt que dans une variable
     * `static` : une statique survit à la requête dans un processus PHP durable
     * (worker de file, Octane) et entre deux tests, servant alors des données
     * périmées. Le conteneur, lui, est reconstruit à chaque requête.
     */
    function fsl_remember(string $key, Closure $resolve): mixed
    {
        if (app()->bound($key)) {
            return app($key);
        }

        $value = $resolve();
        app()->instance($key, $value);

        return $value;
    }
}

if (! function_exists('site_img')) {
    function site_img(string $key): string
    {
        $images = fsl_remember('fsl.site_images', function () {
            try {
                return SiteImage::all()->keyBy('key');
            } catch (Exception $e) {
                return collect();
            }
        });

        $image = $images->get($key);
        if (! $image) {
            return '';
        }

        return $image->url;
    }
}

if (! function_exists('community_photos')) {
    /**
     * URLs des photos des membres actives, pour illustrer la communauté sur le site
     * public (galerie « Notre communauté ») et sur la page de connexion.
     *
     * Alimentée automatiquement : dès qu'une candidature est activée, la photo entre
     * dans la rotation, sans aucune manipulation en back-office. Chaque page qui
     * l'utilise reste responsable de son repli (images éditoriales, visuels par
     * défaut) pour ne jamais afficher de vide.
     *
     * @return array<int, string>
     */
    function community_photos(int $limit = 12): array
    {
        return fsl_remember('fsl.community_photos.'.$limit, function () use ($limit) {
            try {
                return Member::inPublicGallery()
                    ->limit($limit)
                    ->pluck('photo')
                    ->map(fn (string $path) => Storage::disk('public')->url($path))
                    ->all();
            } catch (Exception $e) {
                // Base injoignable ou colonne absente (migration non jouée) : la page
                // publique doit rester affichable, elle retombera sur ses visuels.
                return [];
            }
        });
    }
}

if (! function_exists('site_stats')) {
    /**
     * Chiffres réels de l'association (membres actives, pays, événements,
     * participations), calculés depuis la base.
     *
     * Instance partagée le temps de la requête : la bande de la page d'accueil et
     * la page de connexion interrogent la base une seule fois par chiffre.
     */
    function site_stats(): SiteStats
    {
        return fsl_remember('fsl.site_stats', fn () => new SiteStats);
    }
}
