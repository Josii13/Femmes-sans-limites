<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendBirthdayDateRequests;
use App\Models\ActivityLog;
use App\Models\BirthdayWish;
use App\Models\Member;
use App\Models\Setting;

/**
 * Suivi de la collecte des dates de naissance et du lien à partager.
 */
class BirthdayController extends Controller
{
    public function index()
    {
        return view('admin.birthdays.index', [
            'link' => route('birthdays.collect', Setting::birthdayToken()),
            'missing' => Member::missingBirthday()->orderBy('name')->get(),
            // Celles qui recevraient l'email si on cliquait maintenant.
            'invitable' => Member::invitableForBirthday()->count(),
            // Les vœux récents, pour pouvoir masquer un message déplacé.
            'wishes' => BirthdayWish::with('member')->latest()->limit(30)->get(),
            // Classées par mois puis par jour : c'est l'ordre dans lequel
            // l'équipe les lit pour préparer le mois à venir.
            'filled' => Member::where('status', 'active')
                ->whereNotNull('birth_day')
                ->orderBy('birth_month')->orderBy('birth_day')
                ->get(),
        ]);
    }

    /**
     * Masque ou réaffiche un mot du mur.
     *
     * On masque sans détruire : une décision prise vite un jour de fête doit
     * pouvoir se défaire.
     */
    public function toggleWish(BirthdayWish $wish)
    {
        $wish->update(['hidden_at' => $wish->hidden_at ? null : now()]);

        return back()->with('success', $wish->hidden_at ? 'Message masqué.' : 'Message réaffiché.');
    }

    /**
     * Envoie par email le lien de collecte à toutes les membres dont la date
     * manque — pour celles qui ne sont pas dans le groupe WhatsApp, ou qui y
     * ont laissé passer le lien.
     *
     * Les destinataires sont réservées ici, avant l'envoi, en posant leur date
     * d'invitation : un double clic, ou deux administratrices au même moment,
     * ne trouvent plus personne à inviter au second passage.
     */
    public function invite()
    {
        $ids = Member::invitableForBirthday()->pluck('id')->all();

        if ($ids === []) {
            return back()->with('error', 'Aucune membre à inviter : toutes ont répondu, ont déjà reçu l\'email ces '
                .Member::BIRTHDAY_INVITE_COOLDOWN_DAYS.' derniers jours, ou ne reçoivent pas nos communications.');
        }

        $stamp = now()->startOfSecond();
        Member::whereIn('id', $ids)->invitableForBirthday()->update(['birthday_invited_at' => $stamp]);
        $claimed = Member::whereIn('id', $ids)->where('birthday_invited_at', $stamp)->pluck('id')->all();

        // Après l'envoi de la réponse : avec la file « sync », les emails
        // partiraient sinon pendant la requête et bloqueraient la page.
        $job = new SendBirthdayDateRequests($claimed, auth()->id());
        defer(fn () => $job->handle());

        return back()->with('success', count($claimed).' email(s) en cours d\'envoi avec le lien pour indiquer sa date.');
    }

    /** Régénère le lien quand il a trop circulé : l'ancien cesse de répondre. */
    public function rotate()
    {
        Setting::rotateBirthdayToken();
        ActivityLog::record('birthday.link_rotated', null, []);

        return back()->with('success', 'Nouveau lien généré. L\'ancien ne fonctionne plus.');
    }
}
