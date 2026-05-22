<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur serveur — Femme Sans Limites</title>
    @vite(['resources/css/app.css'])
</head>
<body class="antialiased min-h-screen flex flex-col items-center justify-center px-5" style="background:var(--cream);">

<div class="text-center max-w-lg mx-auto">

    {{-- Logo --}}
    <a href="{{ url('/') }}" class="inline-flex items-center justify-center mb-10 rounded-2xl px-4 py-2 transition-opacity hover:opacity-80" style="background:#FDF0F5;">
        <img src="{{ asset('logo_FSL.png') }}" alt="Femme Sans Limites" class="h-10 w-auto">
    </a>

    {{-- Illustration --}}
    <div class="flex items-center justify-center mb-6">
        <div class="relative">
            <p class="text-[120px] leading-none font-black select-none" style="color:var(--rose);opacity:.12;">500</p>
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-20 h-20 rounded-2xl flex items-center justify-center shadow-md" style="background:white;">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--rose);" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Message --}}
    <h1 class="text-2xl font-black mb-3" style="color:var(--dark);">Une erreur est survenue</h1>
    <p class="text-base mb-8 leading-relaxed" style="color:var(--gray);">
        Quelque chose s'est mal passé de notre côté.<br>
        Nos équipes ont été notifiées. Réessayez dans quelques instants.
    </p>

    {{-- Actions --}}
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="{{ url('/') }}" class="btn-rose inline-flex items-center gap-2 justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Retour à l'accueil
        </a>
        <button onclick="location.reload()" class="btn-outline inline-flex items-center gap-2 justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Réessayer
        </button>
    </div>

</div>

{{-- Footer minimal --}}
<p class="absolute bottom-6 text-xs" style="color:var(--gray);">
    © {{ date('Y') }} Femme Sans Limites — Tous droits réservés
</p>

</body>
</html>
