<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TotpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Rôles et double authentification du back-office.
 *
 * Le panneau ne connaissait qu'un booléen `is_admin` : toute personne y ayant
 * accès pouvait tout faire — consulter les paiements, exporter les membres,
 * envoyer des campagnes. Et aucun second facteur ne le protégeait, alors qu'il
 * contient des données personnelles et des références de paiement.
 */
class AdminSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::factory()->create(['is_admin' => true, 'role' => $role]);
    }

    // ── Cloisonnement par rôle ──────────────────────────────────

    public function test_an_editor_cannot_reach_personal_or_financial_data(): void
    {
        $editor = $this->user(User::ROLE_EDITOR);

        foreach ([
            'admin.members.index',
            'admin.sales.index',
            'admin.communication.index',
        ] as $route) {
            $this->actingAs($editor)->get(route($route))->assertForbidden();
        }
    }

    public function test_an_editor_can_manage_editorial_content(): void
    {
        $editor = $this->user(User::ROLE_EDITOR);

        foreach ([
            'admin.ebooks.index',
            'admin.events.index',
            'admin.testimonials.index',
            'admin.site-images.index',
        ] as $route) {
            $this->actingAs($editor)->get(route($route))->assertOk();
        }
    }

    public function test_only_the_owner_manages_accounts_and_pricing(): void
    {
        foreach ([User::ROLE_EDITOR, User::ROLE_ADMIN] as $role) {
            $user = $this->user($role);
            $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.membership-plans.index'))->assertForbidden();
        }

        $owner = $this->user(User::ROLE_OWNER);
        $this->actingAs($owner)->get(route('admin.users.index'))->assertOk();
        $this->actingAs($owner)->get(route('admin.membership-plans.index'))->assertOk();
    }

    public function test_an_admin_reaches_operations_but_not_accounts(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);

        $this->actingAs($admin)->get(route('admin.members.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.sales.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.users.index'))->assertForbidden();
    }

    // ── Gestion des comptes ─────────────────────────────────────

    public function test_a_new_account_gets_a_link_instead_of_a_password(): void
    {
        Mail::fake();
        $owner = $this->user(User::ROLE_OWNER);

        $this->actingAs($owner)->post(route('admin.users.store'), [
            'name' => 'Awa Traoré', 'email' => 'Awa@Example.com', 'role' => User::ROLE_EDITOR,
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.users.index'));

        // Un mot de passe transmis par un tiers finit partagé : on n'en choisit aucun.
        $this->assertDatabaseHas('users', ['email' => 'awa@example.com', 'role' => User::ROLE_EDITOR]);
        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'awa@example.com']);
    }

    public function test_the_last_owner_cannot_be_demoted(): void
    {
        $owner = $this->user(User::ROLE_OWNER);

        $this->actingAs($owner)->put(route('admin.users.update', $owner), [
            'name' => $owner->name, 'email' => $owner->email, 'role' => User::ROLE_ADMIN,
        ])->assertSessionHas('error');

        // Sans propriétaire, plus personne ne peut gérer les comptes.
        $this->assertTrue($owner->fresh()->isOwner());
    }

    public function test_the_last_owner_cannot_be_deleted(): void
    {
        $owner = $this->user(User::ROLE_OWNER);
        $other = $this->user(User::ROLE_ADMIN);

        $this->actingAs($other);
        // L'admin n'a pas le droit d'y accéder, on passe donc par la propriétaire.
        $this->actingAs($owner)->delete(route('admin.users.destroy', $owner))->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $owner->id]);
    }

    public function test_an_owner_can_be_demoted_when_another_one_remains(): void
    {
        $first = $this->user(User::ROLE_OWNER);
        $second = $this->user(User::ROLE_OWNER);

        $this->actingAs($first)->put(route('admin.users.update', $second), [
            'name' => $second->name, 'email' => $second->email, 'role' => User::ROLE_ADMIN,
        ])->assertSessionHasNoErrors();

        $this->assertSame(User::ROLE_ADMIN, $second->fresh()->role);
    }

    // ── Double authentification ─────────────────────────────────

    /**
     * Vecteurs de test officiels de la RFC 6238 (SHA-1), pour le secret
     * « 12345678901234567890 » encodé en base32. Ils vérifient l'implémentation
     * contre la norme plutôt que contre elle-même : sans cela, un bug de calcul
     * passerait inaperçu jusqu'à ce qu'une application refuse tous les codes.
     */
    public function test_the_totp_algorithm_matches_the_standard(): void
    {
        $totp = new TotpService;
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

        // Chaque compteur vaut floor(temps / 30).
        $this->assertSame('287082', $totp->codeAt($secret, intdiv(59, 30)));
        $this->assertSame('081804', $totp->codeAt($secret, intdiv(1111111109, 30)));
        $this->assertSame('050471', $totp->codeAt($secret, intdiv(1111111111, 30)));
        $this->assertSame('005924', $totp->codeAt($secret, intdiv(1234567890, 30)));
        $this->assertSame('279037', $totp->codeAt($secret, intdiv(2000000000, 30)));
    }

    public function test_a_code_is_accepted_and_a_wrong_one_refused(): void
    {
        $totp = new TotpService;
        $secret = $totp->generateSecret();

        $this->assertTrue($totp->verify($secret, $totp->codeAt($secret)));
        $this->assertFalse($totp->verify($secret, '000000'));
        $this->assertFalse($totp->verify($secret, 'abc'));
    }

    public function test_two_factor_is_only_stored_after_a_valid_code(): void
    {
        $totp = new TotpService;
        $owner = $this->user(User::ROLE_OWNER);
        $secret = $totp->generateSecret();

        // Code faux : rien n'est enregistré, sinon une erreur de scan enfermerait dehors.
        $this->actingAs($owner)->withSession(['two_factor.pending_secret' => $secret])
            ->post(route('admin.two-factor.confirm'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertFalse($owner->fresh()->hasTwoFactorEnabled());

        $this->actingAs($owner)->withSession(['two_factor.pending_secret' => $secret])
            ->post(route('admin.two-factor.confirm'), ['code' => $totp->codeAt($secret)])
            ->assertSessionHasNoErrors();

        $this->assertTrue($owner->fresh()->hasTwoFactorEnabled());
    }

    public function test_an_enabled_account_is_challenged_before_the_back_office(): void
    {
        $totp = new TotpService;
        $owner = $this->user(User::ROLE_OWNER);
        $secret = $totp->generateSecret();
        $owner->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => ['AAAA-BBBB'],
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->actingAs($owner)->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.two-factor.challenge'));

        $this->actingAs($owner)->post(route('admin.two-factor.challenge.verify'), [
            'code' => $totp->codeAt($secret),
        ])->assertRedirect(route('admin.dashboard'));

        $this->actingAs($owner)->withSession(['two_factor.passed' => true])
            ->get(route('admin.dashboard'))->assertOk();
    }

    public function test_a_recovery_code_works_once(): void
    {
        $owner = $this->user(User::ROLE_OWNER);
        $owner->forceFill([
            'two_factor_secret' => (new TotpService)->generateSecret(),
            'two_factor_recovery_codes' => ['AAAA-BBBB', 'CCCC-DDDD'],
            'two_factor_confirmed_at' => now(),
        ])->save();

        // Sans code de secours, un téléphone perdu enfermerait définitivement dehors.
        $this->actingAs($owner)->post(route('admin.two-factor.challenge.verify'), ['code' => 'aaaa-bbbb'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertSame(['CCCC-DDDD'], array_values((array) $owner->fresh()->two_factor_recovery_codes));

        // Le même code ne doit plus fonctionner.
        $this->actingAs($owner)->post(route('admin.two-factor.challenge.verify'), ['code' => 'AAAA-BBBB'])
            ->assertSessionHasErrors('code');
    }

    public function test_an_account_without_two_factor_is_not_challenged(): void
    {
        $this->actingAs($this->user(User::ROLE_OWNER))->get(route('admin.dashboard'))->assertOk();
    }

    public function test_disabling_two_factor_requires_the_password(): void
    {
        $owner = $this->user(User::ROLE_OWNER);
        $owner->forceFill([
            'password' => Hash::make('bon-mot-de-passe'),
            'two_factor_secret' => (new TotpService)->generateSecret(),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $session = ['two_factor.passed' => true];

        // Une session laissée ouverte ne doit pas suffire à retirer le second facteur.
        $this->actingAs($owner)->withSession($session)
            ->delete(route('admin.two-factor.destroy'), ['password' => 'mauvais'])
            ->assertSessionHasErrors('password');

        $this->assertTrue($owner->fresh()->hasTwoFactorEnabled());

        $this->actingAs($owner)->withSession($session)
            ->delete(route('admin.two-factor.destroy'), ['password' => 'bon-mot-de-passe'])
            ->assertSessionHasNoErrors();

        $this->assertFalse($owner->fresh()->hasTwoFactorEnabled());
    }

    public function test_the_secret_is_encrypted_at_rest(): void
    {
        $owner = $this->user(User::ROLE_OWNER);
        $secret = (new TotpService)->generateSecret();
        $owner->forceFill(['two_factor_secret' => $secret, 'two_factor_confirmed_at' => now()])->save();

        $raw = \DB::table('users')->where('id', $owner->id)->value('two_factor_secret');

        // Un accès en lecture à la base ne doit pas suffire à générer les codes.
        $this->assertNotSame($secret, $raw);
        $this->assertSame($secret, $owner->fresh()->two_factor_secret);
    }
}
