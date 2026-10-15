<?php

namespace Tests\Feature;

use App\Http\Controllers\Concerns\Honeypot;
use App\Mail\BirthdayMail;
use App\Mail\BirthdayWishesDigestMail;
use App\Models\BirthdayWish;
use App\Models\Member;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Souhaiter l'anniversaire : la carte, l'email, le mur de vœux.
 *
 * Ce qui compte ici n'est pas qu'un email parte, c'est qu'il ne parte pas aux
 * mauvaises personnes : une adhésion rejetée qui recevrait « joyeux
 * anniversaire » serait humiliante, et un envoi en double gâche le geste.
 */
class BirthdayGreetingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Storage::fake('public');
    }

    private function celebrant(array $overrides = []): Member
    {
        return Member::factory()->create(array_merge([
            'status' => 'active',
            'birth_day' => (int) now()->format('j'),
            'birth_month' => (int) now()->format('n'),
            'birth_year' => 1990,
        ], $overrides));
    }

    // ── L'envoi du jour ─────────────────────────────────────────

    public function test_the_celebrant_receives_her_card(): void
    {
        $member = $this->celebrant();

        $this->artisan('members:birthday')->assertSuccessful();

        Mail::assertQueued(BirthdayMail::class,
            fn (BirthdayMail $mail) => $mail->hasTo($member->email));

        $member->refresh();
        $this->assertNotNull($member->birthday_greeted_at);
        $this->assertNotNull($member->birthday_card_path);
        Storage::disk('public')->assertExists($member->birthday_card_path);
    }

    public function test_the_card_is_a_square_image(): void
    {
        $member = $this->celebrant();

        $this->artisan('members:birthday');

        // Format carré : c'est celui d'un statut WhatsApp. Une carte au format
        // paysage serait rognée de travers à la première reprise.
        [$w, $h] = getimagesizefromstring(
            Storage::disk('public')->get($member->fresh()->birthday_card_path)
        );
        $this->assertSame($w, $h);
        $this->assertSame(1080, $w);
    }

    public function test_nobody_is_greeted_twice_the_same_year(): void
    {
        $this->celebrant();

        $this->artisan('members:birthday');
        $this->artisan('members:birthday');

        // L'ordonnanceur peut repasser : le geste ne doit pas se répéter.
        Mail::assertQueuedCount(1);
    }

    public function test_a_member_greeted_last_year_is_greeted_again(): void
    {
        $this->celebrant(['birthday_greeted_at' => now()->subYear()]);

        $this->artisan('members:birthday');

        Mail::assertQueued(BirthdayMail::class);
    }

    public function test_only_active_members_are_greeted(): void
    {
        $this->celebrant(['status' => 'rejected']);
        $this->celebrant(['status' => 'expired', 'email' => 'expiree@example.com']);

        $this->artisan('members:birthday')->assertSuccessful();

        // Fêter quelqu'un dont la candidature a été rejetée serait humiliant.
        Mail::assertNothingQueued();
    }

    public function test_a_member_without_a_date_is_never_greeted(): void
    {
        Member::factory()->create(['status' => 'active', 'birth_day' => null, 'birth_month' => null]);

        $this->artisan('members:birthday')->assertSuccessful();

        Mail::assertNothingQueued();
    }

    public function test_a_birthday_on_another_day_is_not_greeted_today(): void
    {
        $this->celebrant([
            'birth_day' => (int) now()->addDays(3)->format('j'),
            'birth_month' => (int) now()->addDays(3)->format('n'),
        ]);

        $this->artisan('members:birthday');

        Mail::assertNothingQueued();
    }

    public function test_the_dry_run_sends_nothing(): void
    {
        $this->celebrant();

        $this->artisan('members:birthday', ['--dry' => true])->assertSuccessful();

        Mail::assertNothingQueued();
        $this->assertNull(Member::first()->birthday_greeted_at);
    }

    // ── La page et le mur ───────────────────────────────────────

    public function test_the_page_is_closed_before_the_day(): void
    {
        $member = $this->celebrant();
        $token = $member->birthdayToken();

        // Avant l'envoi, le lien ne doit rien révéler — pas même qu'un
        // anniversaire approche.
        $this->get(route('birthdays.page', $token))->assertNotFound();
    }

    public function test_the_page_shows_the_card_and_the_wall(): void
    {
        $member = $this->celebrant(['name' => 'Aïcha Koné']);
        $this->artisan('members:birthday');

        $html = $this->get(route('birthdays.page', $member->fresh()->birthday_token))
            ->assertOk()->getContent();

        $this->assertStringContainsString('Aïcha', $html);
        $this->assertStringContainsString('Le mur de', $html);
        $this->assertStringContainsString('noindex', $html);
    }

    public function test_the_community_can_leave_a_wish(): void
    {
        $member = $this->celebrant();
        $this->artisan('members:birthday');
        $token = $member->fresh()->birthday_token;

        $this->post(route('birthdays.wish', $token), [
            'author_name' => 'Mariam', 'message' => 'Belle année à toi !',
        ])->assertRedirect()->assertSessionHas('wished');

        $this->assertDatabaseHas('birthday_wishes', [
            'member_id' => $member->id, 'author_name' => 'Mariam',
        ]);

        $this->assertStringContainsString('Belle année à toi !',
            $this->get(route('birthdays.page', $token))->getContent());
    }

    public function test_wishes_close_after_a_week(): void
    {
        $member = $this->celebrant();
        $this->artisan('members:birthday');
        $member->refresh()->forceFill(['birthday_greeted_at' => now()->subDays(8)])->save();

        // Une boîte à messages ouverte toute l'année finirait en dépotoir.
        $this->post(route('birthdays.wish', $member->birthday_token), [
            'author_name' => 'Mariam', 'message' => 'Trop tard !',
        ])->assertSessionHas('error');

        $this->assertDatabaseCount('birthday_wishes', 0);
    }

    public function test_a_bot_cannot_fill_the_wall(): void
    {
        $member = $this->celebrant();
        $this->artisan('members:birthday');

        $this->post(route('birthdays.wish', $member->fresh()->birthday_token), [
            'author_name' => 'Spam', 'message' => 'https://spam.test',
            Honeypot::FIELD => 'https://spam.test',
        ])->assertRedirect();

        $this->assertDatabaseCount('birthday_wishes', 0);
    }

    public function test_a_hidden_wish_disappears_from_the_wall(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => User::ROLE_OWNER]);
        $member = $this->celebrant();
        $this->artisan('members:birthday');
        $token = $member->fresh()->birthday_token;

        $this->post(route('birthdays.wish', $token), [
            'author_name' => 'Mariam', 'message' => 'Message déplacé',
        ]);
        $wish = BirthdayWish::firstOrFail();

        $this->actingAs($admin)->post(route('admin.birthdays.wish-toggle', $wish))->assertRedirect();

        $this->assertStringNotContainsString('Message déplacé',
            $this->get(route('birthdays.page', $token))->getContent());

        // Masqué, pas détruit : la décision doit pouvoir se défaire.
        $this->assertDatabaseCount('birthday_wishes', 1);
        $this->assertNotNull($wish->fresh()->hidden_at);
    }

    // ── Le récapitulatif du lendemain ───────────────────────────

    public function test_the_wishes_are_sent_back_the_next_day(): void
    {
        $member = $this->celebrant();
        $this->artisan('members:birthday');
        $member->forceFill(['birthday_greeted_at' => now()->subDays(2)])->save();

        BirthdayWish::create([
            'member_id' => $member->id, 'author_name' => 'Mariam', 'message' => 'Belle année !',
        ]);

        $this->artisan('members:birthday')->assertSuccessful();

        Mail::assertQueued(BirthdayWishesDigestMail::class,
            fn (BirthdayWishesDigestMail $mail) => $mail->hasTo($member->email));
        $this->assertNotNull($member->fresh()->birthday_digest_sent_at);
    }

    public function test_no_digest_without_wishes(): void
    {
        $member = $this->celebrant();
        $this->artisan('members:birthday');
        $member->forceFill(['birthday_greeted_at' => now()->subDays(2)])->save();

        $this->artisan('members:birthday');

        // Un email « voici tes messages » sans message serait cruel.
        Mail::assertNotQueued(BirthdayWishesDigestMail::class);
    }

    public function test_the_digest_is_sent_only_once(): void
    {
        $member = $this->celebrant();
        $this->artisan('members:birthday');
        $member->forceFill(['birthday_greeted_at' => now()->subDays(2)])->save();
        BirthdayWish::create([
            'member_id' => $member->id, 'author_name' => 'Mariam', 'message' => 'Belle année !',
        ]);

        $this->artisan('members:birthday');
        $this->artisan('members:birthday');

        Mail::assertQueuedCount(2); // la carte + un seul récapitulatif
    }

    // ── Planification ───────────────────────────────────────────

    public function test_the_greeting_runs_every_morning(): void
    {
        $events = collect(app(Schedule::class)->events())
            ->filter(fn ($event) => str_contains($event->command ?? '', 'members:birthday'));

        $this->assertCount(1, $events, 'Les anniversaires doivent être planifiés.');
    }
}
