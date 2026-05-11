@extends('layouts.public')
@section('title', $event->title.' — FSL')
@section('description', Str::limit($event->short_description ?? $event->description, 160))

@section('content')

{{-- Back nav --}}
<div class="pt-24 pb-0 bg-white">
    <div class="max-w-7xl mx-auto px-5 lg:px-8 pt-4">
        <a href="{{ route('events.index') }}" class="inline-flex items-center gap-2 text-sm transition-colors group" style="color:var(--gray);">
            <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Retour aux événements
        </a>
    </div>
</div>

{{-- Hero --}}
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-5 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-start">

            {{-- Image --}}
            <div class="fade-up">
                <div class="rounded-3xl overflow-hidden aspect-[4/3] relative">
                    @if($event->image)
                    <img src="{{ asset('storage/'.$event->image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
                    @else
                    <div class="w-full h-full flex items-center justify-center" style="background:linear-gradient(135deg,var(--rose-pale) 0%,var(--gold-pale) 100%);">
                        <img src="{{ asset('logo_FSL.png') }}" alt="FSL" class="w-32 opacity-20">
                    </div>
                    @endif

                    {{-- Badges --}}
                    <div class="absolute top-4 left-4 flex flex-wrap gap-2">
                        @if($event->is_paid)
                        <span class="text-sm font-bold px-3 py-1.5 rounded-full bg-white shadow" style="color:var(--rose);">
                            {{ number_format($event->price, 0, ',', ' ') }} {{ $event->currency }}
                        </span>
                        @else
                        <span class="text-sm font-bold px-3 py-1.5 rounded-full bg-white shadow" style="color:var(--gold-dark);">Gratuit</span>
                        @endif
                        @if($event->is_sold_out)
                        <span class="text-sm font-bold px-3 py-1.5 rounded-full bg-gray-800 text-white shadow">Complet</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Info --}}
            <div class="fade-up">
                <span class="section-label">{{ $event->event_date->isoFormat('MMMM YYYY') }}</span>

                <h1 class="text-4xl lg:text-5xl font-bold mt-3 mb-8 leading-tight" style="color:var(--dark);font-family:'Playfair Display',serif;">
                    {{ $event->title }}
                </h1>

                {{-- Meta info --}}
                <div class="space-y-4 mb-8 pb-8" style="border-bottom:1px solid var(--border);">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--rose-pale);">
                            <svg class="w-4 h-4" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide mb-0.5" style="color:var(--gray);">Date & heure</p>
                            <p class="text-sm font-semibold" style="color:var(--dark);">{{ $event->event_date->isoFormat('dddd D MMMM YYYY [à] HH[h]mm') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--rose-pale);">
                            <svg class="w-4 h-4" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide mb-0.5" style="color:var(--gray);">Lieu</p>
                            <p class="text-sm font-semibold" style="color:var(--dark);">{{ $event->location }}{{ $event->city ? ', '.$event->city : '' }}</p>
                        </div>
                    </div>

                    @if($event->spots_left !== null)
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--gold-pale);">
                            <svg class="w-4 h-4" style="color:var(--gold);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide mb-0.5" style="color:var(--gray);">Places</p>
                            <p class="text-sm font-semibold" style="color:var(--dark);">
                                {{ $event->is_sold_out ? 'Complet' : $event->spots_left.' disponible(s) sur '.$event->capacity }}
                            </p>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Price block --}}
                @if($event->is_paid)
                <div class="rounded-2xl p-4 mb-6" style="background:var(--rose-pale);border:1px solid var(--rose-mid);">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-sm" style="color:var(--dark);">
                            <strong>Événement payant — {{ number_format($event->price, 0, ',', ' ') }} {{ $event->currency }}</strong><br>
                            <span style="color:var(--gray);">Un lien de paiement vous sera envoyé par email après inscription.</span>
                        </p>
                    </div>
                </div>
                @endif

                {{-- CTA --}}
                @if($event->is_sold_out)
                <div class="rounded-2xl p-4 text-center" style="background:#F3F4F6;">
                    <p class="font-semibold text-gray-500">Cet événement affiche complet.</p>
                    <p class="text-sm text-gray-400 mt-1">Rejoins la communauté pour être notifiée des prochains événements.</p>
                    <button @click="$store.modal.join = true" class="btn-rose mt-4 text-sm">Rejoindre FSL</button>
                </div>
                @else
                <a href="#inscription" class="btn-rose w-full justify-center text-base py-4">
                    S'inscrire à cet événement
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </a>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- Description --}}
<section class="py-16 lg:py-20" style="background:var(--warm);">
    <div class="max-w-4xl mx-auto px-5 lg:px-8">
        <div class="text-center mb-10 fade-up">
            <span class="section-label">Programme</span>
            <h2 class="text-3xl font-bold mt-3" style="color:var(--dark);font-family:'Playfair Display',serif;">À propos de cet événement</h2>
        </div>
        <div class="prose prose-lg max-w-none fade-up" style="color:var(--gray);line-height:1.9;">
            {!! nl2br(e($event->description)) !!}
        </div>
    </div>
</section>

{{-- Registration form --}}
@if(!$event->is_sold_out)
<section class="py-16 lg:py-20 bg-white" id="inscription">
    <div class="max-w-2xl mx-auto px-5 lg:px-8">

        <div class="text-center mb-10 fade-up">
            <span class="section-label">Inscription</span>
            <h2 class="text-3xl font-bold mt-3" style="color:var(--dark);font-family:'Playfair Display',serif;">Réserve ta place</h2>
            @if($event->spots_left !== null)
            <p class="text-sm mt-3" style="color:var(--gray);">Plus que <strong style="color:var(--rose);">{{ $event->spots_left }} place(s)</strong> disponible(s).</p>
            @endif
        </div>

        <div class="card p-8 fade-up" x-data="{ submitted: {{ session('success') ? 'true' : 'false' }} }">

            @if(session('success'))
            <div class="flex flex-col items-center text-center py-8">
                <div class="w-16 h-16 rounded-full flex items-center justify-center mb-5" style="background:var(--rose-pale);">
                    <svg class="w-8 h-8" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h3 class="text-xl font-bold mb-2" style="color:var(--dark);font-family:'Playfair Display',serif;">Inscription enregistrée !</h3>
                <p class="text-sm" style="color:var(--gray);">{{ session('success') }}</p>
                <a href="{{ route('events.index') }}" class="btn-outline mt-6 text-sm">Voir d'autres événements</a>
            </div>
            @else

            @if(session('error'))
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm mb-6" style="background:#FEE2E2;color:#991B1B;">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('error') }}
            </div>
            @endif

            @if($errors->any())
            <div class="px-4 py-3 rounded-xl text-sm mb-6" style="background:#FEF2F2;border:1px solid #FCA5A5;color:#991B1B;">
                <ul class="space-y-1">
                    @foreach($errors->all() as $e)
                    <li class="flex items-center gap-2"><span style="color:var(--rose);">•</span> {{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('events.register', $event->slug) }}" method="POST" class="space-y-5">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="form-label">Prénom <span style="color:var(--rose);">*</span></label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" class="form-input @error('first_name') !border-red-400 @enderror" placeholder="Marie">
                        @error('first_name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Nom <span style="color:var(--rose);">*</span></label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" class="form-input @error('last_name') !border-red-400 @enderror" placeholder="Koné">
                        @error('last_name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="form-label">Email <span style="color:var(--rose);">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-input @error('email') !border-red-400 @enderror" placeholder="marie@exemple.com">
                    @error('email')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Téléphone <span class="font-normal" style="color:var(--gray);">(optionnel)</span></label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" class="form-input" placeholder="+225 07 00 00 00 00">
                </div>

                <button type="submit" class="btn-rose w-full justify-center text-base py-4">
                    Confirmer mon inscription
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </button>

                <p class="text-xs text-center" style="color:var(--gray);">
                    En vous inscrivant, vous acceptez d'être contactée par l'équipe FSL concernant cet événement.
                </p>
            </form>
            @endif
        </div>
    </div>
</section>
@endif

{{-- CTA join --}}
<section class="py-16" style="background:var(--dark);">
    <div class="max-w-2xl mx-auto px-5 text-center fade-up">
        <p class="text-sm mb-2" style="color:rgba(255,255,255,0.5);">Tu n'es pas encore membre FSL ?</p>
        <h2 class="text-2xl font-bold mb-5 text-white" style="font-family:'Playfair Display',serif;">Rejoins la communauté</h2>
        <p class="text-sm mb-8" style="color:rgba(255,255,255,0.6);">Accède à tous nos événements, formations et au réseau de femmes leaders d'Afrique et de la diaspora.</p>
        <button @click="$store.modal.join = true" class="btn-rose">Devenir membre</button>
    </div>
</section>

@endsection
