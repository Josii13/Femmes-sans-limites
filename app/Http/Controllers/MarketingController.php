<?php

namespace App\Http\Controllers;

use App\Models\CampaignRecipient;

class MarketingController extends Controller
{
    /**
     * Page de confirmation d'opposition au marketing pour un membre,
     * identifié par le token unique de son destinataire de campagne.
     */
    public function unsubscribe(string $token)
    {
        $recipient = CampaignRecipient::where('token', $token)->with('member')->firstOrFail();

        return view('public.marketing.unsubscribe', [
            'recipient' => $recipient,
            'member' => $recipient->member,
        ]);
    }

    /**
     * Enregistre l'opposition au marketing du membre (RGPD, droit d'opposition).
     */
    public function unsubscribeConfirm(string $token)
    {
        $recipient = CampaignRecipient::where('token', $token)->with('member')->firstOrFail();

        if ($recipient->member && ! $recipient->member->hasOptedOutOfMarketing()) {
            $recipient->member->update(['marketing_opt_out_at' => now()]);
        }

        return view('public.marketing.unsubscribed');
    }
}
