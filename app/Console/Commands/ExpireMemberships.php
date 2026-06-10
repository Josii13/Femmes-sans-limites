<?php

namespace App\Console\Commands;

use App\Mail\MembershipRenewalMail;
use App\Models\Member;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ExpireMemberships extends Command
{
    protected $signature = 'members:expire {--days=30 : Fenêtre de relance avant échéance}';

    protected $description = 'Expire les adhésions échues et relance les membres dont l\'adhésion approche';

    public function handle(): int
    {
        $window = (int) $this->option('days');

        // 1) Relance des adhésions actives qui expirent dans les {window} jours (une seule fois).
        $reminded = 0;
        Member::active()
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays($window)])
            ->where(function ($q) {
                $q->whereNull('renewal_reminded_at')
                    ->orWhere('renewal_reminded_at', '<', now()->subDays(60));
            })
            ->chunkById(200, function ($members) use (&$reminded) {
                foreach ($members as $member) {
                    Mail::to($member->email)->queue(new MembershipRenewalMail($member, expired: false));
                    $member->forceFill(['renewal_reminded_at' => now()])->save();
                    $reminded++;
                }
            });

        // 2) Expiration des adhésions actives échues.
        $expired = 0;
        Member::active()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->chunkById(200, function ($members) use (&$expired) {
                foreach ($members as $member) {
                    $member->update(['status' => 'expired']);
                    Mail::to($member->email)->queue(new MembershipRenewalMail($member, expired: true));
                    $expired++;
                }
            });

        $this->info("Relances envoyées : {$reminded} — Adhésions expirées : {$expired}.");

        return self::SUCCESS;
    }
}
