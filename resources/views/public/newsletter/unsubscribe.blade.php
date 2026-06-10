@extends('layouts.public')
@section('title', 'Se désabonner — Femme Sans Limites')
@section('robots', 'noindex, nofollow')

@section('content')
<section class="min-h-[60vh] flex items-center justify-center py-24 bg-white">
    <div class="max-w-md mx-auto px-5 text-center">
        <div class="card p-8">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6" style="background:var(--warm);">
                <svg class="w-8 h-8" style="color:var(--gray);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold mb-3" style="color:var(--dark);font-family:'Playfair Display',serif;">Confirmer la désinscription</h1>
            <p class="text-sm leading-relaxed mb-7" style="color:var(--gray);">
                Tu es sur le point de te désabonner de la newsletter Femme Sans Limites
                @if($subscriber->email)<br><strong style="color:var(--charcoal);">{{ $subscriber->email }}</strong>@endif.
                Tu ne recevras plus nos actualités. Es-tu sûre&nbsp;?
            </p>
            <form method="POST" action="{{ route('newsletter.unsubscribe.confirm', $subscriber->token) }}" class="flex flex-col sm:flex-row gap-3 justify-center">
                @csrf
                <button type="submit" class="btn-outline-rose">Oui, me désabonner</button>
                <a href="{{ route('home') }}" class="btn-rose">Rester abonnée</a>
            </form>
        </div>
    </div>
</section>
@endsection
