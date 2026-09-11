@extends('layouts.admin')
@section('title','Tarifs d’adhésion')
@section('page-title','Tarifs d’adhésion')
@section('page-subtitle','Ce que paient les membres pour chaque niveau')

@section('content')

<div class="mb-6 p-4 rounded-xl text-sm" style="background:var(--rose-pale);border:1px solid var(--rose-mid);color:var(--dark);">
    Ces tarifs s’appliquent au renouvellement et au changement de niveau depuis l’espace membre.
    Un tarif <strong>vide</strong> rend le niveau gratuit : il reste proposé, mais sans paiement.
    {{-- Les niveaux sont inscrits sur les cartes et servent au ciblage des campagnes :
         ils se règlent et se désactivent, mais ne se créent ni ne se suppriment ici. --}}
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    @foreach($plans as $plan)
    <div class="admin-card flex flex-col {{ $plan->is_active ? '' : 'opacity-60' }}">
        <div class="flex items-start justify-between gap-2 mb-3">
            <div>
                <p class="font-bold" style="color:var(--dark);font-family:'Playfair Display',serif;">{{ $plan->name }}</p>
                <p class="text-xs" style="color:var(--gray);">{{ $plan->type }}</p>
            </div>
            @if($plan->is_active)
            <span class="text-[10px] px-2 py-0.5 rounded-full font-medium" style="background:#05966918;color:#059669;">Proposé</span>
            @else
            <span class="text-[10px] px-2 py-0.5 rounded-full font-medium bg-gray-100 text-gray-400">Masqué</span>
            @endif
        </div>

        <p class="mb-1">
            @if($plan->isPaid())
            <span class="text-2xl font-bold" style="color:var(--dark);font-family:'Playfair Display',serif;">
                {{ number_format((float) $plan->price, 0, ',', ' ') }}
            </span>
            <span class="text-sm font-semibold" style="color:var(--gray);">{{ $plan->currency }}</span>
            @else
            <span class="text-2xl font-bold" style="color:var(--gray);font-family:'Playfair Display',serif;">Gratuit</span>
            @endif
        </p>
        <p class="text-xs mb-4" style="color:var(--gray);">pour {{ $plan->duration_months }} mois</p>

        @if($plan->description)
        <p class="text-sm leading-relaxed flex-1 mb-4" style="color:var(--gray);">{{ $plan->description }}</p>
        @else
        <div class="flex-1"></div>
        @endif

        <a href="{{ route('admin.membership-plans.edit', $plan) }}"
           class="text-xs px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-center">Modifier</a>
    </div>
    @endforeach
</div>

@if($plans->isEmpty())
<div class="admin-card text-center py-12">
    <p class="font-semibold" style="color:var(--dark);">Aucun tarif enregistré</p>
    <p class="text-sm mt-1" style="color:var(--gray);">
        Lancez <code>php artisan db:seed --class=MembershipPlanSeeder</code> pour créer les trois niveaux.
    </p>
</div>
@endif

@endsection
