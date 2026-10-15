<?php

namespace App\Console\Commands;

use App\Mail\BirthdayAnnouncementMail;
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
 * Souhaite leur anniversaire aux membres du jour, prévient les autres avec
 * le lien du mur de vœux, et renvoie le lendemain le récapitulatif des vœux.
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

    protected $description = 'Envoie la carte d\'anniversaire du jour, prévient les autres membres, et le récapitulatif des vœux de la veille';

    /** Membres fêtées pendant ce passage : ce sont elles que l'on annonce. */
    private array $celebratedIds = [];

    public function handle(BirthdayCardService $cards): int
    {
        $today = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : now();

        $this->celebratedIds = [];
        $greeted = $this->greet($cards, $today);
        $announced = $this->announce();
        $digests = $this->sendDigests();

        $this->info("Anniversaires souhaités : {$greeted}. Membres prévenues : {$announced}. Récapitulatifs envoyés : {$digests}.");

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
                        $this->celebratedIds[] = $member->id;
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
                        $this->celebratedIds[] = $member->id;
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
     * Prévient les autres membres, avec le lien du mur de chacune des fêtées.
     *
     * Seules les membres fêtées pendant ce passage sont annoncées : si
     * l'ordonnanceur repasse dans la journée, `birthday_greeted_at` les a déjà
     * écartées de greet(), et personne ne reçoit l'annonce deux fois.
     * Celles qui se sont opposées aux communications ne la reçoivent pas.
     */
    private function announce(): int
    {
        if ($this->celebratedIds === []) {
            return 0;
        }

        $celebrants = Member::whereIn('id', $this->celebratedIds)->orderBy('name')->get();
        $recipients = Member::reachableByEmail()->whereNotIn('id', $this->celebratedIds);

        if ($this->option('dry')) {
            return $recipients->count();
        }

        $sent = 0;

        $recipients->chunkById(100, function ($members) use ($celebrants, &$sent) {
            foreach ($members as $member) {
                try {
                    Mail::to($member->email)->queue(new BirthdayAnnouncementMail($member, $celebrants));
                    $sent++;
                } catch (\Throwable $e) {
                    // Une adresse refusée ne doit pas priver les suivantes.
                    Log::warning('Annonce d\'anniversaire : échec', ['member' => $member->id, 'error' => $e->getMessage()]);
                }
            }
        });

        // Les fêtées nées le même jour se souhaitent aussi : chacune reçoit
        // l'annonce des autres, jamais la sienne.
        if ($celebrants->count() > 1) {
            foreach ($celebrants as $celebrant) {
                $others = $celebrants->where('id', '!=', $celebrant->id)->values();

                if (! $celebrant->hasOptedOutOfMarketing() && $celebrant->email) {
                    Mail::to($celebrant->email)->queue(new BirthdayAnnouncementMail($celebrant, $others));
                    $sent++;
                }
            }
        }

        ActivityLog::record('birthday.announced', null, ['celebrants' => $this->celebratedIds, 'recipients' => $sent]);

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
