@extends('layouts.public')
@section('title', $event->title.' — FSL')

@section('content')

<section class="pt-32 pb-0 px-6" style="background:linear-gradient(135deg,#FDF0F5,#FDFAF9);">
    <div class="max-w-7xl mx-auto pb-12">
        <a href="{{ route('events.index') }}" class="inline-flex items-center gap-2 text-sm mb-8 transition-colors" style="color:var(--gray);" onmouseover="this.style.color='var(--rose)'" onmouseout="this.style.color='var(--gray)'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Tous les événements
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-start">
            {{-- Image --}}
            <div class="rounded-3xl overflow-hidden shadow-xl">
                @if($event->image)
                <img src="{{ asset('storage/'.$event->image) }}" alt="{{ $event->title }}" class="w-full h-80 object-cover">
                @else
                <div class="w-full h-80 flex items-center justify-center" style="background:linear-gradient(135deg,var(--rose-pale),var(--gold-light));">
                    <img src="{{ asset('logo_FSL.png') }}" alt="FSL" class="w-40 opacity-30">
                </div>
                @endif
            </div>

            {{-- Info --}}
            <div>
                <div class="flex items-center gap-3 mb-4">
                    @if($event->is_paid)
                    <span class="text-sm px-4 py-1.5 rounded-full font-semibold" style="background:rgba(217,30,110,0.08);color:var(--rose);">
                        Payant — {{ number_format($event->price, 0, ',', ' ') }} {{ $event->currency }}
                    </span>
                    @else
                    <span class="text-sm px-4 py-1.5 rounded-full font-semibold" style="background:rgba(201,168,76,0.1);color:var(--gold-dark);">Gratuit</span>
                    @endif

                    @if($event->is_sold_out)
                    <span class="text-sm px-4 py-1.5 rounded-full font-semibold bg-gray-100 text-gray-500">Complet</span>
                    @endif
                </div>

                <h1 class="text-4xl font-bold mb-6" style="color:var(--dark);">{{ $event->title }}</h1>

                <div class="space-y-3 mb-8">
                    <div class="flex items-center gap-3 text-sm" style="color:var(--gray);">
                        <svg class="w-5 h-5 flex-shrink-0" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>{{ $event->event_date->isoFormat('dddd D MMMM YYYY [à] HH[h]mm') }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm" style="color:var(--gray);">
                        <svg class="w-5 h-5 flex-shrink-0" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>{{ $event->location }}{{ $event->city ? ', '.$event->city : '' }}</span>
                    </div>
                    @if($event->spots_left !== null)
                    <div class="flex items-center gap-3 text-sm" style="color:var(--gray);">
                        <svg class="w-5 h-5 flex-shrink-0" style="color:var(--gold);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>{{ $event->spots_left }} place(s) disponible(s) sur {{ $event->capacity }}</span>
                    </div>
                    @endif
                </div>

                <div class="prose prose-sm max-w-none mb-8 leading-relaxed" style="color:var(--gray);">
                    {!! nl2br(e($event->description)) !!}
                </div>

                @if($event->is_paid)
                <div class="rounded-2xl p-4 mb-6 text-sm" style="background:rgba(217,30,110,0.05); border:1px solid rgba(217,30,110,0.15);">
                    <p class="font-medium mb-1" style="color:var(--rose);">Événement payant</p>
                    <p style="color:var(--gray);">Après inscription, vous recevrez un lien de paiement par email. Votre place sera confirmée après réception du paiement.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- Form --}}
@if(!$event->is_sold_out)
<section class="py-16 px-6">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-3xl p-10 shadow-lg">
            <h2 class="text-2xl font-bold mb-2" style="color:var(--dark);">Inscription</h2>
            <p class="text-sm mb-8" style="color:var(--gray);">Remplissez le formulaire ci-dessous pour réserver votre place.</p>

            @if(session('success'))
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm mb-6" style="background:#D1FAE5;color:#065F46;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm mb-6" style="background:#FEE2E2;color:#991B1B;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('error') }}
            </div>
            @endif

            <form action="{{ route('events.register', $event->slug) }}" method="POST" class="space-y-5">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Prénom *</label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" class="form-input @error('first_name') border-red-400 @enderror" placeholder="Marie">
                        @error('first_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Nom *</label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" class="form-input @error('last_name') border-red-400 @enderror" placeholder="Dupont">
                        @error('last_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-input @error('email') border-red-400 @enderror" placeholder="marie@email.com">
                    @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Téléphone</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" class="form-input" placeholder="+225 00 00 00 00 00">
                </div>

                <button type="submit" class="btn-rose w-full justify-center text-base py-3.5">
                    Confirmer mon inscription
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </button>
            </form>
        </div>
    </div>
</section>
@endif

@endsection
