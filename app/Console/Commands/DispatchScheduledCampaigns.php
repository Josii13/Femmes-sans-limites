<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\CommunicationController;
use App\Models\Campaign;
use Illuminate\Console\Command;

class DispatchScheduledCampaigns extends Command
{
    protected $signature   = 'campaigns:dispatch';
    protected $description = 'Envoie les campagnes programmées dont l\'heure est arrivée';

    public function handle(): void
    {
        $campaigns = Campaign::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($campaigns->isEmpty()) {
            $this->info('Aucune campagne à envoyer.');
            return;
        }

        $controller = new CommunicationController();

        foreach ($campaigns as $campaign) {
            $this->info("Envoi : {$campaign->title}...");
            $controller->dispatch($campaign);
            $this->info("  → {$campaign->sent_count} email(s) envoyé(s).");
        }
    }
}
