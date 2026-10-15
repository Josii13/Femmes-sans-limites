@extends('layouts.public')
@section('title', 'Mon billet — '.$registration->event->title)
{{-- Un billet ne doit jamais se retrouver dans un moteur de recherche. --}}
@section('robots', 'noindex, nofollow')

@section('content')
@php
    $event = $registration->event;
    $isPast = $event->hasTakenPlace();
@endphp

<section class="min-h-[70vh] flex items-center justify-center py-24 px-5" style="background:var(--warm);">
    <div class="max-w-md w-full">

        @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm mb-4" style="background:#D1FAE5;color:#065F46;">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        <div class="card overflow-hidden">
            <div class="px-6 py-5 text-center" style="background:var(--dark);">
                <p class="text-xs uppercase tracking-wider mb-1" style="color:rgba(255,255,255,0.5);">Billet d'accès</p>
                <h1 class="text-lg font-bold text-white" style="font-family:'Playfair Display',serif;">{{ $event->title }}</h1>
            </div>

            <div class="p-6 text-center">
                @if($registration->status === 'attended')
                <p class="text-sm px-4 py-2 rounded-xl mb-5 inline-block" style="background:#EDE9FE;color:#5B21B6;">
                    Ce billet a déjà été scanné à l'entrée.
                </p>
                @elseif($isPast)
                <p class="text-sm px-4 py-2 rounded-xl mb-5 inline-block" style="background:#F3F4F6;color:var(--gray);">
                    Cet événement a déjà eu lieu.
                </p>
                @endif

                @if($svg)
                {{-- SVG issu de notre générateur, inséré tel quel : le billet reste
                     lisible même si le réseau lâche une fois la page ouverte. --}}
                <div class="inline-block p-4 rounded-2xl bg-white" style="border:1px solid var(--border);">
                    <div class="w-48 h-48 mx-auto">{!! $svg !!}</div>
                </div>
                @else
                <p class="text-sm px-4 py-3 rounded-xl" style="background:#FEF2F2;color:#991B1B;">
                    Le QR n'a pas pu être chargé. Présente-toi à l'accueil avec ton nom et ton email, l'équipe te retrouvera.
                </p>
                @endif

                <p class="text-sm mt-5" style="color:var(--gray);">Présente ce code à l'entrée.</p>

                <div class="mt-6 pt-6 space-y-3 text-left" style="border-top:1px solid var(--border);">
                    <div>
                        <p class="text-xs uppercase tracking-wider mb-0.5" style="color:var(--gray);">Au nom de</p>
                        <p class="text-sm font-medium" style="color:var(--dark);">{{ $registration->full_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider mb-0.5" style="color:var(--gray);">Date</p>
                        <p class="text-sm" style="color:var(--dark);">{{ $event->event_date->translatedFormat('l d F Y à H\hi') }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider mb-0.5" style="color:var(--gray);">Lieu</p>
                        <p class="text-sm" style="color:var(--dark);">{{ $event->location }}{{ $event->city ? ', '.$event->city : '' }}</p>
                    </div>
                </div>

                <div class="mt-6 flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('tickets.download', $registration->qr_token) }}"
                       class="btn-rose flex-1 justify-center text-sm py-3">Télécharger le QR</a>
                    @unless($isPast)
                    <a href="{{ route('events.ical', $event->slug) }}"
                       class="flex-1 text-center text-sm py-3 rounded-full font-medium" style="border:1px solid var(--border);color:var(--dark);">
                        Ajouter au calendrier
                    </a>
                    @endunless
                </div>
            </div>
        </div>

        <p class="text-xs text-center mt-5" style="color:var(--gray);">
            Garde ce lien : il te redonne accès à ton billet à tout moment.
        </p>
    </div>
</section>
@endsection
