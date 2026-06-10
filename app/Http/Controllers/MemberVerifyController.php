<?php

namespace App\Http\Controllers;

use App\Models\Member;

class MemberVerifyController extends Controller
{
    /**
     * Page publique de vérification d'une carte de membre (cible du QR code).
     * Confirme l'appartenance et la validité, sans exposer de données sensibles.
     */
    public function show(string $token)
    {
        $member = Member::where('verification_token', $token)->first();

        return view('public.member-verify', [
            'member' => $member,
            'valid' => $member && $member->isActive() && ! $member->isExpired(),
        ]);
    }
}
