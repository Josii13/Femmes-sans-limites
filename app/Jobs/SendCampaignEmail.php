<?php

namespace App\Jobs;

use App\Mail\CampaignMail;
use App\Models\CampaignRecipient;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCampaignEmail implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public CampaignRecipient $recipient
    ) {}

    public function handle(): void
    {
        // Le batch a pu être annulé entre-temps.
        if ($this->batch()?->cancelled()) {
            return;
        }

        $recipient = $this->recipient;
        $campaign = $recipient->campaign;

        // Idempotence : si déjà envoyé, ne rien refaire (relance de job).
        if (! $campaign || $recipient->sent_at) {
            return;
        }

        try {
            Mail::to($recipient->email)->send(new CampaignMail($campaign, $recipient));
            $recipient->forceFill(['sent_at' => now(), 'failed_at' => null])->save();
            $campaign->increment('sent_count');
        } catch (\Throwable $e) {
            // Un échec destinataire ne doit pas faire échouer tout le batch.
            $recipient->forceFill(['failed_at' => now()])->save();
            $campaign->increment('failed_count');
            Log::warning('Campagne: échec envoi destinataire', [
                'campaign_id' => $campaign->id,
                'email' => $recipient->email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
