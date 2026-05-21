<?php

namespace Database\Seeders;

use App\Models\SiteImage;
use Illuminate\Database\Seeder;

class SiteImageSeeder extends Seeder
{
    public function run(): void
    {
        $images = [
            // Accueil
            ['key' => 'home_hero_main',      'label' => 'Accueil — Hero, photo principale',          'page' => 'home',  'default_path' => 'photo_01_1.png'],
            ['key' => 'home_hero_secondary', 'label' => 'Accueil — Hero, photo secondaire',           'page' => 'home',  'default_path' => 'photo_03_3.png'],
            ['key' => 'home_adn_group',      'label' => 'Accueil — Section ADN, photo groupe',        'page' => 'home',  'default_path' => 'photo_02_2.png'],
            ['key' => 'home_temoignage_1',   'label' => 'Accueil — Témoignage 1 (Khadija Mbaye)',     'page' => 'home',  'default_path' => 'photo_03_3.png'],
            ['key' => 'home_temoignage_2',   'label' => 'Accueil — Témoignage 2 (Dr. Aminata Sow)',   'page' => 'home',  'default_path' => 'photo_01_2.png'],
            ['key' => 'home_temoignage_3',   'label' => 'Accueil — Témoignage 3 (Bintou Diarra)',     'page' => 'home',  'default_path' => 'photo_03_4.png'],
            ['key' => 'home_cta_bg',         'label' => 'Accueil — CTA final, fond',                  'page' => 'home',  'default_path' => 'photo_02_2.png'],

            // À propos
            ['key' => 'about_hero_main',     'label' => 'À propos — Hero, photo principale',          'page' => 'about', 'default_path' => 'photo_01_3.png'],
            ['key' => 'about_hero_secondary','label' => 'À propos — Hero, photo secondaire',           'page' => 'about', 'default_path' => 'photo_03_1.png'],
            ['key' => 'about_fondatrice',    'label' => 'À propos — Photo de la fondatrice',           'page' => 'about', 'default_path' => 'photo_03_2.jpeg'],
            ['key' => 'about_gallery_1',     'label' => 'À propos — Galerie, photo 1',                 'page' => 'about', 'default_path' => 'photo_02_2.png'],
            ['key' => 'about_gallery_2',     'label' => 'À propos — Galerie, photo 2',                 'page' => 'about', 'default_path' => 'photo_01_1.png'],
            ['key' => 'about_gallery_3',     'label' => 'À propos — Galerie, photo 3',                 'page' => 'about', 'default_path' => 'photo_03_3.png'],
            ['key' => 'about_gallery_4',     'label' => 'À propos — Galerie, photo 4',                 'page' => 'about', 'default_path' => 'photo_01_2.png'],
            ['key' => 'about_gallery_5',     'label' => 'À propos — Galerie, photo 5',                 'page' => 'about', 'default_path' => 'photo_03_4.png'],
            ['key' => 'about_gallery_6',     'label' => 'À propos — Galerie, photo 6',                 'page' => 'about', 'default_path' => 'photo_02_1.png'],
        ];

        foreach ($images as $data) {
            SiteImage::updateOrCreate(['key' => $data['key']], $data);
        }
    }
}
