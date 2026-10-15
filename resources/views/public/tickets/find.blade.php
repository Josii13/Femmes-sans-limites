@extends('layouts.public')
@section('title', 'Retrouver mon billet — FSL')
@section('robots', 'noindex, nofollow')

@section('content')
<section class="min-h-[60vh] flex items-center justify-center py-24 px-5" style="background:var(--warm);">
    <div class="max-w-md w-full">
        <div class="card p-8">
            @if(session('sent'))
            <div class="text-center">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:var(--rose-pale);">
                    <svg class="w-7 h-7" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <h1 class="text-xl font-bold mb-2" style="color:var(--dark);font-family:'Playfair Display',serif;">C'est envoyé</h1>
                <p class="text-sm leading-relaxed" style="color:var(--gray);">
                    Si des inscriptions sont associées à cette adresse, un email contenant le lien de ton billet
                    vient de partir. Pense à vérifier tes spams.
                </p>
                <a href="{{ route('events.index') }}" class="btn-rose mt-6">Voir les événements</a>
            </div>
            @else
            <h1 class="text-2xl font-bold mb-2" style="color:var(--dark);font-family:'Playfair Display',serif;">Retrouver mon billet</h1>
            <p class="text-sm mb-6" style="color:var(--gray);">
                Tu es inscrite à un événement mais tu n'as pas reçu ton QR ? Indique l'email utilisé lors de
                l'inscription, on te renvoie le lien de ton billet.
            </p>

            @if($errors->any())
            <div class="px-4 py-3 rounded-xl text-sm mb-4" style="background:#FEF2F2;border:1px solid #FCA5A5;color:#991B1B;">
                <ul class="space-y-0.5 list-none">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            <form method="POST" action="{{ route('tickets.find.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">Email <span style="color:var(--rose);">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-input" placeholder="marie@exemple.com" required autofocus>
                </div>
                <button type="submit" class="btn-rose w-full justify-center py-3.5">Recevoir mon billet</button>
                <x-honeypot />
            </form>
            @endif
        </div>
    </div>
</section>
@endsection
