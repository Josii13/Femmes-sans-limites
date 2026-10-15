@extends('layouts.public')
@section('title', 'Joyeux anniversaire '.explode(' ', $member->name)[0].' — Femme Sans Limites')
{{-- Page personnelle : partagée de la main à la main, jamais indexée. --}}
@section('robots', 'noindex, nofollow')

@section('content')
@php
    $first = explode(' ', trim($member->name))[0] ?: $member->name;
    $card = $member->birthday_card_path ? asset('storage/'.$member->birthday_card_path) : null;
    // Lien de partage WhatsApp : c'est là que vit la communauté.
    $share = 'https://wa.me/?text='.rawurlencode(
        "C'est l'anniversaire de {$first} ! Laisse-lui un mot 🎂 ".route('birthdays.page', $member->birthdayToken())
    );
@endphp

<section class="py-24 px-5" style="background:var(--warm);">
    <div class="max-w-2xl mx-auto">

        @if(session('wished'))
        <div class="flex items-center gap-3 px-5 py-4 rounded-2xl text-sm mb-6" style="background:#D1FAE5;color:#065F46;">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>Ton message est affiché sur le mur. Merci !</span>
        </div>
        @endif

        @if(session('error'))
        <div class="px-5 py-4 rounded-2xl text-sm mb-6" style="background:#FEE2E2;color:#991B1B;">{{ session('error') }}</div>
        @endif

        {{-- La carte --}}
        @if($card)
        <div class="rounded-3xl overflow-hidden mb-6" style="box-shadow:0 20px 60px rgba(0,0,0,0.12);">
            <img src="{{ $card }}" alt="Joyeux anniversaire {{ $first }}" class="w-full block">
        </div>

        <div class="flex flex-col sm:flex-row gap-3 mb-12">
            <a href="{{ route('birthdays.card', $member->birthdayToken()) }}"
               class="btn-rose flex-1 justify-center text-sm py-3.5">Enregistrer ma carte</a>
            <a href="{{ $share }}" target="_blank" rel="noopener noreferrer"
               class="flex-1 text-center text-sm py-3.5 rounded-full font-medium inline-flex items-center justify-center gap-2"
               style="border:1px solid var(--border);color:var(--dark);background:#fff;">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884a9.82 9.82 0 016.988 2.896 9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.465 3.488"/></svg>
                Partager sur WhatsApp
            </a>
        </div>
        @else
        <div class="text-center mb-12">
            <p class="text-6xl mb-4">🎂</p>
            <h1 class="text-3xl font-bold" style="color:var(--dark);font-family:'Playfair Display',serif;">Joyeux anniversaire {{ $first }} !</h1>
        </div>
        @endif

        {{-- Le mur --}}
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold mb-2" style="color:var(--dark);font-family:'Playfair Display',serif;">Le mur de {{ $first }}</h2>
            <p class="text-sm" style="color:var(--gray);">
                {{ $wishes->count() > 0 ? $wishes->count().' message(s) de la communauté' : 'Sois la première à lui laisser un mot.' }}
            </p>
        </div>

        @if($open)
        <div class="card p-6 mb-8">
            @if($errors->any())
            <div class="px-4 py-3 rounded-xl text-sm mb-4" style="background:#FEF2F2;border:1px solid #FCA5A5;color:#991B1B;">
                <ul class="space-y-0.5 list-none">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            <form method="POST" action="{{ route('birthdays.wish', $member->birthdayToken()) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">Ton prénom <span style="color:var(--rose);">*</span></label>
                    <input type="text" name="author_name" value="{{ old('author_name') }}" class="form-input" placeholder="Mariam" maxlength="80" required>
                </div>
                <div>
                    <label class="form-label">Ton message <span style="color:var(--rose);">*</span></label>
                    <textarea name="message" rows="3" class="form-input" maxlength="500" placeholder="Belle année à toi {{ $first }} !" required>{{ old('message') }}</textarea>
                </div>
                <button type="submit" class="btn-rose w-full justify-center py-3.5">Lui souhaiter</button>
                <x-honeypot />
            </form>
        </div>
        @else
        <p class="text-sm text-center mb-8 px-5 py-4 rounded-2xl" style="background:#F3F4F6;color:var(--gray);">
            Les vœux sont clos — mais les messages reçus restent ici.
        </p>
        @endif

        <div class="space-y-3">
            @foreach($wishes as $wish)
            <div class="card p-5">
                <p class="text-sm leading-relaxed mb-2" style="color:var(--dark);">{{ $wish->message }}</p>
                <p class="text-xs" style="color:var(--gray);">— {{ $wish->author_name }}, {{ $wish->created_at->diffForHumans() }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
