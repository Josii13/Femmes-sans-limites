<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Bloque l'accès au back-office à tout utilisateur qui n'est pas administrateur.
     * Doit être appliqué APRÈS le middleware « auth ».
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isAdmin()) {
            abort(403, "Accès réservé à l'administration de Femme Sans Limites.");
        }

        return $next($request);
    }
}
