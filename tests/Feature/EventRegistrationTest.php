<?php

namespace Tests\Feature;

use App\Mail\WaitingListSpotMail;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use App\Models\WaitingList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EventRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $override = []): array
    {
        return array_merge([
            'first_name' => 'Awa',
            'last_name' => 'Traoré',
            'email' => 'awa@example.com',
            'phone' => '+2250700000000',
        ], $override);
    }

    public function test_registration_is_blocked_when_event_is_sold_out(): void
    {
        $event = Event::factory()->withCapacity(1)->create();
        Registration::create([
            'event_id' => $event->id, 'first_name' => 'A', 'last_name' => 'B',
            'email' => 'first@example.com', 'status' => 'pending',
        ]);

        $this->post(route('events.register', $event->slug), $this->payload())
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('registrations', ['email' => 'awa@example.com']);
    }

    public function test_registration_is_blocked_for_past_event(): void
    {
        $event = Event::factory()->past()->create();

        $this->post(route('events.register', $event->slug), $this->payload())
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('registrations', ['email' => 'awa@example.com']);
    }

    public function test_duplicate_registration_is_prevented_and_email_normalised(): void
    {
        $event = Event::factory()->create();

        $this->post(route('events.register', $event->slug), $this->payload(['email' => 'Awa@Example.com ']))
            ->assertSessionHas('success');

        // Même email avec casse/espaces différents → refusé.
        $this->post(route('events.register', $event->slug), $this->payload(['email' => 'awa@example.com']))
            ->assertSessionHas('error');

        $this->assertSame(1, Registration::where('event_id', $event->id)->count());
        $this->assertDatabaseHas('registrations', ['email' => 'awa@example.com']);
    }

    public function test_valid_registration_succeeds(): void
    {
        $event = Event::factory()->withCapacity(10)->create();

        $this->post(route('events.register', $event->slug), $this->payload())
            ->assertSessionHas('success');

        $this->assertDatabaseHas('registrations', ['event_id' => $event->id, 'email' => 'awa@example.com', 'status' => 'pending']);
    }

    public function test_cancelling_a_registration_notifies_the_waiting_list(): void
    {
        Mail::fake();

        $event = Event::factory()->withCapacity(1)->create();
        $registration = Registration::create([
            'event_id' => $event->id, 'first_name' => 'A', 'last_name' => 'B',
            'email' => 'taken@example.com', 'status' => 'paid',
        ]);
        $waiting = WaitingList::create([
            'event_id' => $event->id, 'first_name' => 'Next', 'last_name' => 'InLine',
            'email' => 'next@example.com',
        ]);

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->post(route('admin.registrations.cancel', $registration))
            ->assertRedirect();

        Mail::assertQueued(WaitingListSpotMail::class);
        $this->assertTrue($waiting->refresh()->notified);
    }
}
