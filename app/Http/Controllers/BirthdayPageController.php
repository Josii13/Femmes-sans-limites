<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\DetectsBots;
use App\Models\BirthdayWish;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Page d'anniversaire d'une membre : sa carte, et le mur où la communauté
 * lui laisse un mot.
 *
 * Même parti pris que la collecte : un lien impossible à deviner, partagé
 * dans le groupe, plutôt qu'un compte à créer. Demander une connexion le jour
 * d'une fête, c'est s'assurer que personne n'écrira.
 */
class BirthdayPageController extends Controller
{
    use DetectsBots;

    public function show(string $token)
    {
        $member = $this->findMember($token);

        return view('public.birthdays.show', [
            'member' => $member,
            'wishes' => $member->birthdayWishes()->visible()->get(),
            'open' => $member->birthdayWishesOpen(),
        ]);
    }

    public function storeWish(Request $request, string $token)
    {
        $member = $this->findMember($token);

        if ($this->isBotSubmission($request, 'birthday.wish')) {
            return back()->with('wished', true);
        }

        if (! $member->birthdayWishesOpen()) {
            return back()->with('error', 'Les vœux sont clos pour cette année.');
        }

        $validated = $request->validate([
            'author_name' => 'required|string|max:80',
            'message' => 'required|string|max:500',
        ], [], ['author_name' => 'prénom', 'message' => 'message']);

        BirthdayWish::create([
            'member_id' => $member->id,
            'author_name' => trim($validated['author_name']),
            'message' => trim($validated['message']),
            'ip' => $request->ip(),
        ]);

        return back()->with('wished', true);
    }

    /** La carte, pour l'enregistrer et la partager. */
    public function card(string $token): StreamedResponse
    {
        $member = $this->findMember($token);

        abort_unless(
            $member->birthday_card_path && Storage::disk('public')->exists($member->birthday_card_path),
            404
        );

        return Storage::disk('public')->download(
            $member->birthday_card_path,
            'anniversaire-'.Str::slug($member->name).'.png'
        );
    }

    /**
     * La page n'existe qu'une fois la membre fêtée : avant l'envoi, le lien
     * ne doit rien révéler, pas même que quelqu'un fête bientôt son
     * anniversaire.
     */
    private function findMember(string $token): Member
    {
        return Member::where('birthday_token', $token)
            ->where('status', 'active')
            ->whereNotNull('birthday_greeted_at')
            ->firstOrFail();
    }
}
