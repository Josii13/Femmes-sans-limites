@extends('layouts.public')
@section('title', 'Désabonnement — Femme Sans Limites')

@section('content')
<section class="min-h-[60vh] flex items-center justify-center py-24 bg-white">
    <div class="max-w-md mx-auto px-5 text-center">
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6" style="background:var(--rose-pale);">
            <svg class="w-8 h-8" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold mb-3" style="color:var(--dark);font-family:'Playfair Display',serif;">Désabonnement effectué</h1>
        <p class="text-base leading-relaxed mb-8" style="color:var(--gray);">
            Tu ne recevras plus nos newsletters. Tu peux te réabonner à tout moment depuis le bas de notre site.
        </p>
        <a href="{{ url('/') }}" class="btn-rose">Retour à l'accueil</a>
    </div>
</section>
@endsection
