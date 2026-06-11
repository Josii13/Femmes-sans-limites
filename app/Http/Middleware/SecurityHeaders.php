<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Ajoute des en-têtes de sécurité à toutes les réponses (défense en profondeur :
     * anti-clickjacking, anti-MIME-sniffing, fuite de referrer, permissions navigateur).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            // Empêche l'interprétation MIME hasardeuse (anti-sniffing).
            'X-Content-Type-Options' => 'nosniff',
            // Anti-clickjacking : le site ne peut pas être embarqué dans une iframe tierce.
            'X-Frame-Options' => 'SAMEORIGIN',
            // Ne fuite pas l'URL complète vers les sites externes.
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            // Désactive les API sensibles ; caméra autorisée pour le scanner QR (même origine).
            'Permissions-Policy' => 'camera=(self), microphone=(), geolocation=(), payment=()',
        ];

        foreach ($headers as $key => $value) {
            if (! $response->headers->has($key)) {
                $response->headers->set($key, $value);
            }
        }

        return $response;
    }
}
