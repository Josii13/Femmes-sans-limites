<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Archive des éditions passées.
 *
 * Les événements terminés disparaissaient entièrement du site alors qu'ils sont
 * la preuve la plus concrète de l'activité de l'association. Ils restent
 * désormais visibles, sans jamais laisser croire qu'on peut encore s'y inscrire.
 */
class PastEventsTest extends TestCase
{
    use RefreshDatabase;

    private function event(string $title, string $status, string $when): Event
    {
        return Event::factory()->create([
            'title' => $title,
            'status' => $status,
            'event_date' => $when === 'past' ? now()->subMonth() : now()->addMonth(),
        ]);
    }

    public function test_past_events_appear_in_the_archive(): void
    {
        $past = $this->event('Forum Leadership 2025', 'completed', 'past');
        $upcoming = $this->event('Forum Leadership 2026', 'published', 'futur');

        $html = $this->get(route('events.index'))->assertOk()->getContent();

        $this->assertStringContainsString('Nos éditions précédentes', $html);
        $this->assertStringContainsString($past->title, $html);
        $this->assertStringContainsString($upcoming->title, $html);
    }

    public function test_the_archive_is_hidden_without_past_events(): void
    {
        $this->event('Forum à venir', 'published', 'futur');

        $html = $this->get(route('events.index'))->assertOk()->getContent();

        $this->assertStringNotContainsString('Nos éditions précédentes', $html);
    }

    public function test_drafts_and_cancelled_events_stay_out_of_the_archive(): void
    {
        $draft = $this->event('Brouillon passé', 'draft', 'past');
        $cancelled = $this->event('Annulé', 'cancelled', 'past');
        $completed = $this->event('Bien eu lieu', 'completed', 'past');

        $html = $this->get(route('events.index'))->assertOk()->getContent();

        $this->assertStringContainsString($completed->title, $html);
        $this->assertStringNotContainsString($draft->title, $html);
        $this->assertStringNotContainsString($cancelled->title, $html);
    }

    public function test_the_archive_shows_the_real_attendance(): void
    {
        $event = $this->event('Forum 2025', 'completed', 'past');

        foreach (['paid', 'attended', 'pending'] as $i => $status) {
            Registration::create([
                'event_id' => $event->id, 'first_name' => 'P', 'last_name' => 'T',
                'email' => 'p'.$i.'@example.com', 'status' => $status,
            ]);
        }
        // Une annulation ne compte pas comme une participante.
        Registration::create([
            'event_id' => $event->id, 'first_name' => 'P', 'last_name' => 'T',
            'email' => 'annulee@example.com', 'status' => 'cancelled',
        ]);

        $html = $this->get(route('events.index'))->assertOk()->getContent();

        $this->assertStringContainsString('3 participantes', $html);
    }

    public function test_a_completed_event_keeps_its_page(): void
    {
        $event = $this->event('Forum 2025', 'completed', 'past');

        // Sans cela, les liens de l'archive et ceux déjà partagés renverraient un 404.
        $this->get(route('events.show', $event->slug))->assertOk()->assertSee($event->title);
    }

    public function test_a_draft_event_page_stays_unreachable(): void
    {
        $draft = $this->event('Brouillon', 'draft', 'past');

        $this->get(route('events.show', $draft->slug))->assertNotFound();
    }

    public function test_registering_for_a_past_event_is_refused(): void
    {
        $event = $this->event('Forum 2025', 'published', 'past');

        $this->post(route('events.register', $event->slug), [
            'first_name' => 'Awa', 'last_name' => 'Traoré', 'email' => 'awa@example.com',
        ])->assertRedirect();

        $this->assertDatabaseCount('registrations', 0);
    }
}
