@extends('layouts.public')
@section('title', 'Abonnement confirmé — Femme Sans Limites')
@section('robots', 'noindex, nofollow')

@section('content')
<section class="min-h-[60vh] flex items-center justify-center py-24 bg-white">
    <div class="max-w-md mx-auto px-5 text-center scale-up visible">
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6" style="background:var(--rose-pale);">
            <svg class="w-8 h-8" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold mb-3" style="color:var(--dark);font-family:'Playfair Display',serif;">Abonnement confirmé ✨</h1>
        <p class="text-base leading-relaxed mb-8" style="color:var(--gray);">
            Merci {{ $subscriber->name ?? '' }} ! Ton abonnement à la newsletter <strong style="color:var(--rose);">Femme Sans Limites</strong> est désormais actif. Tu recevras nos actualités, événements et ressources.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('home') }}" class="btn-rose">Retour à l'accueil</a>
            <a href="{{ route('events.index') }}" class="btn-outline-rose">Voir nos événements</a>
        </div>
    </div>
</section>
@endsection
