<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * L'intro 3D de l'accueil dure environ 8 secondes, pendant lesquelles la page est
 * masquée et le défilement bloqué. Elle ne doit se jouer qu'une fois par session
 * de navigation : la revoir à chaque retour sur l'accueil rendait le site pénible.
 */
class IntroLoaderTest extends TestCase
{
    use RefreshDatabase;

    public function test_intro_is_limited_to_one_play_per_session(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('fsl-intro', $html, 'L’intro est bien présente sur l’accueil.');
        $this->assertStringContainsString('sessionStorage', $html, 'La lecture est conditionnée au stockage de session.');
        $this->assertStringContainsString('fsl.intro.seen', $html);

        // Le drapeau est posé avant de lancer la séquence : quitter la page en
        // pleine intro ne doit pas la rejouer au retour.
        $this->assertLessThan(
            strpos($html, "html.style.overflow = 'hidden'"),
            strpos($html, 'markSeen();'),
            'Le drapeau doit être posé avant le blocage du défilement.'
        );
    }

    public function test_intro_only_runs_on_the_home_page(): void
    {
        foreach ([route('about'), route('ebooks.index'), route('events.index'), route('contact')] as $url) {
            $this->assertStringNotContainsString(
                'fsl-intro', $this->get($url)->assertOk()->getContent(),
                'Aucune autre page ne doit être bloquée par l’intro : '.$url
            );
        }
    }
}
