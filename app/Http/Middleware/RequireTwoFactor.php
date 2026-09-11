<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Impose le second facteur avant d'atteindre le back-office.
 *
 * Ne s'applique qu'aux comptes qui l'ont activé : la double authentification
 * reste facultative, sans quoi l'imposer d'un coup enfermerait dehors toute
 * l'équipe au moment du déploiement.
 */
class RequireTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();

        if (! $user || ! $user->hasTwoFactorEnabled()) {
            return $next($request);
        }

        if ($request->session()->get('two_factor.passed') === true) {
            return $next($request);
        }

        return redirect()->route('admin.two-factor.challenge');
    }
}
