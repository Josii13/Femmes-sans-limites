<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — FSL Back Office</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased" style="background:#F4F3F6;">

<div class="flex min-h-screen">

    {{-- ══ SIDEBAR ══ --}}
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 flex flex-col transition-transform duration-300" style="background:var(--dark);">

        {{-- Logo --}}
        <div class="flex items-center justify-center px-5 py-5" style="border-bottom:1px solid rgba(255,255,255,0.07);">
            <a href="{{ route('admin.dashboard') }}" class="inline-flex rounded-2xl p-2 hover:opacity-90 transition-opacity" style="background:rgba(253,240,245,0.92);">
                <img src="{{ asset('logo_FSL.png') }}" alt="FSL" class="h-12 w-auto" style="filter:drop-shadow(0 1px 4px rgba(217,30,110,0.18));">
            </a>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 px-3 py-5 space-y-0.5 overflow-y-auto">

            <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Tableau de bord
            </a>

            <div class="pt-5 pb-1.5 px-3">
                <p class="text-[10px] font-bold uppercase tracking-[0.13em]" style="color:rgba(255,255,255,0.25);">Membres</p>
            </div>
            <a href="{{ route('admin.members.index') }}" class="admin-nav-link {{ request()->routeIs('admin.members.index') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Tous les membres
            </a>
            <a href="{{ route('admin.members.create') }}" class="admin-nav-link {{ request()->routeIs('admin.members.create') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4v16m8-8H4"/></svg>
                Nouveau membre
            </a>

            <div class="pt-5 pb-1.5 px-3">
                <p class="text-[10px] font-bold uppercase tracking-[0.13em]" style="color:rgba(255,255,255,0.25);">Événements</p>
            </div>
            <a href="{{ route('admin.events.index') }}" class="admin-nav-link {{ request()->routeIs('admin.events.index') || request()->routeIs('admin.events.show') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Tous les événements
            </a>
            <a href="{{ route('admin.events.create') }}" class="admin-nav-link {{ request()->routeIs('admin.events.create') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4v16m8-8H4"/></svg>
                Créer un événement
            </a>

            <div class="pt-5 pb-1.5 px-3">
                <p class="text-[10px] font-bold uppercase tracking-[0.13em]" style="color:rgba(255,255,255,0.25);">Outils</p>
            </div>
            <a href="{{ route('admin.events.index') }}#scanner" class="admin-nav-link {{ request()->routeIs('admin.scanner*') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                Scanner QR
            </a>
            <a href="{{ url('/') }}" target="_blank" class="admin-nav-link">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                Voir le site
            </a>
            <a href="{{ route('admin.activity') }}" class="admin-nav-link {{ request()->routeIs('admin.activity') ? 'active' : '' }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Journal d'activité
            </a>
        </nav>

        {{-- User --}}
        <div class="px-3 py-4" style="border-top:1px solid rgba(255,255,255,0.07);">
            <div class="flex items-center gap-3 px-2 py-2 rounded-xl" style="background:rgba(255,255,255,0.05);">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0" style="background:var(--rose);">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                    <p class="text-[10px] truncate" style="color:rgba(255,255,255,0.35);">Administrateur FSL</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Déconnexion" class="p-1.5 rounded-lg transition-colors hover:bg-white/10 flex-shrink-0">
                        <svg class="w-4 h-4" style="color:rgba(255,255,255,0.4);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ══ MAIN CONTENT ══ --}}
    <div class="flex-1 ml-64 flex flex-col min-h-screen">

        {{-- Top bar --}}
        <header class="sticky top-0 z-30 flex items-center justify-between px-8 py-0 h-16" style="background:white;border-bottom:1px solid #EEEBF0;">
            <div class="flex items-center gap-2 text-sm" style="color:var(--gray);">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-rose-500 transition-colors font-medium" style="color:var(--gray);">FSL</a>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span style="color:var(--dark);" class="font-semibold">@yield('page-title', 'Dashboard')</span>
                @hasSection('page-subtitle')
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-xs" style="color:var(--gray);">@yield('page-subtitle')</span>
                @endif
            </div>
            <div class="flex items-center gap-3">
                @yield('header-actions')
            </div>
        </header>

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="mx-8 mt-4">
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm" style="background:#D1FAE5;color:#065F46;border:1px solid #A7F3D0;">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        </div>
        @endif
        @if(session('error'))
        <div class="mx-8 mt-4">
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm" style="background:#FEF2F2;color:#991B1B;border:1px solid #FECACA;">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('error') }}
            </div>
        </div>
        @endif

        {{-- Page content --}}
        <div class="flex-1 px-8 py-7">
            @yield('content')
        </div>

        {{-- Footer --}}
        <footer class="px-8 py-4 text-xs" style="color:#B0A8B5;border-top:1px solid #EEEBF0;">
            © {{ date('Y') }} Femmes Sans Limites — Back Office v1.0
        </footer>
    </div>
</div>

<style>
.admin-nav-link {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 9px 12px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 500;
    color: rgba(255,255,255,0.55);
    transition: all 0.15s;
    font-family: 'DM Sans', sans-serif;
    text-decoration: none;
}
.admin-nav-link:hover {
    background: rgba(255,255,255,0.08);
    color: rgba(255,255,255,0.9);
}
.admin-nav-link.active {
    background: rgba(217,30,110,0.22);
    color: #F9A8CF;
}
.admin-nav-link.active svg {
    color: #F9A8CF;
}
</style>

@stack('scripts')

<script>
// Auto-scroll to first validation error
document.addEventListener('DOMContentLoaded', () => {
    const firstError = document.querySelector('.text-red-500, .border-red-400');
    if (firstError) {
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Loading spinner on form submit buttons
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function () {
            const btn = this.querySelector('button[type="submit"]');
            if (btn && !btn.dataset.noSpinner) {
                const orig = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = `<svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg> Chargement…`;
                setTimeout(() => { btn.disabled = false; btn.innerHTML = orig; }, 8000);
            }
        });
    });
});
</script>
</body>
</html>
