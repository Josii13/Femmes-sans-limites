<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Écran d'introduction de la page d'accueil.
 *
 * Un visiteur qui vient chercher une date d'atelier ne doit pas attendre.
 * Ces tests gardent les garde-fous qui rendent l'intro supportable : une
 * sortie visible, une durée bornée, et le respect de qui a désactivé les
 * animations.
 */
class IntroLoaderTest extends TestCase
{
    use RefreshDatabase;

    private function home(): string
    {
        return $this->get(route('home'))->assertOk()->getContent();
    }

    public function test_the_intro_only_runs_on_the_home_page(): void
    {
        $this->assertStringContainsString('fsl-intro', $this->home());

        // Ailleurs, elle n'a rien à faire : on vient d'un lien, pas d'une entrée.
        $this->assertStringNotContainsString('fsl-intro',
            $this->get(route('contact'))->getContent());
    }

    public function test_there_is_a_visible_way_out(): void
    {
        $html = $this->home();

        $this->assertStringContainsString('fsl-intro-skip', $html);
        // Le bouton doit rester lisible : 44px de zone de frappe, pas 12px
        // de gris à 45 % d'opacité comme auparavant.
        $this->assertStringContainsString('min-height:44px', $html);
        // Trois sorties : bouton, clic n'importe où, touche Échap.
        $this->assertStringContainsString("el.addEventListener('click', finish)", $html);
        $this->assertStringContainsString("'Escape'", $html);
    }

    public function test_the_wait_stays_short_and_visible(): void
    {
        $html = $this->home();

        // 1 300 + 4×550 + 650 ≈ 4,3 s, contre 8,2 s auparavant.
        $this->assertStringContainsString('HOLD_BRAND=1300', $html);
        $this->assertStringContainsString('HOLD_VAL=550', $html);
        // Une barre de progression : savoir que ça finit change l'attente.
        $this->assertStringContainsString('fsl-intro-bar', $html);
    }

    public function test_a_slow_cdn_does_not_hold_the_visitor(): void
    {
        $html = $this->home();

        // Le filet de sécurité était à 12 s : douze secondes d'écran noir si
        // le CDN ne répondait pas.
        $this->assertStringContainsString('setTimeout(finish, 2500)', $html);
        $this->assertStringContainsString('rel="preconnect"', $html);
    }

    public function test_the_brand_is_never_abbreviated(): void
    {
        $html = $this->home();

        // Sur mobile le nom devenait « FSL ». C'est l'écran où la marque se
        // découvre : elle s'y écrit en entier, sur deux lignes.
        $this->assertStringContainsString("['FEMME', 'SANS LIMITES']", $html);
        $this->assertStringNotContainsString("? 'FSL' :", $html);
    }

    public function test_reduced_motion_is_respected(): void
    {
        $html = $this->home();

        $this->assertStringContainsString('prefers-reduced-motion:reduce', $html);
        $this->assertStringContainsString('prefers-reduced-motion: reduce', $html);
    }

    public function test_it_plays_once_per_browsing_session(): void
    {
        $html = $this->home();

        // sessionStorage et non localStorage : l'intro reste une entrée en
        // matière pour une nouvelle visite, sans se rejouer à chaque retour.
        $this->assertStringContainsString('sessionStorage.getItem(SEEN_KEY)', $html);
        $this->assertStringContainsString('sessionStorage.setItem(SEEN_KEY', $html);
        // On vise les appels, pas le mot : le commentaire du code explique
        // justement pourquoi localStorage a été écarté.
        $this->assertStringNotContainsString('localStorage.setItem', $html);
        $this->assertStringNotContainsString('localStorage.getItem', $html);
    }
}
