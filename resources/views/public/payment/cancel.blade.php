@extends('layouts.public')
@section('title', 'Paiement non abouti — Femme Sans Limites')
@section('robots', 'noindex, nofollow')

@section('content')
<section class="min-h-[60vh] flex items-center justify-center py-24 px-5 bg-white">
    <div class="max-w-md mx-auto text-center">
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6" style="background:#FEF2F2;">
            <svg class="w-8 h-8" style="color:#DC2626;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h1 class="text-3xl font-bold mb-3" style="color:var(--dark);font-family:'Playfair Display',serif;">Paiement non abouti</h1>
        <p class="text-base leading-relaxed mb-8" style="color:var(--gray);">
            Ton paiement n'a pas été finalisé. Aucun montant n'a été débité. Tu peux réessayer quand tu veux.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            @if($payment->checkout_url && in_array($payment->status, ['pending','cancelled']))
            <a href="{{ $payment->checkout_url }}" class="btn-rose">Réessayer le paiement</a>
            @endif
            <a href="{{ route('home') }}" class="btn-outline-rose">Retour à l'accueil</a>
        </div>
    </div>
</section>
@endsection
