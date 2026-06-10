<x-mail::message>
<div style="text-align:center;margin-bottom:24px;">
<img src="{{ asset('logo_FSL.png') }}" alt="FSL" style="height:48px;width:auto;">
</div>

@if($expired)
# Ton adhésion a expiré

Bonjour {{ $member->name }},

Ton adhésion à **Femme Sans Limites** est arrivée à échéance le {{ $member->expires_at?->translatedFormat('d F Y') }}.

Nous serions ravies de te compter à nouveau parmi nos membres. Pour renouveler ton adhésion, il te suffit de nous contacter — notre équipe s'occupe du reste.
@else
# Ton adhésion arrive à échéance

Bonjour {{ $member->name }},

Ton adhésion à **Femme Sans Limites** expire le {{ $member->expires_at?->translatedFormat('d F Y') }}.

Pour continuer à profiter de tous tes avantages de membre {{ ucfirst($member->type) }}, pense à la renouveler. Notre équipe est à ta disposition.
@endif

<x-mail::button :url="route('contact')" color="primary">
Nous contacter →
</x-mail::button>

Avec amour,
**L'équipe Femme Sans Limites**
</x-mail::message>
