<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — FSL Back Office</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased" style="background:#F8F7F9;">

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 flex flex-col transition-transform duration-300" style="background:var(--dark);">
        <!-- Logo -->
        <div class="flex items-center justify-center py-6 px-4 border-b" style="border-color:rgba(255,255,255,0.08);">
            <img src="{{ asset('logo_FSL.png') }}" alt="FSL" class="h-12 w-auto" style="filter:brightness(0) invert(1) opacity(0.9);">
        </div>

        <!-- Nav -->
        <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Tableau de bord
            </a>

            <div class="pt-4 pb-2">
                <p class="text-xs font-semibold uppercase tracking-wider px-3" style="color:rgba(255,255,255,0.3);">Membres</p>
            </div>
            <a href="{{ route('admin.members.index') }}" class="admin-nav-link {{ request()->routeIs('admin.members.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Tous les membres
            </a>
            <a href="{{ route('admin.members.create') }}" class="admin-nav-link {{ request()->routeIs('admin.members.create') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nouveau membre
            </a>

            <div class="pt-4 pb-2">
                <p class="text-xs font-semibold uppercase tracking-wider px-3" style="color:rgba(255,255,255,0.3);">Événements</p>
            </div>
            <a href="{{ route('admin.events.index') }}" class="admin-nav-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Tous les événements
            </a>
            <a href="{{ route('admin.events.create') }}" class="admin-nav-link">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Créer un événement
            </a>
        </nav>

        <!-- User -->
        <div class="p-4 border-t" style="border-color:rgba(255,255,255,0.08);">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-bold" style="background:var(--rose);">
                    {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-xs font-medium truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                    <p class="text-xs truncate" style="color:rgba(255,255,255,0.4);">Administrateur</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="p-1.5 rounded-lg transition-colors hover:bg-white/10" title="Déconnexion">
                        <svg class="w-4 h-4" style="color:rgba(255,255,255,0.5);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 ml-64">
        <!-- Top bar -->
        <header class="sticky top-0 z-30 bg-white border-b border-gray-100 px-8 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-lg font-semibold" style="font-family:'DM Sans',sans-serif; color:var(--dark);">@yield('page-title', 'Dashboard')</h1>
                <p class="text-xs text-gray-400 mt-0.5">@yield('page-subtitle', '')</p>
            </div>
            <div class="flex items-center gap-3">
                @yield('header-actions')
            </div>
        </header>

        <!-- Flash messages -->
        <div class="px-8 pt-4">
            @if(session('success'))
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm mb-4" style="background:#D1FAE5; color:#065F46;">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm mb-4" style="background:#FEE2E2; color:#991B1B;">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('error') }}
            </div>
            @endif
        </div>

        <!-- Page content -->
        <div class="px-8 py-6">
            @yield('content')
        </div>
    </div>
</div>

<style>
.admin-nav-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 500;
    color: rgba(255,255,255,0.65);
    transition: all 0.2s;
    font-family: 'DM Sans', sans-serif;
}
.admin-nav-link:hover, .admin-nav-link.active {
    background: rgba(255,255,255,0.1);
    color: white;
}
.admin-nav-link.active {
    background: rgba(217, 30, 110, 0.25);
    color: #F9A8CF;
}
</style>

@stack('scripts')
</body>
</html>
