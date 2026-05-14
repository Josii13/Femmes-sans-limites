<?php

namespace App\Http\Controllers;

use App\Models\CampaignRecipient;
use Illuminate\Http\Response;

class TrackingController extends Controller
{
    public function pixel(string $token): Response
    {
        $recipient = CampaignRecipient::where('token', $token)->first();

        if ($recipient) {
            if (! $recipient->opened_at) {
                $recipient->opened_at = now();
                $recipient->campaign->increment('open_count');
            }
            $recipient->increment('open_count');
            $recipient->save();
        }

        // 1×1 GIF transparent
        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        return response($gif, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }
}
