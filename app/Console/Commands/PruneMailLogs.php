<?php

namespace App\Console\Commands;

use App\Models\MailLog;
use Illuminate\Console\Command;

/**
 * Entretien du journal des envois.
 *
 * Deux gestes. D'abord conclure : un envoi resté « en cours » depuis dix
 * minutes n'aboutira plus, le transport a levé une exception quelque part et
 * personne n'a confirmé. Le laisser en suspens rendrait le tableau de bord
 * illisible — on ne saurait jamais distinguer un envoi en vol d'un envoi
 * perdu.
 *
 * Ensuite oublier : sur un hébergement mutualisé, une table qui grossit sans
 * fin finit par coûter plus cher que ce qu'elle apprend.
 */
class PruneMailLogs extends Command
{
    protected $signature = 'mail-logs:prune {--days=90 : Durée de conservation}';

    protected $description = 'Clôt les envois sans confirmation et purge les traces anciennes';

    public function handle(): int
    {
        $closed = MailLog::stale()->update([
            'status' => 'failed',
            'error' => 'Aucune confirmation d\'envoi reçue du serveur SMTP.',
            'updated_at' => now(),
        ]);

        $days = max(7, (int) $this->option('days'));
        $pruned = MailLog::where('created_at', '<', now()->subDays($days))->delete();

        $this->info("Envois clôturés en échec : {$closed}. Traces purgées : {$pruned}.");

        return self::SUCCESS;
    }
}
