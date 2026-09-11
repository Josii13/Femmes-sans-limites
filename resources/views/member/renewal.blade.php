@extends('layouts.member')
@section('title', 'Mon adhésion')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl lg:text-3xl font-bold" style="color:var(--dark);font-family:'Playfair Display',serif;">
        Renouveler ou faire évoluer mon adhésion
    </h1>
    @if($member->expires_at)
    <p class="text-sm mt-2" style="color:var(--gray);">
        @if($member->isExpired())
            Votre adhésion {{ ucfirst($member->type) }} est arrivée à échéance le
            {{ $member->expires_at->translatedFormat('d F Y') }}.
        @else
            Votre adhésion {{ ucfirst($member->type) }} court jusqu’au
            {{ $member->expires_at->translatedFormat('d F Y') }}.
            Un renouvellement anticipé s’ajoute au temps restant — vous ne perdez aucun jour.
        @endif
    </p>
    @endif
</div>

@if($plans->isEmpty())
<div class="card p-10 text-center">
    <p class="font-semibold" style="color:var(--dark);">Aucune formule disponible</p>
    <p class="text-sm mt-1" style="color:var(--gray);">Écrivez-nous pour renouveler votre adhésion.</p>
</div>
@else

<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    @foreach($plans as $plan)
    @php $isCurrent = $plan->type === $member->type; @endphp

    <div class="card p-6 flex flex-col {{ $isCurrent ? 'ring-2' : '' }}"
         @if($isCurrent) style="--tw-ring-color:var(--rose);" @endif>

        <div class="mb-4">
            <div class="flex items-center justify-between gap-2 mb-2">
                <h2 class="text-lg font-bold" style="color:var(--dark);font-family:'Playfair Display',serif;">{{ $plan->name }}</h2>
                @if($isCurrent)
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full text-white" style="background:var(--rose);">Formule actuelle</span>
                @endif
            </div>

            @if($plan->isPaid())
            <p class="flex items-baseline gap-1.5">
                <span class="text-3xl font-bold" style="color:var(--dark);font-family:'Playfair Display',serif;">
                    {{ number_format((float) $plan->price, 0, ',', ' ') }}
                </span>
                <span class="text-sm font-semibold" style="color:var(--gray);">{{ $plan->currency }}</span>
            </p>
            <p class="text-xs mt-0.5" style="color:var(--gray);">
                pour {{ $plan->duration_months }} mois
            </p>
            @else
            <p class="text-2xl font-bold" style="color:var(--dark);font-family:'Playfair Display',serif;">Gratuit</p>
            @endif

            @if($plan->description)
            <p class="text-sm mt-3 leading-relaxed" style="color:var(--gray);">{{ $plan->description }}</p>
            @endif
        </div>

        <ul class="space-y-2 flex-1 mb-5">
            @foreach($plan->privileges() as $privilege)
            <li class="flex items-start gap-2 text-xs leading-relaxed" style="color:var(--gray);">
                <svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                {{ $privilege }}
            </li>
            @endforeach
        </ul>

        @if($plan->isPaid())
        <form action="{{ route('member.renewal.pay') }}" method="POST">
            @csrf
            <input type="hidden" name="plan" value="{{ $plan->type }}">
            <button type="submit" class="btn-rose w-full text-sm py-3">
                {{ $isCurrent ? 'Renouveler' : 'Choisir '.$plan->name }}
            </button>
        </form>
        @else
        <p class="text-xs text-center py-3" style="color:var(--gray);">
            {{ $isCurrent ? 'Votre formule actuelle' : 'Formule d’entrée, sans cotisation' }}
        </p>
        @endif
    </div>
    @endforeach
</div>

<p class="text-xs text-center mt-6 leading-relaxed" style="color:var(--gray);">
    🔒 Paiement sécurisé — Wave, Orange Money, MTN, Moov ou carte bancaire.<br>
    Votre carte de membre est mise à jour automatiquement après le règlement.
</p>
@endif

@endsection
