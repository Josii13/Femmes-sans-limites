<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\DetectsBots;
use App\Models\Member;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Collecte des dates de naissance par un lien partagé dans le groupe WhatsApp.
 *
 * Chaque membre clique sur sa photo, saisit sa date, et disparaît du mur : la
 * page se vide à mesure que les réponses arrivent, ce qui rend l'avancement
 * visible sans que personne ait à relancer.
 *
 * L'URL porte un jeton : un lien qui circule sur WhatsApp finit par en sortir,
 * et sans jeton, le trouver donnerait l'annuaire complet des membres avec
 * leurs photos. Les membres ayant refusé la galerie publique apparaissent avec
 * leurs initiales — leur choix vaut ici aussi.
 */
class BirthdayCollectController extends Controller
{
    use DetectsBots;

    public function show(Request $request, string $token)
    {
        $this->assertToken($token);

        $members = Member::missingBirthday()->orderBy('name')->get();

        return view('public.birthdays.collect', [
            'token' => $token,
            'members' => $members,
            // Lien reçu par email : son formulaire s'ouvre directement. Ignoré
            // si sa date est déjà connue — elle n'est alors plus sur le mur.
            'preselected' => $members->firstWhere('id', $request->integer('membre')),
            'done' => Member::where('status', 'active')->whereNotNull('birth_day')->count(),
        ]);
    }

    public function store(Request $request, string $token)
    {
        $this->assertToken($token);

        if ($this->isBotSubmission($request, 'birthday.collect')) {
            return back()->with('saved', 'enregistrée');
        }

        $validated = $request->validate([
            'member_id' => 'required|integer|exists:members,id',
            'day' => 'required|integer|min:1|max:31',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'nullable|integer|min:1920|max:'.(now()->year - 10),
        ], [], [
            'day' => 'jour', 'month' => 'mois', 'year' => 'année',
        ]);

        // Le champ année est absent du tableau validé quand il n'a pas été
        // soumis : on le normalise une fois pour toutes plutôt que de jongler
        // avec `??` à chaque usage.
        $year = ! empty($validated['year']) ? (int) $validated['year'] : null;

        // 31 février, 31 avril… : l'année de référence est bissextile pour que
        // le 29 février reste accepté quand l'année n'est pas donnée.
        if (! checkdate($validated['month'], $validated['day'], $year ?? 2000)) {
            throw ValidationException::withMessages([
                'day' => 'Cette date n\'existe pas. Vérifie le jour et le mois.',
            ]);
        }

        $member = Member::where('id', $validated['member_id'])->where('status', 'active')->first();

        if (! $member) {
            return back()->with('error', 'Cette membre n\'est plus dans la liste.');
        }

        // Déjà renseignée : on ne l'écrase pas. Une fois sortie du mur, une
        // correction passe par l'équipe — sinon n'importe qui pourrait modifier
        // la date d'une autre après coup.
        if ($member->hasBirthday()) {
            return back()->with('error', 'La date de '.$member->name.' a déjà été enregistrée. Contacte l\'équipe pour la corriger.');
        }

        $member->forceFill([
            'birth_day' => $validated['day'],
            'birth_month' => $validated['month'],
            'birth_year' => $year,
            'birth_date_filled_at' => now(),
        ])->save();

        return back()->with('saved', $member->name);
    }

    private function assertToken(string $token): void
    {
        abort_unless(hash_equals(Setting::birthdayToken(), $token), 404);
    }
}
