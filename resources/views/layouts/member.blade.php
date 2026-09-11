<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Espace privé : jamais indexé. --}}
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'Mon espace') — Femme Sans Limites</title>

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background:var(--warm);">

<div x-data="{ menuOpen: false }">

    {{-- ── En-tête ── --}}
    <header class="sticky top-0 z-40 bg-white" style="border-bottom:1px solid var(--border);">
        <div class="max-w-6xl mx-auto px-5 lg:px-8">
            <div class="flex items-center justify-between h-16 gap-4">

                <a href="{{ route('member.dashboard') }}" class="flex items-center gap-2.5 min-w-0">
                    <img src="{{ asset('favicon.png') }}" alt="" class="w-8 h-8 rounded-lg flex-shrink-0">
                    <span class="font-bold text-sm truncate" style="color:var(--dark);font-family:'Playfair Display',serif;">Mon espace FSL</span>
                </a>

                {{-- Navigation desktop --}}
                <nav class="hidden md:flex items-center gap-1">
                    @foreach([
                        ['member.dashboard', 'Tableau de bord'],
                        ['member.registrations', 'Mes événements'],
                        ['member.purchases', 'Mes ebooks'],
                        ['member.directory', 'Annuaire'],
                        ['member.profile', 'Mon profil'],
                    ] as [$route, $label])
                    <a href="{{ route($route) }}"
                       class="text-sm px-3 py-2 rounded-lg transition-colors {{ request()->routeIs($route) ? 'font-semibold' : '' }}"
                       style="color:{{ request()->routeIs($route) ? 'var(--rose)' : 'var(--gray)' }};{{ request()->routeIs($route) ? 'background:var(--rose-pale);' : '' }}">
                        {{ $label }}
                    </a>
                    @endforeach
                </nav>

                <div class="flex items-center gap-2 flex-shrink-0">
                    <form action="{{ route('member.logout') }}" method="POST" class="hidden md:block">
                        @csrf
                        <button type="submit" class="text-sm px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors" style="color:var(--gray);">
                            Déconnexion
                        </button>
                    </form>

                    <button @click="menuOpen = !menuOpen" class="md:hidden w-9 h-9 flex items-center justify-center rounded-lg" style="color:var(--dark);">
                        <svg x-show="!menuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg x-show="menuOpen" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Navigation mobile --}}
        <div x-show="menuOpen" x-cloak class="md:hidden px-5 pb-4 space-y-1" style="border-top:1px solid var(--border);">
            @foreach([
                ['member.dashboard', 'Tableau de bord'],
                ['member.registrations', 'Mes événements'],
                ['member.purchases', 'Mes ebooks'],
                ['member.directory', 'Annuaire'],
                ['member.profile', 'Mon profil'],
            ] as [$route, $label])
            <a href="{{ route($route) }}" class="block text-sm px-3 py-2.5 rounded-lg {{ request()->routeIs($route) ? 'font-semibold' : '' }}"
               style="color:{{ request()->routeIs($route) ? 'var(--rose)' : 'var(--gray)' }};{{ request()->routeIs($route) ? 'background:var(--rose-pale);' : '' }}">
                {{ $label }}
            </a>
            @endforeach
            <form action="{{ route('member.logout') }}" method="POST" class="pt-2">
                @csrf
                <button type="submit" class="block w-full text-left text-sm px-3 py-2.5 rounded-lg" style="color:var(--gray);">Déconnexion</button>
            </form>
        </div>
    </header>

    {{-- ── Contenu ── --}}
    <main class="max-w-6xl mx-auto px-5 lg:px-8 py-8 lg:py-12">

        @if(session('success'))
        <div class="mb-6 flex items-start gap-2.5 p-4 rounded-xl text-sm" style="background:#D1FAE5;color:#065F46;">
            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 flex items-start gap-2.5 p-4 rounded-xl text-sm" style="background:#FEE2E2;color:#991B1B;">
            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        @yield('content')
    </main>

    <footer class="py-8 text-center">
        <a href="{{ route('home') }}" class="text-xs hover:underline" style="color:var(--gray);">← Retour au site</a>
        <p class="text-xs mt-2" style="color:var(--gray-light);">© {{ date('Y') }} Femme Sans Limites</p>
    </footer>
</div>

</body>
</html>
