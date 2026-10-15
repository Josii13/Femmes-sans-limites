<?php

namespace Tests\Feature;

use App\Mail\BirthdayMail;
use App\Mail\MemberCardMail;
use App\Mail\QrCodeMail;
use App\Models\Member;
use App\Models\User;
use App\Support\MailSamples;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Banc d'essai des emails.
 *
 * Le test central est le plus bête : rendre chacun des emails du catalogue.
 * C'est lui qui attrape une variable oubliée dans une vue, une relation non
 * chargée ou une pièce jointe introuvable — exactement ce qui, en production,
 * fait échouer un envoi en silence.
 */
class MailTestBenchTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'role' => User::ROLE_OWNER]);
    }

    /** @return array<int, array<int, string>> */
    public static function emailKeys(): array
    {
        return array_map(fn ($k) => [$k], array_keys(MailSamples::catalog()));
    }

    #[DataProvider('emailKeys')]
    public function test_every_email_renders(string $key): void
    {
        $html = MailSamples::make($key)->render();

        $this->assertNotEmpty($html, "L'email « {$key} » rend un corps vide.");
        // Une variable Blade absente laisse une trace visible plutôt qu'une erreur.
        $this->assertStringNotContainsString('Undefined', $html);
    }

    public function test_no_sample_touches_the_database(): void
    {
        foreach (array_keys(MailSamples::catalog()) as $key) {
            MailSamples::make($key)->render();
        }

        // Tester ses emails ne doit pas semer de fausses inscrites dans la
        // base de production, où ces tests ont le plus de chances d'être lancés.
        $this->assertDatabaseCount('members', 0);
        $this->assertDatabaseCount('registrations', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    // ── L'écran d'administration ────────────────────────────────

    public function test_the_page_lists_every_email(): void
    {
        $html = $this->actingAs($this->admin())->get(route('admin.emails.index'))
            ->assertOk()->getContent();

        foreach (MailSamples::catalog() as $item) {
            // Blade échappe les apostrophes : « d'ebook » devient « d&#039;ebook ».
            $this->assertStringContainsString(e($item['label']), $html);
        }
    }

    public function test_the_page_links_to_each_preview_by_its_key(): void
    {
        $html = $this->actingAs($this->admin())->get(route('admin.emails.index'))
            ->assertOk()->getContent();

        // groupBy() ré-indexe numériquement sans preserveKeys : les liens
        // pointaient vers /emails/1/apercu, et chacun répondait 404.
        foreach (array_keys(MailSamples::catalog()) as $key) {
            $this->assertStringContainsString(
                route('admin.emails.preview', $key), $html,
                "Aperçu manquant ou mal construit pour « {$key} »."
            );
        }
    }

    public function test_an_email_can_be_previewed_without_sending(): void
    {
        Mail::fake();

        $this->actingAs($this->admin())
            ->get(route('admin.emails.preview', 'qr-code'))
            ->assertOk();

        Mail::assertNothingSent();
    }

    public function test_an_unknown_email_is_not_found(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.emails.preview', 'inconnu'))
            ->assertNotFound();
    }

    public function test_selected_emails_are_sent_to_the_given_address(): void
    {
        Mail::fake();

        $this->actingAs($this->admin())->post(route('admin.emails.send'), [
            'email' => 'test@example.com',
            'keys' => ['qr-code', 'member-card'],
        ])->assertRedirect()->assertSessionHas('sent');

        Mail::assertSent(QrCodeMail::class,
            fn ($mail) => $mail->hasTo('test@example.com'));
        Mail::assertSent(MemberCardMail::class);
    }

    public function test_the_emails_are_sent_immediately_never_queued(): void
    {
        Mail::fake();

        $this->actingAs($this->admin())->post(route('admin.emails.send'), [
            'email' => 'test@example.com',
            'keys' => ['birthday'],
        ]);

        // Un test dont le résultat dépend d'un worker ne teste pas grand-chose.
        Mail::assertNothingQueued();
        Mail::assertSent(BirthdayMail::class);
    }

    public function test_an_address_is_required(): void
    {
        Mail::fake();

        $this->actingAs($this->admin())->post(route('admin.emails.send'), [
            'keys' => ['qr-code'],
        ])->assertSessionHasErrors('email');

        Mail::assertNothingSent();
    }

    public function test_an_unknown_key_is_ignored_rather_than_fatal(): void
    {
        Mail::fake();

        $this->actingAs($this->admin())->post(route('admin.emails.send'), [
            'email' => 'test@example.com',
            'keys' => ['qr-code', 'cle-bidon'],
        ])->assertRedirect();

        Mail::assertSentCount(1);
    }

    public function test_the_bench_is_closed_to_non_admins(): void
    {
        $this->get(route('admin.emails.index'))->assertRedirect();

        $member = Member::factory()->create(['status' => 'active']);
        // Une membre connectée n'est pas une administratrice : refus franc.
        $this->actingAs($member, 'member')->get(route('admin.emails.index'))->assertForbidden();
    }
}
