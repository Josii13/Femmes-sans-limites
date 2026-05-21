<?php

if (!function_exists('site_img')) {
    function site_img(string $key): string
    {
        static $cache = null;
        if ($cache === null) {
            try {
                $cache = \App\Models\SiteImage::all()->keyBy('key');
            } catch (\Exception $e) {
                $cache = collect();
            }
        }
        $image = $cache->get($key);
        if (!$image) {
            return '';
        }
        return $image->url;
    }
}
