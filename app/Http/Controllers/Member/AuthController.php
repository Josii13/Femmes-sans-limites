<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Authentification de l'espace membre.
 *
 * Session distincte de celle du back-office : une connexion membre ne donne
 * jamais accès à l'administration, et l'inverse est vrai aussi.
 *
 * Aucun mot de passe n'est jamais envoyé par email. Une nouvelle adhérente
 * définit le sien via le lien signé reçu à l'activation, par le même canal que
 * « mot de passe oublié ».
 */
class AuthController extends Controller
{
    public function showLogin()
    {
        return view('member.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'Indiquez votre adresse email.',
            'password.required' => 'Indiquez votre mot de passe.',
        ]);

        $this->ensureNotRateLimited($request);

        $email = mb_strtolower(trim($credentials['email']));
        $member = Member::where('email', $email)->first();

        // Message identique dans tous les cas d'échec : indiquer « ce compte
        // n'existe pas » révélerait qui est membre de l'association.
        if (! $member || ! $member->password || ! Hash::check($credentials['password'], $member->password)) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => 'Ces identifiants ne correspondent à aucun compte actif.',
            ]);
        }

        // L'adhésion doit être en cours. Le message est explicite ici : la
        // personne a prouvé son identité, elle mérite de savoir pourquoi on refuse.
        if (! $member->canAccessPortal()) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => $member->isExpired()
                    ? 'Votre adhésion est arrivée à échéance. Renouvelez-la pour retrouver votre espace.'
                    : 'Votre adhésion n’est pas active. Contactez-nous si vous pensez qu’il s’agit d’une erreur.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        Auth::guard('member')->login($member, $request->boolean('remember'));
        $request->session()->regenerate();

        $member->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('member.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('member')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Vous êtes déconnectée. À bientôt !');
    }

    // ── Définition et réinitialisation du mot de passe ───────────

    public function showForgotPassword()
    {
        return view('member.auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email'], [
            'email.required' => 'Indiquez votre adresse email.',
        ]);

        $email = mb_strtolower(trim($request->input('email')));

        // Envoi uniquement aux adhésions en cours, mais réponse toujours
        // identique : le formulaire ne doit pas permettre de deviner qui est membre.
        $member = Member::where('email', $email)->where('status', 'active')->first();

        if ($member && ! $member->isExpired()) {
            Password::broker('members')->sendResetLink(['email' => $email]);
        }

        return back()->with('status', 'Si cette adresse correspond à une adhésion active, un lien vient d’être envoyé.');
    }

    public function showResetPassword(Request $request, string $token)
    {
        return view('member.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)],
        ], [
            'password.confirmed' => 'Les deux mots de passe ne correspondent pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        $status = Password::broker('members')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Member $member, string $password) {
                $member->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status !== Password::PasswordReset) {
            throw ValidationException::withMessages([
                'email' => 'Ce lien n’est plus valable. Demandez-en un nouveau.',
            ]);
        }

        return redirect()->route('member.login')
            ->with('status', 'Votre mot de passe est enregistré. Vous pouvez vous connecter.');
    }

    // ── Limitation des tentatives ───────────────────────────────

    /** Protège contre le bourrage d'identifiants sans bloquer une membre distraite. */
    private function ensureNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => 'Trop de tentatives. Réessayez dans '.ceil($seconds / 60).' minute(s).',
        ]);
    }

    private function throttleKey(Request $request): string
    {
        return 'member-login:'.Str::lower((string) $request->input('email')).'|'.$request->ip();
    }
}
