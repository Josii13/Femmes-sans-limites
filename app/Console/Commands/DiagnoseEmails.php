<?php

namespace App\Console\Commands;

use App\Models\Registration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
    protected $signature = 'fsl:diagnose-emails
        {--test= : Envoie réellement un email à cette adresse et affiche l\'erreur SMTP exacte}';

    protected $description = 'Vérifie que les emails (QR, ebooks, cartes) partent réellement';

    private int $problems = 0;

    public function handle(): int
    {
        $this->newLine();
        $this->line('<options=bold>═══ Diagnostic de l\'envoi des emails ═══</>');

        $this->checkMailer();
        $this->checkQueue();
        $this->checkRegistrations();
        $this->checkRecentFailures();

        if ($this->option('test')) {
            $this->sendTestMail((string) $this->option('test'));
        } else {
            $this->newLine();
            $this->line('  Ajoutez <options=bold>--test=vous@exemple.com</> pour tenter un envoi réel.');
        }

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

        // Un outil de diagnostic qui s'effondre sur une base injoignable ne
        // diagnostique rien : on rapporte la panne au lieu de la propager.
        try {
            if (! Schema::hasTable('jobs')) {
                $this->fail_('La table `jobs` est absente : lancez `php artisan migrate --force`.');

                return;
            }

            $pending = DB::table('jobs')->count();
            $failed = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;
        } catch (\Throwable $e) {
            $this->fail_('Base de données injoignable : '.$e->getMessage());

            return;
        }

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

        try {
            $confirmed = Registration::whereIn('status', ['paid', 'attended'])->count();
            $withoutToken = Registration::whereIn('status', ['paid', 'attended'])
                ->whereNull('qr_token')->count();
        } catch (\Throwable $e) {
            $this->fail_('Base de données injoignable : '.$e->getMessage());

            return;
        }

        $this->line("  Confirmées : <options=bold>{$confirmed}</>");

        if ($withoutToken > 0) {
            // Sans jeton, ni le QR ni la page de billet n'existent pour elles.
            $this->fail_("{$withoutToken} inscription(s) confirmée(s) sans QR : accès impossible le jour J.");
        } else {
            $this->ok('Toutes disposent d\'un billet consultable en ligne.');
        }
    }

    /**
     * Les envois sont enveloppés dans un try/catch qui se contente de journaliser :
     * l'inscrite voit « Inscription confirmée » et ne reçoit rien. Sur mutualisé,
     * personne ne va lire storage/logs — alors la commande va le lire pour vous.
     */
    private function checkRecentFailures(): void
    {
        $this->section('Échecs récents');

        $log = storage_path('logs/laravel.log');
        if (! is_file($log)) {
            $this->ok('Aucun fichier de log.');

            return;
        }

        // On ne lit que la fin : un log de production pèse vite plusieurs Mo.
        $handle = fopen($log, 'r');
        $size = filesize($log);
        fseek($handle, max(0, $size - 400_000));
        $tail = (string) fread($handle, 400_000);
        fclose($handle);

        $needles = ['échec envoi', 'échec régénération', 'Failed to authenticate',
            'Connection could not be established', 'TransportException'];

        $hits = array_values(array_filter(
            explode("\n", $tail),
            fn ($line) => array_filter($needles, fn ($n) => str_contains($line, $n))
        ));

        if ($hits === []) {
            $this->ok('Aucun échec d\'envoi journalisé.');

            return;
        }

        $this->fail_(count($hits).' échec(s) d\'envoi journalisé(s). Les 3 derniers :');
        foreach (array_slice($hits, -3) as $line) {
            $this->line('    '.mb_substr(trim($line), 0, 180));
        }
    }

    private function sendTestMail(string $address): void
    {
        $this->section('Envoi de test');

        try {
            Mail::raw(
                "Test d'envoi depuis Femme Sans Limites.\nSi vous lisez ceci, le SMTP fonctionne.",
                fn ($message) => $message->to($address)->subject('Test d\'envoi — FSL')
            );
            $this->ok("Email accepté par le serveur SMTP pour {$address}.");
            $this->line('    S\'il n\'arrive pas, le problème est la délivrabilité (SPF/DKIM, spam),');
            $this->line('    pas la configuration : vérifiez le dossier indésirables.');
        } catch (\Throwable $e) {
            // Le message brut du transport est ce qui manquait : « authentification
            // refusée » et « port bloqué » appellent deux corrections différentes.
            $this->fail_('Envoi refusé : '.$e->getMessage());
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
