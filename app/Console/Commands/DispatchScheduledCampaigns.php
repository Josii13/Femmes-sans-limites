<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Services\CampaignDispatcher;
use Illuminate\Console\Command;

class DispatchScheduledCampaigns extends Command
{
    protected $signature = 'campaigns:dispatch';

    protected $description = 'Met en file les campagnes programmées dont l\'heure est arrivée';

    public function handle(CampaignDispatcher $dispatcher): void
    {
        $campaigns = Campaign::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($campaigns->isEmpty()) {
            $this->info('Aucune campagne à envoyer.');

            return;
        }

        foreach ($campaigns as $campaign) {
            $this->info("Mise en file : {$campaign->title}...");
            $dispatcher->dispatch($campaign);
        }
    }
}
