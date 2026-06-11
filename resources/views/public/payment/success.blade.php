@extends('layouts.public')
@section('title', 'Paiement réussi — Femme Sans Limites')
@section('robots', 'noindex, nofollow')

@section('content')
@php $isEbook = str_contains((string) $type, 'Ebook'); @endphp
<section class="min-h-[60vh] flex items-center justify-center py-24 px-5 bg-white">
    <div class="max-w-md mx-auto text-center scale-up visible">
        @if($payment->isPaid())
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6" style="background:var(--rose-pale);">
            <svg class="w-8 h-8" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h1 class="text-3xl font-bold mb-3" style="color:var(--dark);font-family:'Playfair Display',serif;">Paiement réussi ✨</h1>
        <p class="text-base leading-relaxed mb-8" style="color:var(--gray);">
            Merci {{ $payment->customer_name }} ! Ton paiement a bien été confirmé.
            @if($isEbook)
                Ton ebook vient de t'être envoyé par email à <strong style="color:var(--charcoal);">{{ $payment->customer_email }}</strong>.
            @else
                Ton QR code d'accès t'a été envoyé par email à <strong style="color:var(--charcoal);">{{ $payment->customer_email }}</strong>.
            @endif
        </p>
        @else
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6" style="background:var(--gold-pale);">
            <svg class="w-8 h-8 animate-spin" style="color:var(--gold-dark);" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
        </div>
        <h1 class="text-3xl font-bold mb-3" style="color:var(--dark);font-family:'Playfair Display',serif;">Paiement en cours de confirmation</h1>
        <p class="text-base leading-relaxed mb-8" style="color:var(--gray);">
            Ton paiement est en cours de validation. Tu recevras un email dès qu'il sera confirmé (généralement en quelques instants).
        </p>
        @endif
        <a href="{{ route('home') }}" class="btn-rose">Retour à l'accueil</a>
    </div>
</section>
@endsection
