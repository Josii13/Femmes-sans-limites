@extends('layouts.public')
@section('title', 'Désinscription effectuée — Femme Sans Limites')
@section('robots', 'noindex, nofollow')

@section('content')
<section class="min-h-[60vh] flex items-center justify-center py-24 bg-white">
    <div class="max-w-md mx-auto px-5 text-center">
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6" style="background:var(--rose-pale);">
            <svg class="w-8 h-8" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold mb-3" style="color:var(--dark);font-family:'Playfair Display',serif;">C'est fait</h1>
        <p class="text-base leading-relaxed mb-8" style="color:var(--gray);">
            Tu ne recevras plus nos emails de communication. Tu restes membre de Femme Sans Limites et tu peux nous contacter à tout moment si tu changes d'avis.
        </p>
        <a href="{{ url('/') }}" class="btn-rose">Retour à l'accueil</a>
    </div>
</section>
@endsection
