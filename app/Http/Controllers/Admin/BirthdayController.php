<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

    /** Régénère le lien quand il a trop circulé : l'ancien cesse de répondre. */
    public function rotate()
    {
        Setting::rotateBirthdayToken();
        ActivityLog::record('birthday.link_rotated', null, []);

        return back()->with('success', 'Nouveau lien généré. L\'ancien ne fonctionne plus.');
    }
}
