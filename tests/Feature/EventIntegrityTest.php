<?php

namespace Tests\Feature;

use App\Http\Controllers\Concerns\Honeypot;
use App\Mail\WaitingListSpotMail;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\User;
use App\Models\WaitingList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Intégrité du module événements, revue avant un lancement.
 *
 * Trois familles de défauts corrigées ici : un événement passé qui proposait
 * encore de s'inscrire, un tarif à zéro qui faisait passer un événement payant
 * dans le circuit gratuit, et un formulaire public sans piège anti-bot — par
 * lequel un robot pouvait remplir la capacité et afficher « complet » le jour J.
 */
class EventIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Storage::fake('local');
    }

    private function applicant(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Awa', 'last_name' => 'Traoré', 'email' => 'awa@example.com',
        ], $overrides);
    }

    // ── Événement passé ─────────────────────────────────────────

    public function test_a_past_event_is_closed_even_without_a_closing_date(): void
    {
        $event = Event::factory()->create([
            'status' => 'published',
            'event_date' => now()->subDay(),
            'registration_closes_at' => null,
        ]);

        // Sans ce test la fiche affichait « Réserver » sur un événement terminé.
        $this->assertTrue($event->hasTakenPlace());
        $this->assertFalse($event->registration_open);
    }

    public function test_a_past_event_page_offers_no_registration(): void
    {
        $event = Event::factory()->create(['status' => 'completed', 'event_date' => now()->subWeek()]);

        $html = $this->get(route('events.show', $event->slug))->assertOk()->getContent();

        $this->assertStringContainsString('Cet événement a déjà eu lieu', $html);
        $this->assertStringNotContainsString(route('events.register', $event->slug), $html);
        $this->assertStringNotContainsString(route('events.waiting-list', $event->slug), $html);
    }

    public function test_the_waiting_list_refuses_a_past_event(): void
    {
        $event = Event::factory()->create([
            'status' => 'published', 'event_date' => now()->subDay(), 'capacity' => 1,
        ]);
        Registration::create([
            'event_id' => $event->id, 'first_name' => 'A', 'last_name' => 'B',
            'email' => 'pleine@example.com', 'status' => 'paid',
        ]);

        $this->post(route('events.waiting-list', $event->slug), $this->applicant())
            ->assertRedirect();

        $this->assertDatabaseCount('waiting_list', 0);
    }

    public function test_a_draft_event_is_never_open(): void
    {
        $event = Event::factory()->create(['status' => 'draft', 'event_date' => now()->addMonth()]);

        $this->assertFalse($event->registration_open);
    }

    // ── Tarif ───────────────────────────────────────────────────

    public function test_a_paid_event_cannot_be_saved_with_a_zero_price(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => User::ROLE_OWNER]);

        // Un tarif à zéro sur un événement « payant » confirmerait des places
        // sans jamais encaisser : le circuit gratuit prend le relais en silence.
        $this->actingAs($admin)->post(route('admin.events.store'), [
            'title' => 'Forum', 'description' => 'Desc.',
            'event_date' => now()->addMonth()->format('Y-m-d H:i'),
            'location' => 'Abidjan', 'status' => 'published',
            'is_paid' => '1', 'price' => '0',
        ])->assertSessionHasErrors('price');

        $this->assertDatabaseCount('events', 0);
    }

    public function test_is_paid_event_requires_a_positive_price(): void
    {
        $zero = Event::factory()->create(['is_paid' => true, 'price' => 0]);
        $null = Event::factory()->create(['is_paid' => true, 'price' => null]);
        $real = Event::factory()->create(['is_paid' => true, 'price' => 5000]);

        $this->assertFalse($zero->isPaidEvent());
        $this->assertFalse($null->isPaidEvent());
        $this->assertTrue($real->isPaidEvent());
    }

    public function test_a_paid_event_never_confirms_a_seat_without_payment(): void
    {
        Http::fake(['*/payments' => Http::response([
            'success' => true,
            'data' => ['id' => 1, 'reference' => 'MTX-1', 'checkout_url' => 'https://gp.test/c/1', 'status' => 'pending'],
        ], 201)]);

        $event = Event::factory()->create([
            'status' => 'published', 'event_date' => now()->addMonth(),
            'is_paid' => true, 'price' => 5000, 'currency' => 'XOF',
        ]);

        $this->post(route('events.register', $event->slug), $this->applicant())
            ->assertRedirect('https://gp.test/c/1');

        // La place reste « en attente » jusqu'à l'encaissement.
        $this->assertSame('pending', Registration::firstOrFail()->status);
        $this->assertSame('5000.00', Payment::firstOrFail()->amount);
    }

    // ── Robots ──────────────────────────────────────────────────

    public function test_the_registration_form_carries_a_trap(): void
    {
        $event = Event::factory()->create(['status' => 'published', 'event_date' => now()->addMonth()]);

        $html = $this->get(route('events.show', $event->slug))->assertOk()->getContent();

        $this->assertStringContainsString('name="'.Honeypot::FIELD.'"', $html);
    }

    public function test_a_bot_cannot_consume_a_seat(): void
    {
        $event = Event::factory()->create([
            'status' => 'published', 'event_date' => now()->addMonth(), 'capacity' => 2,
        ]);

        $this->post(route('events.register', $event->slug),
            $this->applicant([Honeypot::FIELD => 'https://spam.test']))->assertRedirect();

        // Une inscription en attente occupe une place : un robot pouvait afficher
        // « complet » avant la première vraie inscrite.
        $this->assertDatabaseCount('registrations', 0);
        $this->assertSame(2, $event->fresh()->spots_left);
    }

    public function test_a_bot_cannot_flood_the_waiting_list(): void
    {
        $event = Event::factory()->create([
            'status' => 'published', 'event_date' => now()->addMonth(), 'capacity' => 1,
        ]);
        Registration::create([
            'event_id' => $event->id, 'first_name' => 'A', 'last_name' => 'B',
            'email' => 'pleine@example.com', 'status' => 'paid',
        ]);

        $this->post(route('events.waiting-list', $event->slug),
            $this->applicant([Honeypot::FIELD => 'https://spam.test']))->assertRedirect();

        $this->assertDatabaseCount('waiting_list', 0);
    }

    public function test_a_human_is_still_registered(): void
    {
        $event = Event::factory()->create(['status' => 'published', 'event_date' => now()->addMonth()]);

        $this->post(route('events.register', $event->slug), $this->applicant())->assertRedirect();

        $this->assertDatabaseCount('registrations', 1);
    }

    // ── Liste d'attente ─────────────────────────────────────────

    public function test_an_expired_unpaid_registration_frees_a_seat_and_warns_the_waiting_list(): void
    {
        $event = Event::factory()->create([
            'status' => 'published', 'event_date' => now()->addMonth(),
            'is_paid' => true, 'price' => 5000, 'capacity' => 1,
        ]);

        $abandoned = Registration::create([
            'event_id' => $event->id, 'first_name' => 'A', 'last_name' => 'B',
            'email' => 'abandon@example.com', 'status' => 'pending',
        ]);
        $abandoned->forceFill(['created_at' => now()->subHour()])->save();

        $waiting = WaitingList::create([
            'event_id' => $event->id, 'first_name' => 'C', 'last_name' => 'D',
            'email' => 'attente@example.com',
        ]);

        $this->assertTrue($event->fresh()->is_sold_out);

        $this->artisan('registrations:expire-unpaid')->assertSuccessful();

        $this->assertSame('cancelled', $abandoned->fresh()->status);
        $this->assertFalse($event->fresh()->is_sold_out);

        // C'est l'expiration qui libère le plus de places, et elle ne prévenait personne.
        Mail::assertQueued(WaitingListSpotMail::class,
            fn (WaitingListSpotMail $mail) => $mail->hasTo('attente@example.com'));
        $this->assertTrue($waiting->fresh()->notified);
    }

    public function test_no_more_people_are_warned_than_there_are_seats(): void
    {
        $event = Event::factory()->create([
            'status' => 'published', 'event_date' => now()->addMonth(),
            'is_paid' => true, 'price' => 5000, 'capacity' => 2,
        ]);

        foreach (['a@example.com', 'b@example.com'] as $email) {
            $r = Registration::create([
                'event_id' => $event->id, 'first_name' => 'X', 'last_name' => 'Y',
                'email' => $email, 'status' => 'pending',
            ]);
            $r->forceFill(['created_at' => now()->subHour()])->save();
        }

        foreach (['w1@example.com', 'w2@example.com', 'w3@example.com'] as $email) {
            WaitingList::create([
                'event_id' => $event->id, 'first_name' => 'W', 'last_name' => 'Z', 'email' => $email,
            ]);
        }

        $this->artisan('registrations:expire-unpaid')->assertSuccessful();

        // Deux places libérées, deux personnes prévenues : promettre une place à
        // trois personnes pour deux sièges serait pire que de se taire.
        Mail::assertQueued(WaitingListSpotMail::class, 2);
        $this->assertSame(2, WaitingList::where('notified', true)->count());
    }

    public function test_nobody_is_warned_when_no_seat_was_freed(): void
    {
        $event = Event::factory()->create([
            'status' => 'published', 'event_date' => now()->addMonth(),
            'is_paid' => true, 'price' => 5000, 'capacity' => 1,
        ]);

        // Inscription payée : elle n'expire pas, la place reste occupée.
        Registration::create([
            'event_id' => $event->id, 'first_name' => 'A', 'last_name' => 'B',
            'email' => 'payee@example.com', 'status' => 'paid',
        ]);
        WaitingList::create([
            'event_id' => $event->id, 'first_name' => 'W', 'last_name' => 'Z', 'email' => 'w@example.com',
        ]);

        $this->artisan('registrations:expire-unpaid')->assertSuccessful();

        Mail::assertNotQueued(WaitingListSpotMail::class);
    }
}
