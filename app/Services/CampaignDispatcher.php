<?php

namespace App\Services;

use App\Jobs\SendCampaignEmail;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Member;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Bus;
use Throwable;

class CampaignDispatcher
{
    /**
     * Résout les destinataires d'une campagne selon sa cible.
     * Exclut toujours les membres opposés au marketing (RGPD) et sans email.
     */
    public function resolveRecipients(Campaign $campaign): Collection
    {
        $base = fn () => Member::query()
            ->where('status', 'active')
            ->marketable()
            ->whereNotNull('email')
            ->where('email', '!=', '');

        return match ($campaign->target_type) {
            'all' => $base()->get(),
            'standard', 'gold', 'premium' => $base()->where('type', $campaign->target_type)->get(),
            'single' => $base()->where('id', $campaign->target_member_id)->get(),
            'custom' => $base()->whereIn('id', $campaign->target_member_ids ?? [])->get(),
            default => new Collection,
        };
    }

    /**
     * Met une campagne en file d'envoi.
     *
     * - Garde anti-double-envoi : refuse si la campagne est déjà « sending » ou « sent ».
     * - Crée les destinataires de façon idempotente (firstOrCreate) pour éviter les doublons.
     * - Délègue chaque email à un Job en file (Bus::batch) : la requête HTTP ne bloque plus.
     * - Le statut passe « sent » automatiquement à la fin du batch.
     *
     * @return bool true si l'envoi a bien été programmé, false si refusé (garde).
     */
    public function dispatch(Campaign $campaign): bool
    {
        if (in_array($campaign->status, ['sending', 'sent'], true)) {
            return false;
        }

        $members = $this->resolveRecipients($campaign);

        $campaign->update(['status' => 'sending']);

        if ($members->isEmpty()) {
            $campaign->update(['status' => 'sent', 'sent_at' => now()]);

            return true;
        }

        $jobs = $members->map(function ($member) use ($campaign) {
            $recipient = CampaignRecipient::firstOrCreate(
                ['campaign_id' => $campaign->id, 'member_id' => $member->id],
                ['email' => $member->email, 'name' => $member->name],
            );

            return new SendCampaignEmail($recipient);
        })->all();

        $campaignId = $campaign->id;

        Bus::batch($jobs)
            ->name("campaign:{$campaignId}")
            ->finally(function () use ($campaignId) {
                Campaign::whereKey($campaignId)->update(['status' => 'sent', 'sent_at' => now()]);
            })
            ->onQueue('emails')
            ->dispatch();

        return true;
    }

    /**
     * Variante synchrone (tests, file « sync ») : envoie immédiatement sans batch.
     */
    public function dispatchNow(Campaign $campaign): bool
    {
        if (in_array($campaign->status, ['sending', 'sent'], true)) {
            return false;
        }

        $members = $this->resolveRecipients($campaign);

        $campaign->update(['status' => 'sending']);

        foreach ($members as $member) {
            $recipient = CampaignRecipient::firstOrCreate(
                ['campaign_id' => $campaign->id, 'member_id' => $member->id],
                ['email' => $member->email, 'name' => $member->name],
            );

            try {
                (new SendCampaignEmail($recipient))->handle();
            } catch (Throwable) {
                // déjà journalisé dans le Job
            }
        }

        $campaign->update(['status' => 'sent', 'sent_at' => now()]);

        return true;
    }
}
