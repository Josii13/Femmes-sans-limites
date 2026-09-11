@extends('layouts.member')
@section('title', 'Tableau de bord')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl lg:text-3xl font-bold" style="color:var(--dark);font-family:'Playfair Display',serif;">
        Bonjour {{ explode(' ', $member->name)[0] }}
    </h1>
    <p class="text-sm mt-1" style="color:var(--gray);">
        Membre {{ ucfirst($member->type) }} · {{ $member->member_number }}
    </p>
</div>

{{-- Échéance de l'adhésion : l'information la plus utile, donc la plus visible. --}}
@if($member->expires_at)
@php
    $daysLeft = (int) now()->diffInDays($member->expires_at, absolute: false);
    $urgent = $daysLeft <= 30;
@endphp
<div class="rounded-2xl p-5 mb-6 flex flex-wrap items-center justify-between gap-4"
     style="background:{{ $urgent ? '#FFFBEB' : 'var(--rose-pale)' }};border:1px solid {{ $urgent ? '#FCD34D' : 'var(--rose-mid)' }};">
    <div>
        <p class="text-sm font-semibold" style="color:var(--dark);">
            @if($daysLeft > 0)
                Adhésion valable jusqu’au {{ $member->expires_at->translatedFormat('d F Y') }}
            @else
                Votre adhésion est arrivée à échéance
            @endif
        </p>
        <p class="text-xs mt-0.5" style="color:var(--gray);">
            @if($daysLeft > 30)
                Il vous reste {{ $daysLeft }} jours.
            @elseif($daysLeft > 0)
                Plus que {{ $daysLeft }} jour{{ $daysLeft > 1 ? 's' : '' }} — pensez à renouveler.
            @else
                Renouvelez pour continuer à profiter de vos avantages.
            @endif
        </p>
    </div>
    @if($urgent)
    <a href="{{ route('member.renewal') }}" class="btn-rose text-sm px-6 py-2.5">Renouveler mon adhésion</a>
    @endif
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Carte de membre --}}
    <div class="lg:col-span-2 card p-6">
        <h2 class="font-bold mb-4" style="color:var(--dark);font-family:'Playfair Display',serif;">Ma carte de membre</h2>

        @if($member->card_path)
        <img src="{{ asset('storage/'.$member->card_path) }}" alt="Carte de membre {{ $member->member_number }}"
             class="w-full max-w-md rounded-xl shadow-lg mb-4">
        <a href="{{ route('member.card') }}" class="btn-rose text-sm inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Télécharger ma carte
        </a>
        @else
        <p class="text-sm" style="color:var(--gray);">
            Votre carte est en cours de génération. Elle apparaîtra ici sous peu.
        </p>
        @endif
    </div>

    {{-- Raccourcis --}}
    <div class="space-y-4">
        <a href="{{ route('member.registrations') }}" class="card-hover p-5 block">
            <p class="text-2xl font-bold" style="color:var(--rose);font-family:'Playfair Display',serif;">{{ $upcomingRegistrations->count() }}</p>
            <p class="text-sm font-semibold mt-1" style="color:var(--dark);">Événement{{ $upcomingRegistrations->count() > 1 ? 's' : '' }} à venir</p>
            <p class="text-xs mt-0.5" style="color:var(--gray);">Voir toutes mes inscriptions →</p>
        </a>

        <a href="{{ route('member.purchases') }}" class="card-hover p-5 block">
            <p class="text-2xl font-bold" style="color:var(--gold);font-family:'Playfair Display',serif;">{{ $purchaseCount }}</p>
            <p class="text-sm font-semibold mt-1" style="color:var(--dark);">Ebook{{ $purchaseCount > 1 ? 's' : '' }} acheté{{ $purchaseCount > 1 ? 's' : '' }}</p>
            <p class="text-xs mt-0.5" style="color:var(--gray);">Retélécharger →</p>
        </a>

        <a href="{{ route('member.directory') }}" class="card-hover p-5 block">
            <p class="text-sm font-semibold" style="color:var(--dark);">Annuaire de la communauté</p>
            <p class="text-xs mt-0.5" style="color:var(--gray);">Découvrir les autres membres →</p>
        </a>
    </div>
</div>

{{-- Mes avantages --}}
<div class="card p-6 mt-6">
    <h2 class="font-bold mb-4" style="color:var(--dark);font-family:'Playfair Display',serif;">
        Mes avantages {{ ucfirst($member->type) }}
    </h2>
    <ul class="space-y-2">
        @foreach($member->privileges_list as $privilege)
        <li class="flex items-start gap-2.5 text-sm" style="color:var(--gray);">
            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            {{ $privilege }}
        </li>
        @endforeach
    </ul>

    @if($member->type !== 'premium')
    <a href="{{ route('member.renewal') }}" class="inline-flex items-center gap-2 mt-5 text-sm font-semibold" style="color:var(--rose);">
        Passer au niveau supérieur
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
    </a>
    @endif
</div>

{{-- Prochains événements --}}
@if($nextEvents->isNotEmpty())
<div class="mt-8">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-bold" style="color:var(--dark);font-family:'Playfair Display',serif;">Prochains rendez-vous</h2>
        <a href="{{ route('events.index') }}" class="text-sm" style="color:var(--rose);">Tout voir →</a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @foreach($nextEvents as $event)
        <a href="{{ route('events.show', $event->slug) }}" class="card-hover p-4 block">
            <p class="text-[11px] font-semibold uppercase tracking-wider" style="color:var(--rose);">
                {{ $event->event_date->translatedFormat('d F') }}
            </p>
            <p class="text-sm font-bold mt-1 line-clamp-2" style="color:var(--dark);">{{ $event->title }}</p>
            @if($event->city)
            <p class="text-xs mt-1" style="color:var(--gray);">{{ $event->city }}</p>
            @endif
        </a>
        @endforeach
    </div>
</div>
@endif

@endsection
