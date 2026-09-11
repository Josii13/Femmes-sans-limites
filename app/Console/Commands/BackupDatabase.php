<?php

namespace App\Console\Commands;

use App\Mail\BackupFailedMail;
use App\Models\User;
use App\Services\DatabaseBackup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use ZipArchive;

/**
 * Sauvegarde quotidienne de la base et des fichiers téléversés.
 *
 * Les archives vivent sur le disque privé (`storage/app/backups`), jamais sous
 * `public/` : un dump contient les adresses, téléphones et références de paiement
 * des membres.
 */
class BackupDatabase extends Command
{
    protected $signature = 'fsl:backup
        {--keep=14 : Nombre de sauvegardes à conserver}
        {--no-files : Ne sauvegarder que la base, sans les fichiers téléversés}';

    protected $description = 'Sauvegarde la base de données et les fichiers téléversés dans storage/app/backups';

    /** Tables dont le contenu ne mérite pas d'être conservé : régénérable ou éphémère. */
    private const SKIP_CONTENT = ['sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'];

    public function handle(DatabaseBackup $backup): int
    {
        $startedAt = microtime(true);
        $stamp = now()->format('Y-m-d_His');
        $directory = storage_path('app/backups');

        $sqlPath = $directory.'/fsl-'.$stamp.'.sql';

        try {
            // Création du dossier comprise : un disque plein ou un droit manquant
            // doit alerter comme n'importe quel autre échec, pas remonter en erreur brute.
            File::ensureDirectoryExists($directory);
            $rows = $backup->dumpTo($sqlPath, self::SKIP_CONTENT);
        } catch (\Throwable $e) {
            return $this->reportFailure('Sauvegarde de la base', $e);
        }

        $this->info('Base sauvegardée : '.number_format($rows, 0, ',', ' ').' lignes.');

        $archive = $directory.'/fsl-'.$stamp.'.zip';

        try {
            $this->compress($archive, $sqlPath, ! $this->option('no-files'));
        } catch (\Throwable $e) {
            // Le dump SQL seul reste exploitable : on ne le supprime pas.
            return $this->reportFailure('Compression de la sauvegarde', $e);
        }

        // Le SQL brut n'a plus lieu d'être une fois dans l'archive.
        File::delete($sqlPath);

        $size = File::size($archive);
        $this->info('Archive : '.basename($archive).' ('.$this->humanSize($size).')');

        $deleted = $this->rotate($directory, max(1, (int) $this->option('keep')));
        if ($deleted > 0) {
            $this->line($deleted.' ancienne(s) sauvegarde(s) supprimée(s).');
        }

        Log::info('backup.completed', [
            'archive' => basename($archive),
            'rows' => $rows,
            'bytes' => $size,
            'seconds' => round(microtime(true) - $startedAt, 1),
        ]);

        return self::SUCCESS;
    }

    /** Regroupe le dump et, si demandé, les fichiers téléversés dans une archive. */
    private function compress(string $archive, string $sqlPath, bool $withFiles): void
    {
        $zip = new ZipArchive;

        if ($zip->open($archive, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("Impossible de créer l'archive {$archive}");
        }

        $zip->addFile($sqlPath, 'base.sql');

        if ($withFiles) {
            // Photos de membres, couvertures d'ebooks, images du site : irremplaçables.
            $this->addDirectory($zip, storage_path('app/public'), 'fichiers-publics');
            // Disque privé : PDF vendus, dont la perte casserait la livraison des
            // achats déjà réglés, et QR codes des inscriptions aux événements.
            $this->addDirectory($zip, storage_path('app/private'), 'fichiers-prives');
        }

        $zip->close();
    }

    private function addDirectory(ZipArchive $zip, string $source, string $prefix): void
    {
        if (! File::isDirectory($source)) {
            return;
        }

        foreach (File::allFiles($source) as $file) {
            // .gitignore et consorts n'ont aucun intérêt dans une sauvegarde.
            if (str_starts_with($file->getFilename(), '.')) {
                continue;
            }

            $zip->addFile($file->getPathname(), $prefix.'/'.$file->getRelativePathname());
        }
    }

    /** Ne conserve que les N archives les plus récentes. */
    private function rotate(string $directory, int $keep): int
    {
        $archives = collect(File::files($directory))
            ->filter(fn ($file) => str_ends_with($file->getFilename(), '.zip'))
            ->sortByDesc(fn ($file) => $file->getFilename())
            ->values();

        $deleted = 0;
        foreach ($archives->slice($keep) as $old) {
            File::delete($old->getPathname());
            $deleted++;
        }

        return $deleted;
    }

    /**
     * Un échec de sauvegarde doit être visible : il est journalisé et signalé par
     * email aux administrateurs, sinon personne ne le découvre avant l'incident.
     */
    private function reportFailure(string $step, \Throwable $e): int
    {
        $message = $step.' : '.$e->getMessage();

        $this->error($message);
        Log::error('backup.failed', ['step' => $step, 'error' => $e->getMessage()]);

        foreach (User::where('is_admin', true)->pluck('email') as $email) {
            try {
                Mail::to($email)->send(new BackupFailedMail($step, $e->getMessage()));
            } catch (\Throwable) {
                // L'échec d'alerte ne doit pas masquer l'échec initial, déjà journalisé.
            }
        }

        return self::FAILURE;
    }

    private function humanSize(int $bytes): string
    {
        foreach (['o', 'Ko', 'Mo', 'Go'] as $unit) {
            if ($bytes < 1024) {
                return round($bytes, 1).' '.$unit;
            }
            $bytes /= 1024;
        }

        return round($bytes, 1).' To';
    }
}
