<?php

use App\Models\SiteImage;

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

if (! function_exists('site_img')) {
    function site_img(string $key): string
    {
        static $cache = null;
        if ($cache === null) {
            try {
                $cache = SiteImage::all()->keyBy('key');
            } catch (Exception $e) {
                $cache = collect();
            }
        }
        $image = $cache->get($key);
        if (! $image) {
            return '';
        }

        return $image->url;
    }
}
