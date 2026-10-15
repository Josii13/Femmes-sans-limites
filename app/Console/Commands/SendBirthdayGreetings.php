<?php

namespace App\Console\Commands;

use App\Mail\BirthdayMail;
use App\Mail\BirthdayWishesDigestMail;
use App\Models\ActivityLog;
use App\Models\Member;
use App\Services\BirthdayCardService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Souhaite leur anniversaire aux membres du jour, et renvoie le lendemain
 * le récapitulatif des vœux reçus.
 *
 * Deux garde-fous repris des relances d'adhésion : on ne fête que les
 * adhésions actives — un « joyeux anniversaire » à quelqu'un dont l'adhésion
 * a été rejetée serait humiliant — et `birthday_greeted_at` empêche le double
 * envoi si l'ordonnanceur repasse dans la journée.
 */
class SendBirthdayGreetings extends Command
{
    protected $signature = 'members:birthday
        {--date= : Forcer une date (Y-m-d), pour tester}
        {--dry : N\'envoie rien, affiche seulement qui serait fêtée}';

    protected $description = 'Envoie la carte d\'anniversaire du jour et le récapitulatif des vœux de la veille';

    public function handle(BirthdayCardService $cards): int
    {
        $today = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : now();

        $greeted = $this->greet($cards, $today);
        $digests = $this->sendDigests();

        $this->info("Anniversaires souhaités : {$greeted}. Récapitulatifs envoyés : {$digests}.");

        return self::SUCCESS;
    }

    private function greet(BirthdayCardService $cards, Carbon $today): int
    {
        $sent = 0;

        Member::birthdayOn($today)
            ->where(function ($q) use ($today) {
                // Jamais fêtée, ou fêtée lors d'une année précédente.
                $q->whereNull('birthday_greeted_at')
                    ->orWhere('birthday_greeted_at', '<', $today->copy()->startOfYear());
            })
            ->chunkById(100, function ($members) use ($cards, &$sent) {
                foreach ($members as $member) {
                    if ($this->option('dry')) {
                        $this->line('  '.$member->name.' — '.$member->email);
                        $sent++;

                        continue;
                    }

                    try {
                        $member->birthdayToken();
                        $member->forceFill([
                            'birthday_card_path' => $cards->generate($member),
                            'birthday_greeted_at' => now(),
                            // Le récapitulatif de l'an dernier ne doit pas bloquer celui-ci.
                            'birthday_digest_sent_at' => null,
                        ])->save();

                        Mail::to($member->email)->queue(new BirthdayMail($member->fresh()));
                        ActivityLog::record('birthday.greeted', $member, []);
                        $sent++;
                    } catch (\Throwable $e) {
                        // Une carte qui échoue ne doit pas priver les suivantes.
                        Log::warning('Anniversaire : échec', ['member' => $member->id, 'error' => $e->getMessage()]);
                        $this->warn('  Échec pour '.$member->name.' : '.$e->getMessage());
                    }
                }
            });

        return $sent;
    }

    /**
     * Récapitulatif pour celles fêtées il y a plus d'un jour, qui ont reçu des
     * vœux et n'ont pas encore eu leur résumé.
     */
    private function sendDigests(): int
    {
        if ($this->option('dry')) {
            return 0;
        }

        $sent = 0;

        Member::whereNotNull('birthday_greeted_at')
            ->whereNull('birthday_digest_sent_at')
            ->where('birthday_greeted_at', '<', now()->subDay())
            ->where('status', 'active')
            ->has('birthdayWishes')
            ->chunkById(100, function ($members) use (&$sent) {
                foreach ($members as $member) {
                    $wishes = $member->birthdayWishes()->visible()->get();

                    if ($wishes->isEmpty()) {
                        // Tous les mots ont été masqués : on note quand même le
                        // passage, sinon la requête le reprend chaque jour.
                        $member->forceFill(['birthday_digest_sent_at' => now()])->save();

                        continue;
                    }

                    Mail::to($member->email)->queue(new BirthdayWishesDigestMail($member, $wishes));
                    $member->forceFill(['birthday_digest_sent_at' => now()])->save();
                    $sent++;
                }
            });

        return $sent;
    }
}
