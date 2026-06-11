<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);

        // Détection correcte du HTTPS derrière le proxy de l'hébergeur (URLs, cookies sécurisés).
        $middleware->trustProxies(at: '*');

        // Le webhook de paiement vient d'un serveur tiers : exempté du CSRF (protégé par signature HMAC).
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
        ]);

        // En-têtes de sécurité sur toutes les réponses web.
        $middleware->web(append: [
            SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
