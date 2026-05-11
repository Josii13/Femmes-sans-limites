<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Femmes Sans Limites')</title>
    <meta name="description" content="@yield('meta_description', 'Plateforme dédiée aux femmes qui désirent évoluer, se dépasser et briser les barrières.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">

    <!-- Navigation -->
    <nav id="navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-500 py-4">
        <div class="max-w-7xl mx-auto px-6 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex-shrink-0">
                <img src="{{ asset('logo_FSL.png') }}" alt="Femmes Sans Limites" class="h-14 w-auto">
            </a>

            <div class="hidden md:flex items-center gap-10">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Accueil</a>
                <a href="{{ route('about') }}" class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}">À propos</a>
                <a href="{{ route('events.index') }}" class="nav-link {{ request()->routeIs('events.*') ? 'active' : '' }}">Événements</a>
                <a href="{{ route('contact') }}" class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
            </div>

            <div class="hidden md:flex items-center gap-4">
                <a href="{{ route('events.index') }}" class="btn-rose text-sm px-6 py-2.5">
                    Rejoindre la communauté
                </a>
            </div>

            <button id="menu-toggle" class="md:hidden p-2 rounded-lg" aria-label="Menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-100 mt-4 px-6 py-4 space-y-3">
            <a href="{{ route('home') }}" class="block text-sm font-medium py-2 border-b border-gray-50">Accueil</a>
            <a href="{{ route('about') }}" class="block text-sm font-medium py-2 border-b border-gray-50">À propos</a>
            <a href="{{ route('events.index') }}" class="block text-sm font-medium py-2 border-b border-gray-50">Événements</a>
            <a href="{{ route('contact') }}" class="block text-sm font-medium py-2">Contact</a>
            <a href="{{ route('events.index') }}" class="btn-rose text-sm block text-center mt-4">Rejoindre</a>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="py-16 px-6" style="background: var(--dark);">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mb-12">
                <div>
                    <img src="{{ asset('logo_FSL.png') }}" alt="FSL" class="h-16 w-auto mb-4" style="filter: brightness(0) invert(1) opacity(0.8);">
                    <p class="text-sm leading-relaxed" style="color: rgba(255,255,255,0.6);">
                        Une plateforme dédiée aux femmes qui désirent évoluer, se dépasser et briser les barrières.
                    </p>
                </div>

                <div>
                    <h4 class="text-white font-semibold mb-4" style="font-family:'DM Sans',sans-serif;">Navigation</h4>
                    <ul class="space-y-2">
                        @foreach([['home','Accueil'],['about','À propos'],['events.index','Événements'],['contact','Contact']] as [$r,$l])
                        <li><a href="{{ route($r) }}" class="text-sm transition-colors hover:text-white" style="color:rgba(255,255,255,0.6);">{{ $l }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-semibold mb-4" style="font-family:'DM Sans',sans-serif;">Contact</h4>
                    <p class="text-sm" style="color:rgba(255,255,255,0.6);">contact@femmessanslimites.com</p>
                    <div class="flex gap-3 mt-6">
                        <a href="#" class="w-9 h-9 rounded-full flex items-center justify-center" style="background:rgba(255,255,255,0.1);" aria-label="Instagram">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a href="#" class="w-9 h-9 rounded-full flex items-center justify-center" style="background:rgba(255,255,255,0.1);" aria-label="Facebook">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-t pt-8 flex flex-col md:flex-row items-center justify-between gap-4" style="border-color:rgba(255,255,255,0.1);">
                <p class="text-xs" style="color:rgba(255,255,255,0.4);">&copy; {{ date('Y') }} Femmes Sans Limites. Tous droits réservés.</p>
                <div class="flex items-center gap-2">
                    <span class="w-6 h-0.5" style="background:var(--rose);"></span>
                    <span class="w-6 h-0.5" style="background:var(--gold);"></span>
                </div>
            </div>
        </div>
    </footer>

    <script>
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 60) {
                navbar.classList.add('bg-white/95', 'shadow-sm', 'backdrop-blur-md');
                navbar.style.paddingTop = '0.6rem';
                navbar.style.paddingBottom = '0.6rem';
            } else {
                navbar.classList.remove('bg-white/95', 'shadow-sm', 'backdrop-blur-md');
                navbar.style.paddingTop = '1rem';
                navbar.style.paddingBottom = '1rem';
            }
        });

        document.getElementById('menu-toggle')?.addEventListener('click', () => {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });

        const observer = new IntersectionObserver(entries => {
            entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
        }, { threshold: 0.1 });
        document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
    </script>

    @stack('scripts')
</body>
</html>
