<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\RequireTwoFactor;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'role' => EnsureUserHasRole::class,
            '2fa' => RequireTwoFactor::class,
        ]);

        // Le site a DEUX espaces authentifiés : le back-office et l’espace membre.
        // Laravel ne connaît qu’une route « login » par défaut, si bien qu’une membre
        // non connectée atterrissait sur la page de connexion de l’administration,
        // où ses identifiants ne fonctionnent pas.
        //
        // Le réglage doit vivre ICI et non dans un service provider : withMiddleware()
        // réapplique sa valeur par défaut à la résolution du kernel HTTP, donc après le
        // boot des providers, et écraserait silencieusement toute configuration posée là.
        $middleware->redirectTo(
            guests: fn (Request $request) => $request->is('espace-membre*')
                ? route('member.login')
                : route('login'),
            users: fn (Request $request) => $request->is('espace-membre*')
                ? route('member.dashboard')
                : route('admin.dashboard'),
        );

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
