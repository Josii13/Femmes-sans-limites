@extends('layouts.admin')
@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@section('header-actions')
<a href="{{ route('admin.members.create') }}" class="btn-rose text-sm px-4 py-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Nouveau membre
</a>
<a href="{{ route('admin.events.create') }}" class="btn-outline text-sm px-4 py-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Nouvel événement
</a>
@endsection

@section('content')

{{-- ══ Bienvenue ══ --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-7">
    <div>
        <h2 class="text-2xl font-bold leading-tight" style="color:var(--dark);font-family:'Playfair Display',serif;">
            Bonjour, {{ auth()->user()->name ?? 'Admin' }} 👋
        </h2>
        <p class="text-sm mt-1" style="color:var(--gray);">{{ now()->isoFormat('dddd D MMMM YYYY') }} · Vue d'ensemble de la plateforme</p>
    </div>
    @if($stats['pending'] > 0)
    <a href="{{ route('admin.members.index', ['status' => 'inactive']) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all" style="background:rgba(217,30,110,0.08);color:var(--rose);border:1px solid rgba(217,30,110,0.18);" onmouseover="this.style.background='rgba(217,30,110,0.14)'" onmouseout="this.style.background='rgba(217,30,110,0.08)'">
        <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:var(--rose);animation:pulse 2s infinite;"></span>
        {{ $stats['pending'] }} candidature{{ $stats['pending'] > 1 ? 's' : '' }} en attente
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </a>
    @endif
</div>

{{-- ══ KPIs ══ --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    {{-- Membres --}}
    <div class="bg-white rounded-2xl p-5" style="border:1px solid #EEEBF0;box-shadow:0 1px 6px rgba(0,0,0,0.04);">
        <div class="flex items-start justify-between mb-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(217,30,110,0.08);">
                <svg class="w-5 h-5" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <a href="{{ route('admin.members.index') }}" class="text-xs font-medium" style="color:var(--rose);">Voir →</a>
        </div>
        <p class="text-3xl font-bold mb-1" style="color:var(--dark);font-family:'Playfair Display',serif;">{{ $stats['members'] }}</p>
        <p class="text-xs font-medium" style="color:var(--gray);">Membres total</p>
        <div class="flex items-center gap-1.5 mt-3 pt-3" style="border-top:1px solid #F3F0F5;">
            <span class="text-xs font-medium" style="color:#059669;">{{ $stats['active'] }} actives</span>
            <span class="text-xs" style="color:var(--gray-light);">·</span>
            <span class="text-xs" style="color:var(--gray);">{{ $stats['pending'] }} en attente</span>
        </div>
    </div>

    {{-- Événements --}}
    <div class="bg-white rounded-2xl p-5" style="border:1px solid #EEEBF0;box-shadow:0 1px 6px rgba(0,0,0,0.04);">
        <div class="flex items-start justify-between mb-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(201,168,76,0.1);">
                <svg class="w-5 h-5" style="color:var(--gold);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <a href="{{ route('admin.events.index') }}" class="text-xs font-medium" style="color:var(--gold);">Voir →</a>
        </div>
        <p class="text-3xl font-bold mb-1" style="color:var(--dark);font-family:'Playfair Display',serif;">{{ $stats['events'] }}</p>
        <p class="text-xs font-medium" style="color:var(--gray);">Événements</p>
        <div class="flex items-center gap-1.5 mt-3 pt-3" style="border-top:1px solid #F3F0F5;">
            <span class="text-xs font-medium" style="color:#059669;">{{ $stats['published'] }} publiés</span>
            <span class="text-xs" style="color:var(--gray-light);">·</span>
            <span class="text-xs" style="color:var(--gray);">{{ $upcoming->count() }} à venir</span>
        </div>
    </div>

    {{-- Inscriptions --}}
    <div class="bg-white rounded-2xl p-5" style="border:1px solid #EEEBF0;box-shadow:0 1px 6px rgba(0,0,0,0.04);">
        <div class="flex items-start justify-between mb-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(124,58,237,0.08);">
                <svg class="w-5 h-5" style="color:#7C3AED;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
        </div>
        <p class="text-3xl font-bold mb-1" style="color:var(--dark);font-family:'Playfair Display',serif;">{{ $stats['registrations'] }}</p>
        <p class="text-xs font-medium" style="color:var(--gray);">Inscriptions</p>
        <div class="flex items-center gap-1.5 mt-3 pt-3" style="border-top:1px solid #F3F0F5;">
            <span class="text-xs font-medium" style="color:#059669;">{{ $stats['paid'] }} confirmées</span>
            <span class="text-xs" style="color:var(--gray-light);">·</span>
            <span class="text-xs" style="color:var(--gray);">{{ $stats['registrations'] - $stats['paid'] }} en attente</span>
        </div>
    </div>

    {{-- Répartition types --}}
    <div class="bg-white rounded-2xl p-5" style="border:1px solid #EEEBF0;box-shadow:0 1px 6px rgba(0,0,0,0.04);">
        <div class="flex items-start justify-between mb-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(15,10,12,0.06);">
                <svg class="w-5 h-5" style="color:var(--dark);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
            </div>
        </div>
        <p class="text-xs font-medium mb-3" style="color:var(--gray);">Répartition des membres</p>
        @php $total = max($stats['members'], 1); @endphp
        <div class="space-y-2.5">
            @foreach([['Standard','standard','#6B7280',$stats['standard']],['Gold','gold','#C9A84C',$stats['gold']],['Premium','premium','#D91E6E',$stats['premium']]] as [$label,$type,$color,$count])
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium" style="color:{{ $color }};">{{ $label }}</span>
                    <span class="text-xs font-bold" style="color:var(--dark);">{{ $count }}</span>
                </div>
                <div class="h-1.5 rounded-full" style="background:#F3F0F5;">
                    <div class="h-1.5 rounded-full transition-all" style="width:{{ $total > 0 ? round($count/$total*100) : 0 }}%;background:{{ $color }};"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ══ Alerte candidatures ══ --}}
@if($pendingMembers->isNotEmpty())
<div class="bg-white rounded-2xl p-5 mb-6" style="border:1px solid rgba(217,30,110,0.2);box-shadow:0 1px 6px rgba(217,30,110,0.06);">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background:rgba(217,30,110,0.1);">
                <svg class="w-4 h-4" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h3 class="text-sm font-bold" style="color:var(--dark);">Candidatures en attente de validation</h3>
                <p class="text-xs" style="color:var(--gray);">{{ $stats['pending'] }} membre{{ $stats['pending'] > 1 ? 's' : '' }} à activer</p>
            </div>
        </div>
        <a href="{{ route('admin.members.index', ['status' => 'inactive']) }}" class="text-xs font-semibold" style="color:var(--rose);">Voir toutes →</a>
    </div>
    <div class="flex flex-wrap gap-2">
        @foreach($pendingMembers as $pm)
        <div class="flex items-center gap-2.5 px-3 py-2 rounded-xl" style="background:#FAFAFA;border:1px solid #EEEBF0;">
            <div class="w-7 h-7 rounded-full overflow-hidden flex-shrink-0 flex items-center justify-center text-white text-xs font-bold" style="background:var(--rose);">
                @if($pm->photo)
                <img src="{{ asset('storage/'.$pm->photo) }}" alt="" class="w-full h-full object-cover">
                @else
                {{ strtoupper(substr($pm->name,0,1)) }}
                @endif
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold truncate" style="color:var(--dark);">{{ $pm->name }}</p>
                <p class="text-[10px]" style="color:var(--gray);">{{ $pm->city }}</p>
            </div>
            <form action="{{ route('admin.members.activate', $pm) }}" method="POST">
                @csrf
                <button type="submit" class="text-[11px] font-semibold px-2.5 py-1 rounded-lg transition-all" style="background:rgba(5,150,105,0.1);color:#059669;" onmouseover="this.style.background='rgba(5,150,105,0.2)'" onmouseout="this.style.background='rgba(5,150,105,0.1)'">Activer</button>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ══ Listes ══ --}}
<div class="grid grid-cols-1 xl:grid-cols-2 gap-5 mb-6">

    {{-- Prochains événements --}}
    <div class="bg-white rounded-2xl" style="border:1px solid #EEEBF0;box-shadow:0 1px 6px rgba(0,0,0,0.04);">
        <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #F3F0F5;">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:rgba(201,168,76,0.12);">
                    <svg class="w-3.5 h-3.5" style="color:var(--gold);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-sm font-bold" style="color:var(--dark);">Prochains événements</h3>
            </div>
            <a href="{{ route('admin.events.index') }}" class="text-xs font-medium" style="color:var(--rose);">Tout voir →</a>
        </div>
        <div class="divide-y" style="--tw-divide-opacity:1;border-color:#F8F6FA;">
            @forelse($upcoming as $ev)
            <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50/60 transition-colors">
                {{-- Date chip --}}
                <div class="flex-shrink-0 w-11 text-center rounded-xl py-1.5" style="background:var(--warm);border:1px solid #EEEBF0;">
                    <p class="text-[10px] font-bold uppercase" style="color:var(--rose);">{{ $ev->event_date->isoFormat('MMM') }}</p>
                    <p class="text-base font-bold leading-tight" style="color:var(--dark);">{{ $ev->event_date->format('d') }}</p>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold truncate" style="color:var(--dark);">{{ $ev->title }}</p>
                    <p class="text-xs mt-0.5" style="color:var(--gray);">{{ $ev->city ?? $ev->location }}</p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full" style="background:rgba(217,30,110,0.08);color:var(--rose);">{{ $ev->registrations_count }} inscrits</span>
                    <a href="{{ route('admin.registrations.index', $ev) }}" class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors" title="Voir les inscriptions">
                        <svg class="w-4 h-4" style="color:var(--gray);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
            @empty
            <div class="px-5 py-10 text-center">
                <div class="w-10 h-10 rounded-xl mx-auto mb-3 flex items-center justify-center" style="background:#F3F0F5;">
                    <svg class="w-5 h-5" style="color:#C4BEC8;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <p class="text-sm" style="color:var(--gray);">Aucun événement à venir</p>
                <a href="{{ route('admin.events.create') }}" class="text-xs font-medium mt-1 inline-block" style="color:var(--rose);">Créer un événement →</a>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Membres récents --}}
    <div class="bg-white rounded-2xl" style="border:1px solid #EEEBF0;box-shadow:0 1px 6px rgba(0,0,0,0.04);">
        <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid #F3F0F5;">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:rgba(217,30,110,0.1);">
                    <svg class="w-3.5 h-3.5" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="text-sm font-bold" style="color:var(--dark);">Membres récents</h3>
            </div>
            <a href="{{ route('admin.members.index') }}" class="text-xs font-medium" style="color:var(--rose);">Tout voir →</a>
        </div>
        <div class="divide-y" style="--tw-divide-opacity:1;border-color:#F8F6FA;">
            @forelse($recentMembers as $member)
            <div class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50/60 transition-colors">
                <div class="w-9 h-9 rounded-full overflow-hidden flex-shrink-0 flex items-center justify-center font-bold text-white text-sm" style="background:{{ $member->badge_color }};">
                    @if($member->photo)
                    <img src="{{ asset('storage/'.$member->photo) }}" alt="" class="w-full h-full object-cover">
                    @else
                    {{ strtoupper(substr($member->name,0,1)) }}
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold truncate" style="color:var(--dark);">{{ $member->name }}</p>
                    <p class="text-xs truncate" style="color:var(--gray);">{{ $member->profession }} · {{ $member->city }}</p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    @if($member->status === 'active')
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full" style="background:rgba(5,150,105,0.1);color:#059669;">Active</span>
                    @else
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full" style="background:rgba(245,158,11,0.1);color:#D97706;">En attente</span>
                    @endif
                    <span class="badge-{{ $member->type }} !text-[10px] !px-2 !py-0.5">{{ ucfirst($member->type) }}</span>
                    <a href="{{ route('admin.members.show', $member) }}" class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors" title="Voir le profil">
                        <svg class="w-4 h-4" style="color:var(--gray);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
            @empty
            <div class="px-5 py-10 text-center">
                <p class="text-sm" style="color:var(--gray);">Aucun membre enregistré</p>
                <a href="{{ route('admin.members.create') }}" class="text-xs font-medium mt-1 inline-block" style="color:var(--rose);">Ajouter un membre →</a>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ══ Actions rapides ══ --}}
<div class="bg-white rounded-2xl p-5" style="border:1px solid #EEEBF0;box-shadow:0 1px 6px rgba(0,0,0,0.04);">
    <h3 class="text-sm font-bold mb-4" style="color:var(--dark);">Actions rapides</h3>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @foreach([
            [route('admin.members.create'), 'var(--rose)', 'rgba(217,30,110,0.08)', 'Ajouter un membre', 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'],
            [route('admin.events.create'), 'var(--gold)', 'rgba(201,168,76,0.1)', 'Créer un événement', 'M12 4v16m8-8H4'],
            [route('admin.members.index', ['status'=>'inactive']), '#7C3AED', 'rgba(124,58,237,0.08)', 'Candidatures en attente', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
            [route('admin.members.index'), 'var(--dark)', 'rgba(15,10,12,0.06)', 'Voir tous les membres', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
        ] as [$href, $color, $bg, $label, $icon])
        <a href="{{ $href }}" class="flex flex-col items-start gap-3 p-4 rounded-xl transition-all hover:shadow-sm" style="background:{{ $bg }};border:1px solid transparent;" onmouseover="this.style.borderColor='{{ $color }}33'" onmouseout="this.style.borderColor='transparent'">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:{{ $color }}22;">
                <svg class="w-4.5 h-4.5 w-[18px] h-[18px]" style="color:{{ $color }};" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
            </div>
            <span class="text-xs font-semibold leading-tight" style="color:var(--dark);">{{ $label }}</span>
        </a>
        @endforeach
    </div>
</div>

@endsection

@push('scripts')
<style>
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }
</style>
@endpush
