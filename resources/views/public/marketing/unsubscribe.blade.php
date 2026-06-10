@extends('layouts.public')
@section('title', 'Gérer mes communications — Femme Sans Limites')
@section('robots', 'noindex, nofollow')

@section('content')
<section class="min-h-[60vh] flex items-center justify-center py-24 bg-white">
    <div class="max-w-md mx-auto px-5 text-center">
        <div class="card p-8">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6" style="background:var(--warm);">
                <svg class="w-8 h-8" style="color:var(--gray);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold mb-3" style="color:var(--dark);font-family:'Playfair Display',serif;">Ne plus recevoir nos emails</h1>
            <p class="text-sm leading-relaxed mb-7" style="color:var(--gray);">
                @if($member)Bonjour {{ $member->name }},<br>@endif
                Tu peux choisir de ne plus recevoir les campagnes et communications de Femme Sans Limites. Tu resteras membre de la communauté — seuls les emails marketing seront stoppés.
            </p>
            <form method="POST" action="{{ route('marketing.unsubscribe.confirm', $recipient->token) }}" class="flex flex-col sm:flex-row gap-3 justify-center">
                @csrf
                <button type="submit" class="btn-outline-rose">Me désinscrire</button>
                <a href="{{ route('home') }}" class="btn-rose">Continuer à recevoir</a>
            </form>
        </div>
    </div>
</section>
@endsection
