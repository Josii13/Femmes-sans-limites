@extends('layouts.public')
@section('title', 'Événements — Femme Sans Limites')
@section('description', 'Découvrez tous les événements FSL : conférences, ateliers, formations et rencontres pour les femmes ambitieuses d\'Afrique et de la diaspora.')
@section('og_type', 'website')

@section('content')

{{-- Hero --}}
<section class="pt-20 pb-16 bg-white relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none" style="background:radial-gradient(ellipse at 80% 50%,var(--rose-pale) 0%,transparent 60%);"></div>
    <div class="max-w-7xl mx-auto px-5 lg:px-8 relative">
        <div class="max-w-2xl">
            <span class="section-label fade-up">Agenda FSL</span>
            <h1 class="text-5xl lg:text-6xl font-bold mt-3 mb-5 fade-up" style="color:var(--dark);font-family:'Playfair Display',serif;">
                Nos événements
            </h1>
            <p class="text-lg leading-relaxed fade-up" style="color:var(--gray);">
                Des forums, panels, masterclasses et ateliers pensés pour te connecter, te former et t'inspirer. Chaque événement est une opportunité de grandir.
            </p>
        </div>
    </div>
</section>

{{-- Events --}}
<section class="py-16 lg:py-20" style="background:var(--warm);">
    <div class="max-w-7xl mx-auto px-5 lg:px-8">

        @if($events->isEmpty())
        <div class="text-center py-24">
            <div class="w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-5" style="background:var(--rose-pale);">
                <svg class="w-8 h-8" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="text-2xl font-bold mb-3" style="color:var(--dark);font-family:'Playfair Display',serif;">Voir tout</h3>
            <p class="text-base mb-8" style="color:var(--gray);">Les prochains événements seront annoncés ici très prochainement. Active les notifications pour ne rien manquer !</p>
            <button @click="$store.modal.contact = true" class="btn-rose">Être notifiée</button>
        </div>

        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($events as $event)
            <article class="card-hover flex flex-col fade-up overflow-hidden group">

                {{-- Image --}}
                <div class="h-48 relative overflow-hidden flex-shrink-0">
                    @if($event->image)
                    <img src="{{ asset('storage/'.$event->image) }}" alt="{{ $event->title }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                    <div class="w-full h-full flex items-center justify-center" style="background:linear-gradient(135deg,var(--rose-pale) 0%,var(--gold-pale) 100%);">
                        <img src="{{ asset('logo_FSL.png') }}" alt="" class="w-16 h-auto opacity-15 pointer-events-none">
                    </div>
                    @endif

                    {{-- Badges --}}
                    <div class="absolute top-3 left-3 flex items-center gap-2">
                        @if($event->is_paid)
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-white shadow-sm" style="color:var(--rose);">{{ number_format($event->price,0,',',' ') }} {{ $event->currency }}</span>
                        @else
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-white shadow-sm" style="color:var(--gold-dark);">Gratuit</span>
                        @endif
                        @if($event->is_sold_out)
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-gray-800 text-white">Complet</span>
                        @endif
                    </div>

                    {{-- Date badge --}}
                    <div class="absolute top-3 right-3 bg-white rounded-xl shadow-sm px-3 py-1.5 text-center">
                        <p class="text-xs font-bold uppercase" style="color:var(--rose);">{{ $event->event_date->isoFormat('MMM') }}</p>
                        <p class="text-lg font-bold leading-tight" style="color:var(--dark);">{{ $event->event_date->format('d') }}</p>
                    </div>
                </div>

                {{-- Content --}}
                <div class="p-5 flex flex-col flex-1">
                    <div class="flex items-center gap-4 text-xs mb-3" style="color:var(--gray);">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $event->location }}{{ $event->city ? ', '.$event->city : '' }}
                        </span>
                        @if($event->spots_left !== null && !$event->is_sold_out)
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" style="color:var(--gold);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $event->spots_left }} place(s)
                        </span>
                        @endif
                    </div>

                    <h3 class="text-lg font-bold mb-2 flex-1" style="color:var(--dark);font-family:'Playfair Display',serif;">{{ $event->title }}</h3>
                    <p class="text-sm leading-relaxed mb-5" style="color:var(--gray);">{{ Str::limit($event->short_description ?? $event->description, 110) }}</p>

                    <div class="flex items-center justify-between pt-4" style="border-top:1px solid var(--border);">
                        <span class="text-xs" style="color:var(--gray);">{{ $event->event_date->isoFormat('HH[h]mm') }}</span>
                        @if($event->is_sold_out)
                        <span class="text-xs font-medium px-3 py-1.5 rounded-full bg-gray-100 text-gray-400">Complet</span>
                        @else
                        <a href="{{ route('events.show', $event->slug) }}" class="btn-rose text-xs px-4 py-2">
                            Voir & S'inscrire →
                        </a>
                        @endif
                    </div>
                </div>
            </article>
            @endforeach
        </div>

        {{-- CTA --}}
        <div class="text-center mt-16 fade-up">
            <p class="text-sm mb-4" style="color:var(--gray);">Tu veux être informée des prochains événements en avant-première ?</p>
            <button @click="$store.modal.join = true" class="btn-rose text-sm px-8 py-3">
                Rejoindre la communauté
            </button>
        </div>
        @endif

    </div>
</section>

@endsection
