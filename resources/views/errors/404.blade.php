<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page introuvable — Femmes Sans Limites</title>
    @vite(['resources/css/app.css'])
</head>
<body class="antialiased min-h-screen flex flex-col items-center justify-center px-5" style="background:var(--cream);">

<div class="text-center max-w-lg mx-auto">

    {{-- Logo --}}
    <a href="{{ url('/') }}" class="inline-flex items-center justify-center mb-10 rounded-2xl px-4 py-2 transition-opacity hover:opacity-80" style="background:#FDF0F5;">
        <img src="{{ asset('logo_FSL.png') }}" alt="Femmes Sans Limites" class="h-10 w-auto">
    </a>

    {{-- Illustration --}}
    <div class="flex items-center justify-center mb-6">
        <div class="relative">
            <p class="text-[120px] leading-none font-black select-none" style="color:var(--rose);opacity:.12;">404</p>
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-20 h-20 rounded-2xl flex items-center justify-center shadow-md" style="background:white;">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--rose);" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Message --}}
    <h1 class="text-2xl font-black mb-3" style="color:var(--dark);">Page introuvable</h1>
    <p class="text-base mb-8 leading-relaxed" style="color:var(--gray);">
        Cette page n'existe pas ou a été déplacée.<br>
        Revenez à l'accueil pour continuer votre exploration.
    </p>

    {{-- Actions --}}
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="{{ url('/') }}" class="btn-rose inline-flex items-center gap-2 justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Retour à l'accueil
        </a>
        <a href="{{ url('/evenements') }}" class="btn-outline inline-flex items-center gap-2 justify-center">
            Voir les événements
        </a>
    </div>

</div>

{{-- Footer minimal --}}
<p class="absolute bottom-6 text-xs" style="color:var(--gray);">
    © {{ date('Y') }} Femmes Sans Limites — Tous droits réservés
</p>

</body>
</html>
