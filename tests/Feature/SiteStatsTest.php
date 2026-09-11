<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Member;
use App\Models\Registration;
use App\Services\SiteStats;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Les chiffres affichés sur le site viennent de la base, jamais d'une valeur
 * saisie dans une vue. Deux exigences : ne compter que ce qui est réel (une
 * candidature en attente n'est pas une membre, un brouillon n'est pas un
 * événement), et ne rien afficher plutôt qu'un zéro qui dessert le propos.
 */
class SiteStatsTest extends TestCase
{
    use RefreshDatabase;

    private function stats(): SiteStats
    {
        // Instance neuve : le service mémorise ses résultats pour la durée d'une requête.
        return new SiteStats;
    }

    private function event(string $status = 'published'): Event
    {
        return Event::factory()->create(['status' => $status]);
    }

    public function test_only_active_members_are_counted(): void
    {
        Member::factory()->count(3)->create(['status' => 'active']);
        Member::factory()->create(['status' => 'pending']);
        Member::factory()->create(['status' => 'rejected']);
        Member::factory()->create(['status' => 'expired']);

        $this->assertSame(3, $this->stats()->activeMembers());
    }

    public function test_deleted_members_are_not_counted(): void
    {
        Member::factory()->count(2)->create(['status' => 'active']);
        Member::factory()->create(['status' => 'active'])->delete();

        $this->assertSame(2, $this->stats()->activeMembers());
    }

    public function test_countries_are_counted_once_each(): void
    {
        Member::factory()->count(2)->create(['status' => 'active', 'country' => 'Côte d’Ivoire']);
        Member::factory()->create(['status' => 'active', 'country' => 'Sénégal']);
        // Une membre non active ne fait pas entrer son pays dans le compte.
        Member::factory()->create(['status' => 'pending', 'country' => 'Mali']);

        $this->assertSame(2, $this->stats()->countries());
    }

    public function test_drafts_and_cancelled_events_are_excluded(): void
    {
        $this->event('published');
        $this->event('completed');
        $this->event('draft');
        $this->event('cancelled');

        $this->assertSame(2, $this->stats()->events());
    }

    public function test_only_confirmed_registrations_count_as_participations(): void
    {
        $event = $this->event();

        foreach (['paid', 'attended', 'pending', 'payment_sent', 'cancelled'] as $i => $status) {
            Registration::create([
                'event_id' => $event->id,
                'first_name' => 'Participante',
                'last_name' => 'Test',
                'email' => 'participante'.$i.'@example.com',
                'status' => $status,
            ]);
        }

        $this->assertSame(2, $this->stats()->participations());
    }

    public function test_zero_values_are_dropped_from_the_banner(): void
    {
        Member::factory()->count(2)->create(['status' => 'active', 'country' => 'Côte d’Ivoire']);
        $this->event('published');
        // Aucune participation enregistrée.

        $labels = array_column($this->stats()->banner(), 'label');

        $this->assertSame(['Membres actives', 'Pays représentés', 'Événements organisés'], $labels);
        $this->assertNotContains('Participations', $labels);
    }

    public function test_banner_is_hidden_when_there_is_almost_nothing_to_show(): void
    {
        $this->assertFalse($this->stats()->hasBanner(), 'Base vide : aucune bande.');

        // Une seule membre fournit déjà deux chiffres : elle-même et son pays.
        Member::factory()->create(['status' => 'active', 'country' => 'Côte d’Ivoire']);
        $this->assertTrue((new SiteStats)->hasBanner());
    }

    // ── Rendu ────────────────────────────────────────────────────

    public function test_home_page_shows_the_real_figures(): void
    {
        Member::factory()->count(7)->create(['status' => 'active', 'country' => 'Côte d’Ivoire']);
        Member::factory()->count(2)->create(['status' => 'active', 'country' => 'Sénégal']);
        $this->event('published');
        $this->event('completed');

        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('data-target="9"', $html);   // membres actives
        $this->assertStringContainsString('data-target="2"', $html);   // pays et événements
        $this->assertStringContainsString('Membres actives', $html);
        $this->assertStringContainsString('Événements organisés', $html);

        // Les anciens chiffres inventés ont disparu.
        $this->assertStringNotContainsString('Heures de formation', $html);
        $this->assertStringNotContainsString('data-suffix="+"', $html);
    }

    public function test_stats_band_disappears_on_an_empty_site(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringNotContainsString('Membres actives', $html);
        $this->assertStringNotContainsString('Événements organisés', $html);
    }

    public function test_about_page_shows_the_band_with_its_heading(): void
    {
        Member::factory()->count(4)->create(['status' => 'active', 'country' => 'Côte d’Ivoire']);
        $this->event('published');

        $html = $this->get(route('about'))->assertOk()->getContent();

        // Blade échappe l'apostrophe du titre : on compare à la version rendue.
        $this->assertStringContainsString(e('L\'impact en chiffres'), $html);
        $this->assertStringContainsString('Nos résultats', $html);
        $this->assertStringContainsString('data-target="4"', $html);
    }

    public function test_login_page_shows_the_real_figures(): void
    {
        Member::factory()->count(6)->create(['status' => 'active', 'country' => 'Côte d’Ivoire']);
        $this->event('published');

        $html = $this->get(route('login'))->assertOk()->getContent();

        $this->assertStringContainsString('6 membres actives', $html);
        $this->assertStringNotContainsString('500+', $html);
        $this->assertStringNotContainsString('15+', $html);
    }

    public function test_login_page_uses_the_singular_for_a_lone_member(): void
    {
        Member::factory()->create(['status' => 'active', 'country' => 'Côte d’Ivoire']);

        $html = $this->get(route('login'))->assertOk()->getContent();

        $this->assertStringContainsString('1 membre active', $html);
        $this->assertStringNotContainsString('1 membres actives', $html);
    }
}
