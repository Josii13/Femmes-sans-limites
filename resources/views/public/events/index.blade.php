@extends('layouts.public')
@section('title', 'Événements — Femmes Sans Limites')

@section('content')

<section class="pt-40 pb-16 px-6 text-center" style="background:linear-gradient(135deg,#FDF0F5,#FDFAF9);">
    <div class="max-w-3xl mx-auto">
        <div class="gold-divider mb-8"></div>
        <h1 class="section-title mb-4">Nos Événements</h1>
        <p class="section-subtitle">Des rencontres, des panels et des formations pour vous élever et vous connecter à la communauté.</p>
    </div>
</section>

<section class="py-20 px-6">
    <div class="max-w-7xl mx-auto">
        @if($events->isEmpty())
        <div class="text-center py-24">
            <div class="w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6" style="background:var(--rose-pale);">
                <svg class="w-10 h-10" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="text-2xl font-bold mb-3" style="color:var(--dark);">Bientôt disponible</h3>
            <p style="color:var(--gray);">Les prochains événements seront annoncés ici. Restez connectée !</p>
            <a href="{{ route('contact') }}" class="btn-rose mt-8 inline-flex">Nous contacter</a>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($events as $event)
            <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 fade-up group flex flex-col">
                @if($event->image)
                <div class="h-52 overflow-hidden">
                    <img src="{{ asset('storage/'.$event->image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
                @else
                <div class="h-52 flex items-center justify-center" style="background:linear-gradient(135deg,var(--rose-pale),var(--gold-light));">
                    <svg class="w-20 h-20 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                @endif

                <div class="p-6 flex flex-col flex-1">
                    <div class="flex items-center justify-between mb-3">
                        @if($event->is_paid)
                        <span class="text-xs px-3 py-1 rounded-full font-semibold" style="background:rgba(217,30,110,0.08);color:var(--rose);">
                            {{ number_format($event->price, 0, ',', ' ') }} {{ $event->currency }}
                        </span>
                        @else
                        <span class="text-xs px-3 py-1 rounded-full font-semibold" style="background:rgba(201,168,76,0.1);color:var(--gold-dark);">Gratuit</span>
                        @endif

                        @if($event->is_sold_out)
                        <span class="text-xs px-3 py-1 rounded-full font-semibold bg-gray-100 text-gray-500">Complet</span>
                        @endif
                    </div>

                    <h3 class="text-xl font-bold mb-2 flex-1" style="color:var(--dark);">{{ $event->title }}</h3>
                    <p class="text-sm mb-4 leading-relaxed" style="color:var(--gray);">{{ Str::limit($event->short_description ?? $event->description, 100) }}</p>

                    <div class="space-y-2 text-xs mb-6" style="color:var(--gray);">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ $event->event_date->isoFormat('dddd D MMMM YYYY [à] HH[h]mm') }}
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $event->location }}{{ $event->city ? ', '.$event->city : '' }}
                        </div>
                        @if($event->spots_left !== null)
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0" style="color:var(--gold);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $event->spots_left }} place(s) restante(s)
                        </div>
                        @endif
                    </div>

                    @if($event->is_sold_out)
                    <button disabled class="w-full py-2.5 rounded-full text-sm font-medium bg-gray-100 text-gray-400 cursor-not-allowed">Complet</button>
                    @else
                    <a href="{{ route('events.show', $event->slug) }}" class="btn-rose w-full justify-center text-sm py-2.5">
                        S'inscrire →
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</section>

@endsection
