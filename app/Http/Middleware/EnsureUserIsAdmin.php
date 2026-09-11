<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Bloque l'accès au back-office à tout utilisateur qui n'est pas administrateur.
     * Doit être appliqué APRÈS le middleware « auth ».
     *
     * Le guard « web » est nommé explicitement, et le type de l'utilisateur est
     * vérifié : depuis l'ouverture de l'espace membre, une autre identité peut
     * occuper le guard par défaut. `$request->user()?->isAdmin()` provoquait alors
     * une erreur 500 au lieu d'un refus propre.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();

        if (! $user instanceof User || ! $user->isAdmin()) {
            abort(403, "Accès réservé à l'administration de Femme Sans Limites.");
        }

        return $next($request);
    }
}
