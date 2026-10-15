<?php

namespace Tests\Feature;

use App\Http\Controllers\Concerns\Honeypot;
use App\Mail\TicketLinkMail;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Accès au billet sans dépendre de l'email.
 *
 * Le QR n'existait que dans un email envoyé avec ->queue(). En production la
 * file était en base sans worker : les emails dormaient dans la table `jobs`,
 * le back-office affichait « Confirmée » et l'inscrite n'avait rien. Elle se
 * réinscrivait pour comprendre, et tombait sur « Vous êtes déjà inscrit(e) ».
 */
class TicketAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Storage::fake('local');
    }

    private function freeEvent(): Event
    {
        return Event::factory()->create([
            'status' => 'published',
            'event_date' => now()->addMonth(),
            'is_paid' => false,
        ]);
    }

    private function applicant(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Awa', 'last_name' => 'Traoré', 'email' => 'awa@example.com',
        ], $overrides);
    }

    // ── L'inscription mène au billet ────────────────────────────

    public function test_a_free_registration_lands_on_its_ticket(): void
    {
        $event = $this->freeEvent();

        $response = $this->post(route('events.register', $event->slug), $this->applicant());

        $registration = Registration::firstOrFail();
        $this->assertNotNull($registration->qr_token);
        $response->assertRedirect(route('tickets.show', $registration->qr_token));
    }

    public function test_the_ticket_page_shows_the_qr_itself(): void
    {
        $event = $this->freeEvent();
        $this->post(route('events.register', $event->slug), $this->applicant());
        $registration = Registration::firstOrFail();

        $html = $this->get(route('tickets.show', $registration->qr_token))->assertOk()->getContent();

        // Le QR est dans la page, pas seulement en pièce jointe d'un email.
        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('Awa Traoré', $html);
        $this->assertStringContainsString($event->title, $html);
    }

    public function test_a_ticket_is_never_indexed(): void
    {
        $event = $this->freeEvent();
        $this->post(route('events.register', $event->slug), $this->applicant());
        $registration = Registration::firstOrFail();

        $html = $this->get(route('tickets.show', $registration->qr_token))->assertOk()->getContent();

        $this->assertStringContainsString('noindex', $html);
    }

    // ── Le jeton fait le secret ─────────────────────────────────

    public function test_an_unknown_token_is_not_found(): void
    {
        $this->get(route('tickets.show', 'jeton-inexistant'))->assertNotFound();
    }

    public function test_an_unconfirmed_registration_has_no_ticket(): void
    {
        $event = Event::factory()->create([
            'status' => 'published', 'event_date' => now()->addMonth(),
            'is_paid' => true, 'price' => 5000,
        ]);
        $registration = Registration::create([
            'event_id' => $event->id, 'first_name' => 'Awa', 'last_name' => 'Traoré',
            'email' => 'awa@example.com', 'status' => 'pending', 'qr_token' => 'jeton-en-attente',
        ]);

        // Une place non payée ne doit pas ouvrir un billet.
        $this->get(route('tickets.show', $registration->qr_token))->assertNotFound();
        $this->get(route('tickets.download', $registration->qr_token))->assertNotFound();
    }

    // ── Se réinscrire ne mène plus à une impasse ────────────────

    public function test_registering_twice_leads_to_the_ticket_not_an_error(): void
    {
        $event = $this->freeEvent();
        $this->post(route('events.register', $event->slug), $this->applicant());
        $registration = Registration::firstOrFail();

        $response = $this->post(route('events.register', $event->slug), $this->applicant());

        // C'est précisément quand on n'a pas reçu son QR qu'on réessaie.
        $response->assertRedirect(route('tickets.show', $registration->qr_token));
        $response->assertSessionMissing('error');
        $this->assertDatabaseCount('registrations', 1);
    }

    public function test_a_free_registration_stuck_as_pending_is_confirmed_on_retry(): void
    {
        $event = $this->freeEvent();
        Registration::create([
            'event_id' => $event->id, 'first_name' => 'Awa', 'last_name' => 'Traoré',
            'email' => 'awa@example.com', 'status' => 'pending',
        ]);

        $this->post(route('events.register', $event->slug), $this->applicant());

        $registration = Registration::firstOrFail();
        $this->assertSame('paid', $registration->status);
        $this->assertNotNull($registration->qr_token);
    }

    // ── Retrouver son billet par email ──────────────────────────

    public function test_a_registrant_can_ask_for_her_ticket_link(): void
    {
        $event = $this->freeEvent();
        $this->post(route('events.register', $event->slug), $this->applicant());

        $this->post(route('tickets.find.store'), ['email' => 'awa@example.com'])
            ->assertRedirect()->assertSessionHas('sent');

        Mail::assertSent(TicketLinkMail::class,
            fn (TicketLinkMail $mail) => $mail->hasTo('awa@example.com'));
    }

    public function test_the_ticket_link_email_is_never_queued(): void
    {
        $event = $this->freeEvent();
        $this->post(route('events.register', $event->slug), $this->applicant());

        $this->post(route('tickets.find.store'), ['email' => 'awa@example.com']);

        // C'est le recours de quelqu'un qui n'a pas reçu son email : le mettre
        // en file reproduirait la panne qu'il répare.
        Mail::assertNotQueued(TicketLinkMail::class);
    }

    public function test_an_unknown_email_gets_the_same_answer_and_no_mail(): void
    {
        $this->post(route('tickets.find.store'), ['email' => 'inconnue@example.com'])
            ->assertRedirect()->assertSessionHas('sent');

        // La réponse ne doit pas révéler qui est inscrit.
        Mail::assertNothingSent();
    }

    public function test_a_bot_cannot_harvest_ticket_links(): void
    {
        $event = $this->freeEvent();
        $this->post(route('events.register', $event->slug), $this->applicant());

        $this->post(route('tickets.find.store'), [
            'email' => 'awa@example.com', Honeypot::FIELD => 'https://spam.test',
        ])->assertRedirect();

        Mail::assertNotSent(TicketLinkMail::class);
    }

    public function test_a_past_event_ticket_is_not_resent(): void
    {
        $event = Event::factory()->create([
            'status' => 'completed', 'event_date' => now()->subMonth(), 'is_paid' => false,
        ]);
        Registration::create([
            'event_id' => $event->id, 'first_name' => 'Awa', 'last_name' => 'Traoré',
            'email' => 'awa@example.com', 'status' => 'attended', 'qr_token' => 'jeton-passe',
        ]);

        $this->post(route('tickets.find.store'), ['email' => 'awa@example.com']);

        Mail::assertNotSent(TicketLinkMail::class);
    }

    // ── La file d'attente est vidée ─────────────────────────────

    public function test_the_mail_queue_is_drained_by_the_scheduler(): void
    {
        $events = collect(app(Schedule::class)->events())
            ->filter(fn ($event) => str_contains($event->command ?? '', 'queue:work'));

        // Sans cette tâche, les confirmations restaient dans la table `jobs`
        // et personne ne recevait son QR.
        $this->assertCount(1, $events, 'La file d\'attente doit être vidée par l\'ordonnanceur.');
    }
}
