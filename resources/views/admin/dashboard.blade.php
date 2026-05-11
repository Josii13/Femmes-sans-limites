@extends('layouts.admin')
@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')
@section('page-subtitle', 'Vue d\'ensemble de la plateforme')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    @foreach([
        ['Membres Total', $stats['members'], 'var(--rose)', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
        ['Événements', $stats['events'], 'var(--gold)', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ['Inscriptions', $stats['registrations'], '#7C3AED', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
        ['Paiements confirmés', $stats['paid'], '#059669', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
    ] as [$label, $value, $color, $icon])
    <div class="admin-card">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 mb-1">{{ $label }}</p>
                <p class="text-3xl font-bold" style="color:{{ $color }}; font-family:'Playfair Display',serif;">{{ $value }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:{{ $color }}18;">
                <svg class="w-5 h-5" style="color:{{ $color }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                </svg>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Members type breakdown --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-8">
    @foreach([
        ['Standard', $stats['standard'], 'badge-standard', '#6B7280'],
        ['Gold', $stats['gold'], 'badge-gold', 'var(--gold)'],
        ['Premium', $stats['premium'], 'badge-premium', 'var(--rose)'],
    ] as [$type, $count, $badge, $color])
    <div class="admin-card flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="{{ $badge }}">{{ $type }}</span>
        </div>
        <span class="text-2xl font-bold" style="color:{{ $color }}; font-family:'Playfair Display',serif;">{{ $count }}</span>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Upcoming events --}}
    <div class="admin-card">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-semibold text-sm" style="color:var(--dark);">Prochains événements</h3>
            <a href="{{ route('admin.events.index') }}" class="text-xs" style="color:var(--rose);">Voir tout →</a>
        </div>
        <div class="space-y-3">
            @forelse($upcoming as $ev)
            <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate" style="color:var(--dark);">{{ $ev->title }}</p>
                    <p class="text-xs mt-0.5" style="color:var(--gray);">{{ $ev->event_date->format('d M Y') }} — {{ $ev->city ?? $ev->location }}</p>
                </div>
                <div class="flex items-center gap-3 ml-4">
                    <span class="text-xs font-medium px-2 py-1 rounded-full" style="background:rgba(217,30,110,0.08);color:var(--rose);">{{ $ev->registrations_count }} inscrits</span>
                    <a href="{{ route('admin.registrations.index', $ev) }}" class="text-xs underline" style="color:var(--gray);">voir</a>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400 py-4 text-center">Aucun événement à venir</p>
            @endforelse
        </div>
    </div>

    {{-- Recent members --}}
    <div class="admin-card">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-semibold text-sm" style="color:var(--dark);">Membres récents</h3>
            <a href="{{ route('admin.members.index') }}" class="text-xs" style="color:var(--rose);">Voir tout →</a>
        </div>
        <div class="space-y-3">
            @forelse($recentMembers as $member)
            <div class="flex items-center gap-3 py-2 border-b border-gray-50 last:border-0">
                <div class="w-8 h-8 rounded-full overflow-hidden flex-shrink-0 flex items-center justify-center font-bold text-white text-sm" style="background:var(--rose);">
                    @if($member->photo)
                    <img src="{{ asset('storage/'.$member->photo) }}" alt="" class="w-full h-full object-cover">
                    @else
                    {{ substr($member->name, 0, 1) }}
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate" style="color:var(--dark);">{{ $member->name }}</p>
                    <p class="text-xs" style="color:var(--gray);">{{ $member->city }}, {{ $member->country }}</p>
                </div>
                <span class="badge-{{ $member->type }}">{{ ucfirst($member->type) }}</span>
            </div>
            @empty
            <p class="text-sm text-gray-400 py-4 text-center">Aucun membre enregistré</p>
            @endforelse
        </div>
    </div>
</div>

@endsection
