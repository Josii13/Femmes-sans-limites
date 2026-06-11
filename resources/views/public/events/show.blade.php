@extends('layouts.public')
@section('title', $event->title.' — Femme Sans Limites')
@section('description', Str::limit($event->short_description ?? $event->description, 160))
@section('og_type', 'article')
@section('og_image', $event->image ? asset('storage/'.$event->image) : asset('logo_FSL.png'))

@push('seo')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "Event",
  "name": @json($event->title),
  "description": @json(Str::limit($event->short_description ?? $event->description, 200)),
  "startDate": "{{ $event->event_date->toIso8601String() }}",
  "location": { "@type": "Place", "name": @json($event->location.($event->city ? ', '.$event->city : '')) },
  "url": "{{ url()->current() }}",
  "organizer": { "@type": "Organization", "name": "Femme Sans Limites", "url": "{{ config('app.url') }}" }
  @if($event->image),"image": "{{ asset('storage/'.$event->image) }}"@endif
}
</script>
@endpush

@section('content')
@php
    $closed = $event->registration_closes_at && now()->gt($event->registration_closes_at);
    $canRegister = ! $event->is_sold_out && ! $closed;
    $autoOpen = $errors->any() || session('error');
@endphp

<div x-data="{ reg: {{ $autoOpen ? 'true' : 'false' }}, details: false }">

{{-- ══ HERO ══ --}}
<section class="pt-24 pb-12 bg-white">
    <div class="max-w-6xl mx-auto px-5 lg:px-8">
        <a href="{{ route('events.index') }}" class="inline-flex items-center gap-2 text-sm mb-6 group" style="color:var(--gray);">
            <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Retour aux événements
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-14 items-center">
            {{-- Image --}}
            <div class="rounded-3xl overflow-hidden aspect-[4/3] relative">
                @if($event->image)
                <img src="{{ asset('storage/'.$event->image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
                @else
                <div class="w-full h-full flex items-center justify-center" style="background:linear-gradient(135deg,var(--rose-pale),var(--gold-pale));">
                    <img src="{{ asset('logo_FSL.png') }}" alt="FSL" class="w-28 opacity-20">
                </div>
                @endif
                <div class="absolute top-4 left-4 flex flex-wrap gap-2">
                    @if($event->is_paid)
                    <span class="text-sm font-bold px-3 py-1.5 rounded-full bg-white shadow" style="color:var(--rose);">{{ number_format($event->price, 0, ',', ' ') }} {{ $event->currency }}</span>
                    @else
                    <span class="text-sm font-bold px-3 py-1.5 rounded-full bg-white shadow" style="color:var(--gold-dark);">Gratuit</span>
                    @endif
                    @if($event->is_sold_out)<span class="text-sm font-bold px-3 py-1.5 rounded-full bg-gray-800 text-white shadow">Complet</span>@endif
                </div>
            </div>

            {{-- Info --}}
            <div>
                <span class="section-label">{{ $event->event_date->isoFormat('MMMM YYYY') }}</span>
                <h1 class="text-3xl lg:text-4xl font-bold mt-3 mb-5 leading-tight" style="color:var(--dark);font-family:'Playfair Display',serif;">{{ $event->title }}</h1>

                @if($event->short_description)
                <p class="text-base leading-relaxed mb-6" style="color:var(--gray);">{{ $event->short_description }}</p>
                @endif

                {{-- Méta compacte --}}
                <div class="space-y-3 mb-6">
                    <div class="flex items-center gap-3 text-sm" style="color:var(--dark);">
                        <svg class="w-4 h-4 flex-shrink-0" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span class="font-medium">{{ $event->event_date->isoFormat('dddd D MMMM YYYY [à] HH[h]mm') }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm" style="color:var(--dark);">
                        <svg class="w-4 h-4 flex-shrink-0" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="font-medium">{{ $event->location }}{{ $event->city ? ', '.$event->city : '' }}</span>
                    </div>
                    @if($event->spots_left !== null)
                    <div class="flex items-center gap-3 text-sm" style="color:var(--dark);">
                        <svg class="w-4 h-4 flex-shrink-0" style="color:var(--gold);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="font-medium">{{ $event->is_sold_out ? 'Complet' : $event->spots_left.' place(s) sur '.$event->capacity }}</span>
                    </div>
                    @endif
                </div>

                {{-- Message de session (inscription gratuite confirmée) --}}
                @if(session('success'))
                <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm mb-4" style="background:var(--rose-pale);color:var(--rose);">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
                @endif

                {{-- CTA --}}
                <div class="flex flex-col sm:flex-row gap-3">
                    @if($canRegister)
                    <button @click="reg = true" class="btn-rose flex-1 justify-center text-base py-4">
                        {{ $event->is_paid ? 'Réserver — '.number_format($event->price, 0, ',', ' ').' '.$event->currency : "S'inscrire gratuitement" }}
                    </button>
                    @elseif($event->is_sold_out)
                    <button @click="reg = true" class="btn-gold flex-1 justify-center text-base py-4">Liste d'attente</button>
                    @else
                    <span class="flex-1 text-center text-sm rounded-xl py-4 px-4" style="background:#F3F4F6;color:var(--gray);">Les inscriptions sont closes.</span>
                    @endif

                    <button @click="details = true" class="btn-outline justify-center text-sm py-4 px-6">Détails</button>
                </div>

                <a href="{{ route('events.ical', $event->slug) }}" class="inline-flex items-center gap-2 text-xs mt-4" style="color:var(--gray);">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Ajouter à mon calendrier
                </a>

                @if($event->is_paid && $canRegister)
                <p class="text-xs mt-4" style="color:var(--gray);">🔒 Paiement en ligne sécurisé (Wave, Orange Money, MTN, Moov, carte).</p>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- ══ MODAL DÉTAILS ══ --}}
<div x-show="details" x-cloak class="modal-overlay" @click.self="details = false"
     x-transition.opacity>
    <div class="modal-box" @keydown.escape.window="details = false">
        <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid var(--border);">
            <h2 class="text-lg font-bold" style="color:var(--dark);font-family:'Playfair Display',serif;">À propos de l'événement</h2>
            <button @click="details = false" class="text-gray-400 hover:text-gray-700 text-2xl leading-none">&times;</button>
        </div>
        <div class="px-6 py-5">
            <div class="text-sm leading-relaxed" style="color:var(--gray);">{!! nl2br(e($event->description)) !!}</div>
        </div>
    </div>
</div>

{{-- ══ MODAL INSCRIPTION / LISTE D'ATTENTE ══ --}}
<div x-show="reg" x-cloak class="modal-overlay" @click.self="reg = false" x-transition.opacity>
    <div class="modal-box" @keydown.escape.window="reg = false">
        <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid var(--border);">
            <h2 class="text-lg font-bold" style="color:var(--dark);font-family:'Playfair Display',serif;">
                {{ $event->is_sold_out ? "Liste d'attente" : 'Réserve ta place' }}
            </h2>
            <button @click="reg = false" class="text-gray-400 hover:text-gray-700 text-2xl leading-none">&times;</button>
        </div>

        <div class="px-6 py-5">
            @if(session('error'))
            <div class="flex items-center gap-2 px-4 py-3 rounded-xl text-sm mb-4" style="background:#FEE2E2;color:#991B1B;">{{ session('error') }}</div>
            @endif
            @if($errors->any())
            <div class="px-4 py-3 rounded-xl text-sm mb-4" style="background:#FEF2F2;border:1px solid #FCA5A5;color:#991B1B;">
                <ul class="space-y-0.5 list-none">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            @if($event->is_paid && ! $event->is_sold_out)
            <div class="rounded-xl px-4 py-3 text-sm mb-4" style="background:var(--rose-pale);color:var(--dark);">
                <strong>{{ number_format($event->price, 0, ',', ' ') }} {{ $event->currency }}</strong> — tu seras redirigée vers le paiement sécurisé, puis tu recevras ton reçu et ton QR d'accès par email.
            </div>
            @endif

            <form action="{{ $event->is_sold_out ? route('events.waiting-list', $event->slug) : route('events.register', $event->slug) }}"
                  method="POST" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Prénom <span style="color:var(--rose);">*</span></label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" class="form-input" placeholder="Marie" required>
                    </div>
                    <div>
                        <label class="form-label">Nom <span style="color:var(--rose);">*</span></label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" class="form-input" placeholder="Koné" required>
                    </div>
                </div>
                <div>
                    <label class="form-label">Email <span style="color:var(--rose);">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-input" placeholder="marie@exemple.com" required>
                </div>
                <div>
                    <label class="form-label">Téléphone <span class="font-normal" style="color:var(--gray);">(optionnel)</span></label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" class="form-input" placeholder="+225 07 00 00 00 00">
                </div>

                <button type="submit" class="{{ $event->is_sold_out ? 'btn-gold' : 'btn-rose' }} w-full justify-center py-3.5" :disabled="submitting" :class="submitting ? 'opacity-70' : ''">
                    <template x-if="!submitting"><span>
                        @if($event->is_sold_out) Rejoindre la liste d'attente
                        @elseif($event->is_paid) Payer et réserver →
                        @else Confirmer mon inscription @endif
                    </span></template>
                    <template x-if="submitting"><span class="inline-flex items-center gap-2"><svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg> Patiente…</span></template>
                </button>
                <p class="text-xs text-center" style="color:var(--gray);">En t'inscrivant, tu acceptes d'être contactée par l'équipe FSL pour cet événement.</p>
            </form>
        </div>
    </div>
</div>

</div>

{{-- ══ CTA membre ══ --}}
<section class="py-16" style="background:var(--dark);">
    <div class="max-w-2xl mx-auto px-5 text-center">
        <p class="text-sm mb-2" style="color:rgba(255,255,255,0.5);">Tu n'es pas encore membre FSL ?</p>
        <h2 class="text-2xl font-bold mb-5 text-white" style="font-family:'Playfair Display',serif;">Rejoins la communauté</h2>
        <p class="text-sm mb-8" style="color:rgba(255,255,255,0.6);">Accède à tous nos événements, formations et au réseau de femmes leaders d'Afrique et de la diaspora.</p>
        <button @click="$store.modal.join = true" class="btn-rose">Devenir membre</button>
    </div>
</section>

@endsection
