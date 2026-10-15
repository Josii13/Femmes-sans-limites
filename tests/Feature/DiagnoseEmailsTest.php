<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Le diagnostic d'envoi des emails.
 *
 * Il est lancé précisément quand quelque chose ne va pas, souvent depuis un
 * terminal cPanel : s'il s'effondre lui-même, il ne reste plus rien pour
 * comprendre. Ces tests vérifient d'abord qu'il tient debout.
 */
class DiagnoseEmailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_diagnosis_runs(): void
    {
        $this->artisan('fsl:diagnose-emails')->assertSuccessful();
    }

    public function test_the_send_test_actually_reaches_the_mailer(): void
    {
        Mail::fake();

        // Un import manquant a déjà fait répondre « Class Mail not found » à
        // l'endroit même où l'on attendait l'erreur SMTP.
        $this->artisan('fsl:diagnose-emails', ['--test' => 'awa@example.com'])
            ->expectsOutputToContain('Email accepté par le serveur SMTP')
            ->assertSuccessful();
    }

    public function test_a_refused_send_reports_the_transport_message(): void
    {
        // « Authentification refusée » et « port bloqué » appellent deux
        // corrections différentes : le message brut doit remonter.
        Mail::shouldReceive('raw')->andThrow(new \RuntimeException('Connection could not be established'));

        $this->artisan('fsl:diagnose-emails', ['--test' => 'awa@example.com'])
            ->expectsOutputToContain('Connection could not be established')
            ->assertSuccessful();
    }
}
