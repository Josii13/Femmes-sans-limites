<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restreint une route à certains rôles du back-office.
 *
 * Utilisé en complément du middleware « admin », qui ne vérifie que l'accès
 * global : sans cela, une éditrice recrutée pour publier des ebooks pourrait
 * aussi consulter les paiements et supprimer des membres.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::guard('web')->user();

        if (! $user instanceof User || ! in_array($user->role, $roles, true)) {
            abort(403, 'Cette section est réservée à un autre niveau de droits.');
        }

        return $next($request);
    }
}
