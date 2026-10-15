<?php

namespace Tests\Feature;

use App\Mail\BirthdayAnnouncementMail;
use App\Mail\BirthdayDateRequestMail;
use App\Models\Member;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Faire circuler la fête : prévenir les autres membres le jour J, et
 * demander leur date à celles qui ne l'ont pas encore donnée.
 */
class BirthdayAnnouncementTest extends TestCase
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
        ], $overrides));
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'role' => User::ROLE_OWNER]);
    }

    // ── Annonce du jour J ───────────────────────────────────────

    public function test_the_other_members_receive_the_wall_link(): void
    {
        $celebrant = $this->celebrant();
        $other = Member::factory()->create(['status' => 'active']);

        $this->artisan('members:birthday')->assertSuccessful();

        $wall = route('birthdays.page', $celebrant->fresh()->birthday_token);

        Mail::assertQueued(BirthdayAnnouncementMail::class, function (BirthdayAnnouncementMail $mail) use ($other, $celebrant, $wall) {
            return $mail->hasTo($other->email)
                && $mail->celebrants->pluck('id')->all() === [$celebrant->id]
                && str_contains($mail->render(), $wall);
        });
    }

    public function test_the_celebrant_does_not_receive_her_own_announcement(): void
    {
        $celebrant = $this->celebrant();
        Member::factory()->create(['status' => 'active']);

        $this->artisan('members:birthday');

        Mail::assertNotQueued(BirthdayAnnouncementMail::class,
            fn (BirthdayAnnouncementMail $mail) => $mail->hasTo($celebrant->email));
    }

    public function test_inactive_and_opted_out_members_are_not_told(): void
    {
        $this->celebrant();
        $rejected = Member::factory()->create(['status' => 'rejected']);
        $optedOut = Member::factory()->create(['status' => 'active', 'marketing_opt_out_at' => now()]);

        $this->artisan('members:birthday');

        foreach ([$rejected, $optedOut] as $member) {
            Mail::assertNotQueued(BirthdayAnnouncementMail::class,
                fn (BirthdayAnnouncementMail $mail) => $mail->hasTo($member->email));
        }
    }

    public function test_two_birthdays_the_same_day_make_a_single_email_per_member(): void
    {
        $a = $this->celebrant(['name' => 'Awa Koné']);
        $b = $this->celebrant(['name' => 'Bintou Traoré']);
        $other = Member::factory()->create(['status' => 'active']);

        $this->artisan('members:birthday');

        Mail::assertQueued(BirthdayAnnouncementMail::class,
            fn (BirthdayAnnouncementMail $mail) => $mail->hasTo($other->email) && $mail->celebrants->count() === 2);
        $this->assertCount(1, Mail::queued(BirthdayAnnouncementMail::class,
            fn (BirthdayAnnouncementMail $mail) => $mail->hasTo($other->email)));

        // Chacune est prévenue de l'autre, jamais d'elle-même.
        Mail::assertQueued(BirthdayAnnouncementMail::class,
            fn (BirthdayAnnouncementMail $mail) => $mail->hasTo($a->email) && $mail->celebrants->pluck('id')->all() === [$b->id]);
        Mail::assertQueued(BirthdayAnnouncementMail::class,
            fn (BirthdayAnnouncementMail $mail) => $mail->hasTo($b->email) && $mail->celebrants->pluck('id')->all() === [$a->id]);
    }

    public function test_a_second_run_the_same_day_announces_nothing(): void
    {
        $this->celebrant();
        Member::factory()->create(['status' => 'active']);

        $this->artisan('members:birthday');
        $count = count(Mail::queued(BirthdayAnnouncementMail::class));

        $this->artisan('members:birthday');

        $this->assertSame($count, count(Mail::queued(BirthdayAnnouncementMail::class)));
    }

    public function test_no_birthday_no_announcement(): void
    {
        Member::factory()->count(3)->create(['status' => 'active']);

        $this->artisan('members:birthday');

        Mail::assertNothingQueued();
    }

    // ── Demande de date par l'administration ────────────────────

    public function test_the_admin_sends_the_link_to_members_without_a_date(): void
    {
        $missing = Member::factory()->create(['status' => 'active', 'birth_day' => null, 'birth_month' => null]);
        $known = Member::factory()->create(['status' => 'active', 'birth_day' => 3, 'birth_month' => 4]);
        $optedOut = Member::factory()->create(['status' => 'active', 'birth_day' => null, 'marketing_opt_out_at' => now()]);
        $pending = Member::factory()->create(['status' => 'pending', 'birth_day' => null]);

        $this->actingAs($this->admin())
            ->post(route('admin.birthdays.invite'))
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertQueued(BirthdayDateRequestMail::class, function (BirthdayDateRequestMail $mail) use ($missing) {
            return $mail->hasTo($missing->email)
                && str_contains($mail->render(), e(route('birthdays.collect', [
                    'token' => Setting::birthdayToken(),
                    'membre' => $missing->id,
                ])));
        });

        foreach ([$known, $optedOut, $pending] as $member) {
            Mail::assertNotQueued(BirthdayDateRequestMail::class,
                fn (BirthdayDateRequestMail $mail) => $mail->hasTo($member->email));
        }

        $this->assertNotNull($missing->fresh()->birthday_invited_at);
    }

    public function test_a_double_click_does_not_send_the_email_twice(): void
    {
        $missing = Member::factory()->create(['status' => 'active', 'birth_day' => null]);
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.birthdays.invite'));
        $this->actingAs($admin)->post(route('admin.birthdays.invite'))->assertSessionHas('error');

        $this->assertCount(1, Mail::queued(BirthdayDateRequestMail::class,
            fn (BirthdayDateRequestMail $mail) => $mail->hasTo($missing->email)));
    }

    public function test_a_member_can_be_reminded_after_the_cooldown(): void
    {
        $missing = Member::factory()->create([
            'status' => 'active',
            'birth_day' => null,
            'birthday_invited_at' => now()->subDays(Member::BIRTHDAY_INVITE_COOLDOWN_DAYS + 1),
        ]);

        $this->actingAs($this->admin())->post(route('admin.birthdays.invite'));

        Mail::assertQueued(BirthdayDateRequestMail::class,
            fn (BirthdayDateRequestMail $mail) => $mail->hasTo($missing->email));
    }

    public function test_an_editor_cannot_send_the_invitations(): void
    {
        Member::factory()->create(['status' => 'active', 'birth_day' => null]);
        $editor = User::factory()->create(['is_admin' => true, 'role' => User::ROLE_EDITOR]);

        $this->actingAs($editor)->post(route('admin.birthdays.invite'))->assertForbidden();

        Mail::assertNothingQueued();
    }

    public function test_the_emailed_link_opens_her_own_form(): void
    {
        $missing = Member::factory()->create(['status' => 'active', 'birth_day' => null, 'name' => 'Mariam Diallo']);

        $html = $this->get(route('birthdays.collect', ['token' => Setting::birthdayToken(), 'membre' => $missing->id]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('open: true', $html);
        $this->assertStringContainsString('member: '.$missing->id, $html);
    }
}
