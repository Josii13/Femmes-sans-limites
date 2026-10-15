<?php

namespace App\Console\Commands;

use App\Models\Registration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Diagnostic en LECTURE SEULE de la chaîne d'envoi des emails.
 *
 * Écrit après le cas suivant : des inscriptions apparaissaient « Confirmée »
 * dans le back-office alors que personne ne recevait son QR. La file d'attente
 * était en base, sans worker pour la vider — les emails dormaient dans la table
 * `jobs`. Rien dans l'interface ne permettait de s'en apercevoir.
 *
 * Ne crée, ne modifie et ne supprime rien : conçu pour tourner en production.
 */
class DiagnoseEmails extends Command
{
    protected $signature = 'fsl:diagnose-emails';

    protected $description = 'Vérifie que les emails (QR, ebooks, cartes) partent réellement';

    private int $problems = 0;

    public function handle(): int
    {
        $this->newLine();
        $this->line('<options=bold>═══ Diagnostic de l\'envoi des emails ═══</>');

        $this->checkMailer();
        $this->checkQueue();
        $this->checkRegistrations();

        $this->newLine();
        if ($this->problems === 0) {
            $this->info('  Aucun problème détecté.');
        } else {
            $this->warn("  {$this->problems} point(s) à corriger ci-dessus.");
        }
        $this->newLine();

        return self::SUCCESS;
    }

    private function checkMailer(): void
    {
        $this->section('Transport');

        $mailer = config('mail.default');
        $this->line("  Transport : <options=bold>{$mailer}</>");

        if ($mailer === 'log') {
            $this->fail_('Les emails sont écrits dans les logs, pas envoyés (MAIL_MAILER=log).');

            return;
        }

        if ($mailer === 'smtp' && ! config('mail.mailers.smtp.host')) {
            $this->fail_('MAIL_HOST est vide.');

            return;
        }

        $from = config('mail.from.address');
        if (! $from) {
            $this->fail_('MAIL_FROM_ADDRESS est vide : la plupart des serveurs rejetteront l\'envoi.');

            return;
        }

        $this->ok("Expéditeur : {$from}");
    }

    private function checkQueue(): void
    {
        $this->section('File d\'attente');

        $connection = config('queue.default');
        $this->line("  QUEUE_CONNECTION : <options=bold>{$connection}</>");

        if ($connection === 'sync') {
            $this->ok('Envoi immédiat pendant la requête : aucun worker nécessaire.');

            return;
        }

        if ($connection !== 'database') {
            $this->ok('File externe : vérifiez que le worker correspondant tourne.');

            return;
        }

        if (! Schema::hasTable('jobs')) {
            $this->fail_('La table `jobs` est absente : lancez `php artisan migrate --force`.');

            return;
        }

        $pending = DB::table('jobs')->count();
        $failed = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;

        if ($pending > 0) {
            $oldest = DB::table('jobs')->min('created_at');
            $age = $oldest ? now()->diffInMinutes(now()->setTimestamp((int) $oldest)) : 0;

            // Au-delà de quelques minutes, ce n'est plus un délai : c'est un blocage.
            if ($age > 5) {
                $this->fail_("{$pending} email(s) en attente depuis {$age} min — la file n'est pas vidée.");
                $this->line('    Vérifiez que le cron `php artisan schedule:run` tourne chaque minute,');
                $this->line('    ou passez QUEUE_CONNECTION=sync pour un envoi immédiat.');
            } else {
                $this->ok("{$pending} email(s) en attente, file active.");
            }
        } else {
            $this->ok('File vide.');
        }

        if ($failed > 0) {
            $this->fail_("{$failed} email(s) en échec — `php artisan queue:failed` pour le détail.");
        }
    }

    private function checkRegistrations(): void
    {
        $this->section('Inscriptions confirmées');

        $confirmed = Registration::whereIn('status', ['paid', 'attended'])->count();
        $withoutToken = Registration::whereIn('status', ['paid', 'attended'])
            ->whereNull('qr_token')->count();

        $this->line("  Confirmées : <options=bold>{$confirmed}</>");

        if ($withoutToken > 0) {
            // Sans jeton, ni le QR ni la page de billet n'existent pour elles.
            $this->fail_("{$withoutToken} inscription(s) confirmée(s) sans QR : accès impossible le jour J.");
        } else {
            $this->ok('Toutes disposent d\'un billet consultable en ligne.');
        }
    }

    private function section(string $title): void
    {
        $this->newLine();
        $this->line("<options=bold>── {$title}</>");
    }

    private function ok(string $message): void
    {
        $this->line("  <fg=green>✓</> {$message}");
    }

    /** `fail` est déjà pris par Command : d'où le tiret bas. */
    private function fail_(string $message): void
    {
        $this->problems++;
        $this->line("  <fg=red>✗</> {$message}");
    }
}
