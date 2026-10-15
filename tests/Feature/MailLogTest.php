<?php

namespace Tests\Feature;

use App\Listeners\RecordMailActivity;
use App\Mail\QrCodeMail;
use App\Models\MailLog;
use App\Models\User;
use App\Support\MailSamples;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Journal des envois.
 *
 * Rien ne disait si les emails partaient : les envois sont enveloppés dans
 * des try/catch qui se contentent de journaliser, et personne ne lit
 * storage/logs sur un hébergement mutualisé. Une carte d'anniversaire qui
 * cesse de partir passait inaperçue jusqu'à la plainte d'une membre.
 *
 * Ces tests utilisent le transport « array » de l'environnement de test, et
 * non Mail::fake() : une fausse façade court-circuite les événements du
 * framework, donc précisément ce qu'on vérifie ici.
 */
class MailLogTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'role' => User::ROLE_OWNER]);
    }

    // ── La trace ────────────────────────────────────────────────

    public function test_an_email_leaves_a_trace(): void
    {
        Mail::to('awa@example.com')->sendNow(MailSamples::make('qr-code'));

        $log = MailLog::firstOrFail();
        $this->assertSame(QrCodeMail::class, $log->mailable);
        $this->assertSame('awa@example.com', $log->recipient);
        $this->assertSame('sent', $log->status);
        $this->assertNotNull($log->sent_at);
        $this->assertNotEmpty($log->subject);
    }

    public function test_the_body_is_never_stored(): void
    {
        Mail::to('awa@example.com')->sendNow(MailSamples::make('birthday'));

        // Les corps contiennent des liens signés valant accès, et pèseraient
        // lourd pour ce qu'ils apprennent.
        $this->assertArrayNotHasKey('body', MailLog::firstOrFail()->getAttributes());
    }

    public function test_several_recipients_are_counted(): void
    {
        Mail::to(['a@example.com', 'b@example.com'])->sendNow(MailSamples::make('contact'));

        $log = MailLog::firstOrFail();
        $this->assertSame(2, $log->recipients_count);
        $this->assertSame('a@example.com', $log->recipient);
    }

    public function test_every_mailable_of_the_app_is_traced(): void
    {
        foreach (array_keys(MailSamples::catalog()) as $key) {
            Mail::to('awa@example.com')->sendNow(MailSamples::make($key));
        }

        // Le journal est branché sur les événements du framework : aucun type
        // d'email ne peut lui échapper, pas même ceux écrits demain.
        $this->assertSame(count(MailSamples::catalog()), MailLog::count());
        $this->assertSame(0, MailLog::where('status', '!=', 'sent')->count());
    }

    // ── Distinguer les tests des vrais envois ───────────────────

    public function test_bench_sends_are_flagged_as_tests(): void
    {
        $this->actingAs($this->admin())->post(route('admin.emails.send'), [
            'email' => 'awa@example.com',
            'keys' => ['qr-code'],
        ]);

        // Sinon un test d'administration ferait croire que le système tourne.
        $this->assertTrue(MailLog::firstOrFail()->is_test);
        $this->assertSame(0, MailLog::real()->count());
    }

    public function test_a_normal_send_is_not_flagged(): void
    {
        Mail::to('awa@example.com')->sendNow(MailSamples::make('qr-code'));

        $this->assertFalse(MailLog::firstOrFail()->is_test);
        $this->assertSame(1, MailLog::real()->count());
    }

    // ── Les envois qui n'aboutissent pas ────────────────────────

    public function test_an_unconfirmed_send_becomes_a_failure(): void
    {
        $log = MailLog::create([
            'mailable' => QrCodeMail::class, 'subject' => 'Test',
            'recipient' => 'awa@example.com', 'status' => 'sending',
        ]);
        $log->forceFill(['created_at' => now()->subMinutes(MailLog::STALE_AFTER_MINUTES + 1)])->save();

        $this->assertTrue($log->fresh()->isStale());

        $this->artisan('mail-logs:prune')->assertSuccessful();

        $log->refresh();
        $this->assertSame('failed', $log->status);
        $this->assertNotEmpty($log->error);
    }

    public function test_a_recent_send_is_left_alone(): void
    {
        $log = MailLog::create([
            'mailable' => QrCodeMail::class, 'subject' => 'Test',
            'recipient' => 'awa@example.com', 'status' => 'sending',
        ]);

        $this->artisan('mail-logs:prune');

        // Un envoi parti il y a dix secondes peut encore être confirmé.
        $this->assertSame('sending', $log->fresh()->status);
    }

    public function test_old_traces_are_forgotten(): void
    {
        $old = MailLog::create(['mailable' => QrCodeMail::class, 'status' => 'sent', 'recipient' => 'a@example.com']);
        $old->forceFill(['created_at' => now()->subDays(100)])->save();
        MailLog::create(['mailable' => QrCodeMail::class, 'status' => 'sent', 'recipient' => 'b@example.com']);

        $this->artisan('mail-logs:prune');

        // Une table qui grossit sans fin coûte plus cher qu'elle n'apprend.
        $this->assertSame(1, MailLog::count());
    }

    public function test_the_pruning_is_scheduled(): void
    {
        $events = collect(app(Schedule::class)->events())
            ->filter(fn ($e) => str_contains($e->command ?? '', 'mail-logs:prune'));

        $this->assertCount(1, $events);
    }

    // ── Le tableau de bord ──────────────────────────────────────

    public function test_the_dashboard_shows_each_type_even_when_silent(): void
    {
        $html = $this->actingAs($this->admin())->get(route('admin.emails.logs'))
            ->assertOk()->getContent();

        // C'est l'absence qu'on cherche : un type jamais parti doit figurer
        // dans le tableau, avec un tiret.
        $this->assertStringContainsString(e('Carte d\'anniversaire'), $html);
        $this->assertStringContainsString('Jamais', $html);
    }

    public function test_the_dashboard_can_filter_on_failures(): void
    {
        MailLog::create(['mailable' => QrCodeMail::class, 'status' => 'sent', 'recipient' => 'ok@example.com']);
        MailLog::create(['mailable' => QrCodeMail::class, 'status' => 'failed', 'recipient' => 'ko@example.com', 'error' => 'Connexion refusée']);

        $html = $this->actingAs($this->admin())
            ->get(route('admin.emails.logs', ['statut' => 'failed']))
            ->assertOk()->getContent();

        $this->assertStringContainsString('ko@example.com', $html);
        $this->assertStringNotContainsString('ok@example.com', $html);
    }

    public function test_tests_are_hidden_unless_asked_for(): void
    {
        MailLog::create(['mailable' => QrCodeMail::class, 'status' => 'sent', 'recipient' => 'essai@example.com', 'is_test' => true]);

        $this->assertStringNotContainsString('essai@example.com',
            $this->actingAs($this->admin())->get(route('admin.emails.logs'))->getContent());

        $this->assertStringContainsString('essai@example.com',
            $this->actingAs($this->admin())->get(route('admin.emails.logs', ['tests' => 1]))->getContent());
    }

    public function test_the_journal_is_closed_to_non_admins(): void
    {
        $this->get(route('admin.emails.logs'))->assertRedirect();
    }

    // ── Le journal ne doit jamais bloquer un envoi ──────────────

    public function test_a_broken_journal_does_not_stop_the_email(): void
    {
        // Table absente : écrire la trace lève une exception. L'email doit
        // partir quand même — mieux vaut une trace manquante qu'une inscrite
        // sans son QR.
        Schema::drop('mail_logs');

        Mail::to('awa@example.com')->sendNow(MailSamples::make('qr-code'));

        $this->assertTrue(true, 'L\'envoi a abouti malgré le journal indisponible.');
    }

    // ── Les cas qui font perdre une trace ───────────────────────

    public function test_a_real_smtp_failure_still_leaves_a_trace(): void
    {
        // Hôte joignable, port fermé : l'envoi lève pour de bon.
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => '127.0.0.1',
            'mail.mailers.smtp.port' => 2,
            'mail.mailers.smtp.timeout' => 1,
        ]);

        try {
            Mail::to('awa@example.com')->sendNow(MailSamples::make('qr-code'));
        } catch (\Throwable) {
            // L'exception remonte à l'appelant, c'est voulu.
        }

        // La trace existe même si l'envoi a échoué : sans elle, un SMTP cassé
        // ne laisserait rien derrière lui.
        $log = MailLog::firstOrFail();
        $this->assertSame('sending', $log->status);
        $this->assertSame('awa@example.com', $log->recipient);

        $log->forceFill(['created_at' => now()->subMinutes(MailLog::STALE_AFTER_MINUTES + 1)])->save();
        $this->artisan('mail-logs:prune');

        $this->assertSame('failed', $log->fresh()->status);
    }

    public function test_an_overlong_subject_does_not_lose_the_trace(): void
    {
        // En MySQL strict, un sujet dépassant la colonne fait échouer
        // l'insertion : la trace se perdrait en silence, soit l'exact
        // contraire de ce que ce journal doit faire.
        Mail::raw('x', fn ($m) => $m->to('awa@example.com')->subject(str_repeat('Sujet interminable ', 40)));

        $log = MailLog::firstOrFail();
        $this->assertLessThanOrEqual(300, mb_strlen($log->subject));
    }

    public function test_a_blind_copy_only_send_is_still_attributed(): void
    {
        Mail::send([], [], fn ($m) => $m->bcc('cachee@example.com')->subject('Copie cachée')->html('x'));

        $this->assertSame('cachee@example.com', MailLog::firstOrFail()->recipient);
    }

    public function test_a_raw_email_without_mailable_is_traced(): void
    {
        // fsl:diagnose-emails passe par Mail::raw : pas de classe de mailable.
        Mail::raw('Test', fn ($m) => $m->to('awa@example.com')->subject('Brut'));

        $log = MailLog::firstOrFail();
        $this->assertNull($log->mailable);
        $this->assertSame('Inconnu', $log->typeLabel());
        $this->assertSame('sent', $log->status);
    }

    public function test_the_diagnostic_test_email_is_flagged(): void
    {
        $this->artisan('fsl:diagnose-emails', ['--test' => 'awa@example.com']);

        // Sinon un diagnostic ferait croire que le système tourne.
        $this->assertTrue(MailLog::firstOrFail()->is_test);
    }

    public function test_the_listener_exposes_its_test_header(): void
    {
        $this->assertSame('X-FSL-Test', RecordMailActivity::TEST_HEADER);
    }
}
