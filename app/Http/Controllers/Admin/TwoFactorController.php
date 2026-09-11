<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\TotpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Double authentification du back-office.
 *
 * Le panneau contient les données personnelles des membres et les références de
 * paiement : un mot de passe seul y donnait accès.
 *
 * L'activation se fait en deux temps — on ne enregistre le second facteur
 * qu'après avoir vérifié un premier code. Sans cela, une erreur de scan
 * enfermerait l'administratrice dehors.
 */
class TwoFactorController extends Controller
{
    public function __construct(private TotpService $totp) {}

    public function show(Request $request)
    {
        $user = $request->user();

        return view('admin.two-factor.index', [
            'user' => $user,
            'pendingSecret' => $request->session()->get('two_factor.pending_secret'),
            'qrCode' => $this->pendingQrCode($request),
            'recoveryCodes' => $request->session()->get('two_factor.recovery_codes'),
        ]);
    }

    /** Prépare un secret, sans encore l'activer. */
    public function start(Request $request): RedirectResponse
    {
        $secret = $this->totp->generateSecret();

        $request->session()->put('two_factor.pending_secret', $secret);

        return redirect()->route('admin.two-factor.index');
    }

    /** Active réellement, après vérification d'un code issu de l'application. */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|string'], [
            'code.required' => 'Saisissez le code affiché par votre application.',
        ]);

        $secret = $request->session()->get('two_factor.pending_secret');

        if (! $secret) {
            return redirect()->route('admin.two-factor.index')
                ->with('error', 'La configuration a expiré. Recommencez.');
        }

        if (! $this->totp->verify($secret, $request->input('code'))) {
            return back()->withErrors(['code' => 'Ce code est incorrect ou a expiré. Réessayez avec le code affiché en ce moment.']);
        }

        $recoveryCodes = $this->totp->generateRecoveryCodes();

        $request->user()->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $request->session()->forget('two_factor.pending_secret');
        // Le défi vient d'être franchi : la session est considérée comme validée.
        $request->session()->put('two_factor.passed', true);

        ActivityLog::record('user.two_factor_enabled', $request->user());

        return redirect()->route('admin.two-factor.index')
            ->with('two_factor.recovery_codes', $recoveryCodes)
            ->with('success', 'Double authentification activée. Conservez vos codes de secours.');
    }

    /**
     * Désactivation, protégée par le mot de passe : sans cela, une session
     * laissée ouverte suffirait à retirer le second facteur.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate(['password' => 'required|string']);

        if (! Hash::check($request->input('password'), $request->user()->password)) {
            return back()->withErrors(['password' => 'Mot de passe incorrect.']);
        }

        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        ActivityLog::record('user.two_factor_disabled', $request->user());

        return redirect()->route('admin.two-factor.index')
            ->with('success', 'Double authentification désactivée.');
    }

    /** Régénère les codes de secours (par exemple après en avoir utilisé plusieurs). */
    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasTwoFactorEnabled(), 403);

        $codes = $this->totp->generateRecoveryCodes();
        $request->user()->forceFill(['two_factor_recovery_codes' => $codes])->save();

        return redirect()->route('admin.two-factor.index')
            ->with('two_factor.recovery_codes', $codes)
            ->with('success', 'Nouveaux codes de secours générés. Les anciens ne fonctionnent plus.');
    }

    // ── Défi à la connexion ─────────────────────────────────────

    public function showChallenge(Request $request)
    {
        if (! $request->user()?->hasTwoFactorEnabled()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.two-factor.challenge');
    }

    public function challenge(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|string'], [
            'code.required' => 'Saisissez votre code.',
        ]);

        $user = $request->user();
        $code = trim($request->input('code'));

        // Un code de secours est accepté en remplacement : téléphone perdu,
        // réinitialisé, ou application désinstallée.
        if ($this->totp->verify((string) $user->two_factor_secret, $code) || $user->consumeRecoveryCode($code)) {
            $request->session()->put('two_factor.passed', true);

            return redirect()->intended(route('admin.dashboard'));
        }

        ActivityLog::record('user.two_factor_failed', $user, ['ip' => $request->ip()]);

        return back()->withErrors(['code' => 'Code incorrect. Vérifiez l’heure de votre téléphone, ou utilisez un code de secours.']);
    }

    public function abandonChallenge(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function pendingQrCode(Request $request): ?string
    {
        $secret = $request->session()->get('two_factor.pending_secret');

        if (! $secret) {
            return null;
        }

        $uri = $this->totp->provisioningUri(
            $secret,
            $request->user()->email,
            config('app.name', 'Femme Sans Limites')
        );

        return QrCode::format('svg')->size(200)->errorCorrection('M')->generate($uri);
    }
}
