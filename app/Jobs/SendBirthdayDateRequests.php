<?php

namespace App\Jobs;

use App\Mail\BirthdayDateRequestMail;
use App\Models\ActivityLog;
use App\Models\Member;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Envoie à chaque membre listée l'email l'invitant à donner sa date de
 * naissance.
 *
 * Lancé après la réponse HTTP (defer) : avec la file « sync »
 * de la production, chaque email partirait sinon pendant la requête, et la
 * page d'administration resterait bloquée le temps de tous les envois SMTP.
 *
 * Les destinataires ont déjà été réservées par le contrôleur (date
 * d'invitation posée avant l'envoi) : un double clic ne peut donc pas les
 * inviter deux fois. Celle qui a donné sa date entre-temps est sautée, et un
 * échec d'envoi lève la réservation pour qu'elle reste à relancer.
 */
class SendBirthdayDateRequests
{
    /** @param  int[]  $memberIds */
    public function __construct(public array $memberIds, public ?int $userId = null) {}

    public function handle(): void
    {
        $sent = 0;

        Member::whereIn('id', $this->memberIds)
            ->missingBirthday()
            ->reachableByEmail()
            ->chunkById(50, function ($members) use (&$sent) {
                foreach ($members as $member) {
                    try {
                        Mail::to($member->email)->queue(new BirthdayDateRequestMail($member));
                        $sent++;
                    } catch (\Throwable $e) {
                        $member->forceFill(['birthday_invited_at' => null])->save();
                        Log::warning('Demande de date d\'anniversaire : échec', [
                            'member' => $member->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        ActivityLog::record('birthday.date_requested', null, ['sent' => $sent, 'by' => $this->userId]);
    }
}
