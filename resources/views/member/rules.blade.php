@extends('layouts.member')
@section('title', 'Règles de la communauté')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold" style="color:var(--dark);font-family:'Playfair Display',serif;">
        Règles de la communauté
    </h1>
    <p class="text-sm mt-1" style="color:var(--gray);">
        Version {{ \App\Support\CommunityRules::VERSION }} ·
        en vigueur depuis le {{ \App\Support\CommunityRules::effectiveDate()->translatedFormat('d F Y') }}
    </p>
</div>

{{--
    Les membres inscrites avant la mise en place de la charte, ou avant sa dernière
    révision, n'ont rien accepté : on le leur signale plutôt que de supposer leur accord.
--}}
@if($member->hasAcceptedCurrentRules())
<div class="flex items-start gap-3 p-4 rounded-xl mb-6" style="background:#D1FAE5;color:#065F46;">
    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <div>
        <p class="text-sm font-semibold">Vous avez accepté ces règles</p>
        <p class="text-xs mt-0.5">
            Le {{ $member->rules_accepted_at->translatedFormat('d F Y à H:i') }}
            (version {{ $member->rules_version }}).
        </p>
    </div>
</div>
@else
<div class="p-5 rounded-xl mb-6" style="background:#FFFBEB;border:1px solid #FCD34D;">
    <p class="text-sm font-semibold" style="color:#92400E;">
        @if($member->rules_accepted_at)
            Ces règles ont évolué depuis votre acceptation
        @else
            Votre engagement n’a pas encore été enregistré
        @endif
    </p>
    <p class="text-xs mt-1 leading-relaxed" style="color:#92400E;">
        @if($member->rules_accepted_at)
            Vous aviez accepté la version {{ $member->rules_version }} le
            {{ $member->rules_accepted_at->translatedFormat('d F Y') }}.
            Merci de prendre connaissance de la version en vigueur.
        @else
            Vous avez rejoint la communauté avant la mise en place de cette charte.
            Prenez-en connaissance ci-dessous, puis confirmez votre engagement.
        @endif
    </p>
</div>
@endif

<div class="card p-6 lg:p-8">
    <x-community-rules />

    <div class="mt-8 pt-6 rounded-2xl p-5" style="background:var(--rose-pale);border:1px solid var(--rose-mid);">
        <p class="text-xs font-bold uppercase tracking-widest mb-2" style="color:var(--rose);">Notre engagement</p>
        <p class="text-sm leading-relaxed" style="color:var(--dark);">
            En rejoignant Femme Sans Limites, chaque membre reconnaît avoir pris connaissance
            de ces règles et s’engage à les respecter, afin de contribuer à un espace où chaque
            femme peut apprendre, évoluer, s’exprimer et grandir dans un environnement sain.
        </p>
    </div>

    @unless($member->hasAcceptedCurrentRules())
    <form action="{{ route('member.rules.accept') }}" method="POST" class="mt-6">
        @csrf
        <button type="submit" class="btn-rose">
            Je reconnais avoir pris connaissance de ces règles et m’engage à les respecter
        </button>
    </form>
    @endunless
</div>

@endsection
