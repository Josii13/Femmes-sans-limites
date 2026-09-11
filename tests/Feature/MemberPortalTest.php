<?php

namespace Tests\Feature;

use App\Mail\MemberPasswordSetupMail;
use App\Models\Ebook;
use App\Models\Event;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Espace membre.
 *
 * Jusqu'ici une adhérente recevait sa carte par email et n'avait plus aucun
 * accès : ni à sa date d'échéance, ni à ses inscriptions, ni à ses achats.
 *
 * Le point sensible est l'étanchéité : le guard « member » et le guard « web »
 * du back-office sont distincts, et aucune connexion ne doit franchir la
 * frontière.
 */
class MemberPortalTest extends TestCase
{
    use RefreshDatabase;

    private function activeMember(array $overrides = []): Member
    {
        $member = Member::factory()->create(array_merge([
            'status' => 'active',
            'expires_at' => now()->addMonths(6),
        ], $overrides));

        $member->forceFill(['password' => Hash::make('motdepasse-solide')])->save();

        return $member->fresh();
    }

    // ── Connexion ───────────────────────────────────────────────

    public function test_an_active_member_can_log_in(): void
    {
        $member = $this->activeMember();

        $this->post(route('member.login.store'), [
            'email' => $member->email, 'password' => 'motdepasse-solide',
        ])->assertRedirect(route('member.dashboard'));

        $this->assertAuthenticatedAs($member, 'member');
        $this->assertNotNull($member->fresh()->last_login_at);
    }

    public function test_a_wrong_password_is_refused(): void
    {
        $member = $this->activeMember();

        $this->post(route('member.login.store'), [
            'email' => $member->email, 'password' => 'mauvais',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('member');
    }

    public function test_the_error_message_does_not_reveal_who_is_a_member(): void
    {
        $member = $this->activeMember();

        // Un message différent entre « adresse inconnue » et « mot de passe faux »
        // permettrait de savoir qui adhère à l'association.
        $expected = 'Ces identifiants ne correspondent à aucun compte actif.';

        $this->post(route('member.login.store'), [
            'email' => 'inconnue@example.com', 'password' => 'peu-importe',
        ])->assertSessionHasErrors(['email' => $expected]);

        $this->post(route('member.login.store'), [
            'email' => $member->email, 'password' => 'mauvais',
        ])->assertSessionHasErrors(['email' => $expected]);
    }

    public function test_a_pending_application_cannot_log_in(): void
    {
        $member = $this->activeMember(['status' => 'pending']);

        $this->post(route('member.login.store'), [
            'email' => $member->email, 'password' => 'motdepasse-solide',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('member');
    }

    public function test_an_expired_membership_cannot_log_in(): void
    {
        $member = $this->activeMember(['expires_at' => now()->subDay()]);

        $this->post(route('member.login.store'), [
            'email' => $member->email, 'password' => 'motdepasse-solide',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('member');
    }

    public function test_a_member_without_a_password_cannot_log_in(): void
    {
        $member = Member::factory()->create(['status' => 'active', 'expires_at' => now()->addYear()]);

        $this->assertFalse($member->canAccessPortal());
        $this->post(route('member.login.store'), [
            'email' => $member->email, 'password' => '',
        ])->assertSessionHasErrors();

        $this->assertGuest('member');
    }

    public function test_repeated_failures_are_throttled(): void
    {
        $member = $this->activeMember();

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('member.login.store'), ['email' => $member->email, 'password' => 'faux']);
        }

        $response = $this->post(route('member.login.store'), [
            'email' => $member->email, 'password' => 'motdepasse-solide',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('member', 'Le bon mot de passe ne doit pas passer pendant le blocage.');
    }

    // ── Étanchéité des deux espaces ─────────────────────────────

    /**
     * Une membre authentifiée ne doit jamais atteindre le back-office.
     *
     * Le refus est un 403 : le middleware interroge explicitement le guard « web »,
     * qui ne contient aucune administratrice. Avant ce correctif il appelait
     * isAdmin() sur l'identité du guard par défaut — donc sur un Member — et
     * renvoyait une erreur 500 au lieu d'un refus.
     */
    public function test_a_member_session_gives_no_access_to_the_back_office(): void
    {
        $member = $this->activeMember();
        $this->actingAs($member, 'member');

        $this->get(route('admin.dashboard'))->assertForbidden();
        $this->get(route('admin.members.index'))->assertForbidden();
    }

    public function test_an_admin_session_gives_no_access_to_the_member_portal(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $this->get(route('member.dashboard'))->assertRedirect();
    }

    public function test_the_portal_requires_a_connection(): void
    {
        foreach (['member.dashboard', 'member.registrations', 'member.purchases', 'member.directory', 'member.profile', 'member.renewal'] as $route) {
            $this->get(route($route))->assertRedirect();
        }
    }

    // ── Contenu de l'espace ─────────────────────────────────────

    public function test_the_dashboard_shows_the_membership_details(): void
    {
        $member = $this->activeMember(['type' => 'gold']);

        $html = $this->actingAs($member, 'member')->get(route('member.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString($member->member_number, $html);
        $this->assertStringContainsString($member->expires_at->translatedFormat('d F Y'), $html);
    }

    public function test_a_member_sees_her_own_registrations_only(): void
    {
        $member = $this->activeMember();
        $event = Event::factory()->create(['title' => 'Forum Leadership', 'event_date' => now()->addMonth()]);

        Registration::create([
            'event_id' => $event->id, 'first_name' => 'A', 'last_name' => 'B',
            'email' => $member->email, 'status' => 'paid',
        ]);
        $otherEvent = Event::factory()->create(['title' => 'Atelier Confidentiel']);
        Registration::create([
            'event_id' => $otherEvent->id, 'first_name' => 'C', 'last_name' => 'D',
            'email' => 'autre@example.com', 'status' => 'paid',
        ]);

        $html = $this->actingAs($member, 'member')->get(route('member.registrations'))->assertOk()->getContent();

        $this->assertStringContainsString('Forum Leadership', $html);
        $this->assertStringNotContainsString('Atelier Confidentiel', $html);
    }

    public function test_a_member_can_redownload_her_ebooks(): void
    {
        Storage::fake('local');
        $member = $this->activeMember();
        $ebook = Ebook::factory()->sellable()->create(['title' => 'Guide du leadership']);
        Storage::disk('local')->put($ebook->file_path, '%PDF');

        Payment::create([
            'provider' => 'geniuspay', 'reference' => 'ref-1', 'status' => 'completed',
            'amount' => 5000, 'currency' => 'XOF',
            'customer_name' => $member->name, 'customer_email' => $member->email,
            'payable_type' => $ebook->getMorphClass(), 'payable_id' => $ebook->id,
        ]);

        $html = $this->actingAs($member, 'member')->get(route('member.purchases'))->assertOk()->getContent();

        $this->assertStringContainsString('Guide du leadership', $html);
        // Un lien régénéré : celui reçu par email expire au bout de 30 jours.
        $this->assertStringContainsString('signature=', $html);
    }

    // ── Profil ──────────────────────────────────────────────────

    public function test_a_member_can_update_her_profile(): void
    {
        $member = $this->activeMember();

        $this->actingAs($member, 'member')->put(route('member.profile.update'), [
            'name' => 'Awa Traoré', 'profession' => 'Architecte',
            'country' => 'Sénégal', 'city' => 'Dakar', 'phone' => '+221 77 000 00 00',
            'show_in_gallery' => '1', 'marketing_opt_in' => '1',
        ])->assertSessionHasNoErrors()->assertRedirect(route('member.profile'));

        $member->refresh();
        $this->assertSame('Architecte', $member->profession);
        $this->assertSame('Dakar', $member->city);
    }

    public function test_a_member_cannot_change_her_email_from_the_portal(): void
    {
        $member = $this->activeMember();
        $original = $member->email;

        $this->actingAs($member, 'member')->put(route('member.profile.update'), [
            'name' => $member->name, 'profession' => 'Coach',
            'country' => 'CI', 'city' => 'Abidjan',
            'email' => 'nouvelle@example.com',
        ]);

        // L'email identifie l'adhésion et relie inscriptions et achats.
        $this->assertSame($original, $member->fresh()->email);
    }

    public function test_a_member_can_opt_out_of_marketing_from_her_profile(): void
    {
        $member = $this->activeMember();
        $this->assertFalse($member->hasOptedOutOfMarketing());

        $this->actingAs($member, 'member')->put(route('member.profile.update'), [
            'name' => $member->name, 'profession' => 'Coach',
            'country' => 'CI', 'city' => 'Abidjan',
            'marketing_opt_in' => '0',
        ])->assertSessionHasNoErrors();

        $this->assertTrue($member->fresh()->hasOptedOutOfMarketing());
    }

    public function test_changing_the_password_requires_the_current_one(): void
    {
        $member = $this->activeMember();

        $this->actingAs($member, 'member')->put(route('member.password.update'), [
            'current_password' => 'mauvais',
            'password' => 'nouveau-mot-de-passe', 'password_confirmation' => 'nouveau-mot-de-passe',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('motdepasse-solide', $member->fresh()->password));
    }

    public function test_a_member_can_change_her_password(): void
    {
        $member = $this->activeMember();

        $this->actingAs($member, 'member')->put(route('member.password.update'), [
            'current_password' => 'motdepasse-solide',
            'password' => 'nouveau-mot-de-passe', 'password_confirmation' => 'nouveau-mot-de-passe',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('nouveau-mot-de-passe', $member->fresh()->password));
    }

    // ── Définition du mot de passe ──────────────────────────────

    public function test_activation_invites_the_member_to_set_her_password(): void
    {
        Mail::fake();
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);
        $member = Member::factory()->create(['status' => 'pending']);

        $this->actingAs($admin)->post(route('admin.members.activate', $member))->assertRedirect();

        Mail::assertQueued(MemberPasswordSetupMail::class,
            fn (MemberPasswordSetupMail $mail) => $mail->hasTo($member->email) && $mail->isFirstTime);
    }

    public function test_a_member_who_already_has_a_password_is_not_invited_again(): void
    {
        Mail::fake();
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);
        $member = $this->activeMember(['status' => 'pending']);

        $this->actingAs($admin)->post(route('admin.members.activate', $member))->assertRedirect();

        Mail::assertNotQueued(MemberPasswordSetupMail::class);
    }

    public function test_the_reset_form_never_reveals_whether_an_email_is_known(): void
    {
        Mail::fake();
        $member = $this->activeMember();

        $known = $this->post(route('member.password.email'), ['email' => $member->email]);
        $unknown = $this->post(route('member.password.email'), ['email' => 'inconnue@example.com']);

        $this->assertSame($known->getSession()->get('status'), $unknown->getSession()->get('status'));
        Mail::assertQueued(MemberPasswordSetupMail::class, 1);
    }

    public function test_logout_ends_the_session(): void
    {
        $member = $this->activeMember();

        $this->actingAs($member, 'member')->post(route('member.logout'))->assertRedirect(route('home'));

        $this->assertGuest('member');
    }
}
