@extends('layouts.public')

@section('title', 'Femmes Sans Limites — Brise tes limites, révèle ta puissance')

@section('content')

{{-- ═══════════════════ HERO ═══════════════════ --}}
<section class="relative min-h-screen flex items-center overflow-hidden" style="background: linear-gradient(135deg, #FDF0F5 0%, #FDFAF9 50%, #FDF5E8 100%);">
    {{-- Décors géométriques --}}
    <div class="absolute top-0 right-0 w-[600px] h-[600px] rounded-full opacity-20 pointer-events-none" style="background: radial-gradient(circle, var(--rose-light), transparent 70%); transform: translate(30%, -30%);"></div>
    <div class="absolute bottom-0 left-0 w-[400px] h-[400px] rounded-full opacity-15 pointer-events-none" style="background: radial-gradient(circle, var(--gold-light), transparent 70%); transform: translate(-30%, 30%);"></div>

    <div class="max-w-7xl mx-auto px-6 py-32 grid grid-cols-1 lg:grid-cols-2 gap-16 items-center w-full">
        {{-- Text --}}
        <div>
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-semibold mb-8" style="background:rgba(217,30,110,0.08); color:var(--rose);">
                <span class="w-1.5 h-1.5 rounded-full" style="background:var(--rose);"></span>
                Plateforme de transformation féminine
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold leading-tight mb-6" style="color:var(--dark);">
                Brise tes<br>
                <span style="color:var(--rose);">limites.</span><br>
                <span class="italic" style="color:var(--gold);">Révèle</span> ta<br>
                puissance.
            </h1>

            <p class="text-lg leading-relaxed mb-10 max-w-md" style="color:var(--gray);">
                Femmes Sans Limites est une plateforme dédiée aux femmes qui désirent évoluer, se dépasser et briser les barrières mentales imposées par la société.
            </p>

            <div class="flex flex-wrap gap-4">
                <a href="{{ route('events.index') }}" class="btn-rose">
                    Rejoindre la communauté
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
                <a href="{{ route('about') }}" class="btn-gold">
                    Découvrir la vision
                </a>
            </div>

            {{-- Stats --}}
            <div class="flex gap-12 mt-16 pt-8 border-t border-gray-100">
                <div>
                    <p class="text-3xl font-bold" style="color:var(--rose); font-family:'Playfair Display',serif;">4</p>
                    <p class="text-sm mt-1" style="color:var(--gray);">Piliers de<br>transformation</p>
                </div>
                <div>
                    <p class="text-3xl font-bold" style="color:var(--gold); font-family:'Playfair Display',serif;">8+</p>
                    <p class="text-sm mt-1" style="color:var(--gray);">Écoles<br>spécialisées</p>
                </div>
                <div>
                    <p class="text-3xl font-bold" style="color:var(--dark); font-family:'Playfair Display',serif;">∞</p>
                    <p class="text-sm mt-1" style="color:var(--gray);">Potentiel à<br>révéler</p>
                </div>
            </div>
        </div>

        {{-- Logo / Visual --}}
        <div class="flex justify-center lg:justify-end">
            <div class="relative">
                <div class="absolute inset-0 rounded-full opacity-20" style="background: radial-gradient(circle, var(--rose-light), transparent 60%); transform: scale(1.3);"></div>
                <img src="{{ asset('logo_FSL.png') }}" alt="Femmes Sans Limites" class="relative w-80 md:w-96 lg:w-[420px] drop-shadow-2xl">
            </div>
        </div>
    </div>

    {{-- Scroll indicator --}}
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 animate-bounce">
        <span class="text-xs" style="color:var(--gray);">Découvrir</span>
        <svg class="w-5 h-5" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </div>
</section>

{{-- ═══════════════════ MISSION ═══════════════════ --}}
<section class="py-24 px-6">
    <div class="max-w-4xl mx-auto text-center fade-up">
        <div class="gold-divider"></div>
        <h2 class="section-title mt-6 mb-6">Notre Mission</h2>
        <p class="text-xl leading-relaxed" style="color:var(--gray);">
            Notre mission est de vous aider à <strong style="color:var(--dark);">briser ces barrières</strong>, à croire en votre valeur et à révéler pleinement votre potentiel.
        </p>
        <p class="text-lg leading-relaxed mt-4" style="color:var(--gray);">
            Concrètement, cela passe par des <strong style="color:var(--rose);">événements</strong>, des <strong style="color:var(--rose);">panels</strong>, des formations en ligne et en présentiel, ainsi qu'une <strong style="color:var(--rose);">communauté active</strong> où les femmes se soutiennent et grandissent ensemble.
        </p>
        <div class="gold-divider mt-8"></div>
    </div>
</section>

{{-- ═══════════════════ PILIERS ═══════════════════ --}}
<section class="py-24 px-6" style="background:var(--rose-pale);">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16 fade-up">
            <h2 class="section-title">Les 4 Piliers</h2>
            <p class="section-subtitle mt-4">Quatre dimensions essentielles pour votre transformation complète</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            {{-- Pilier 1 --}}
            <div class="card-pillar fade-up">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl mb-6" style="background:rgba(217,30,110,0.08);">
                    🌱
                </div>
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-xs font-bold uppercase tracking-widest" style="color:var(--rose);">Pilier 01</span>
                    <span class="flex-1 h-px" style="background:var(--rose-light);"></span>
                </div>
                <h3 class="text-2xl font-bold mb-3" style="color:var(--dark);">Développement Personnel</h3>
                <p class="text-sm leading-relaxed mb-6" style="color:var(--gray);">Transformation intérieure. Construire des femmes fortes, conscientes et mentalement solides.</p>
                <ul class="space-y-2">
                    @foreach(['École de l\'estime de soi & santé mentale', 'École de l\'intelligence émotionnelle', 'École de la résilience & de la discipline', 'École de la vision'] as $school)
                    <li class="flex items-center gap-2 text-sm" style="color:var(--dark);">
                        <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:var(--rose);"></span>
                        {{ $school }}
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Pilier 2 --}}
            <div class="card-pillar fade-up" style="transition-delay:0.1s;">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl mb-6" style="background:rgba(201,168,76,0.1);">
                    🎓
                </div>
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-xs font-bold uppercase tracking-widest" style="color:var(--gold);">Pilier 02</span>
                    <span class="flex-1 h-px" style="background:var(--gold-light);"></span>
                </div>
                <h3 class="text-2xl font-bold mb-3" style="color:var(--dark);">Formation & Compétences</h3>
                <p class="text-sm leading-relaxed mb-6" style="color:var(--gray);">Apprentissage concret. Donner des outils pour évoluer professionnellement.</p>
                <ul class="space-y-2">
                    @foreach(['Compétences pratiques & métiers', 'Formation business & entrepreneuriat', 'Développement professionnel', 'Ateliers pratiques en présentiel'] as $school)
                    <li class="flex items-center gap-2 text-sm" style="color:var(--dark);">
                        <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:var(--gold);"></span>
                        {{ $school }}
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Pilier 3 --}}
            <div class="card-pillar fade-up" style="transition-delay:0.2s;">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl mb-6" style="background:rgba(217,30,110,0.08);">
                    👑
                </div>
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-xs font-bold uppercase tracking-widest" style="color:var(--rose);">Pilier 03</span>
                    <span class="flex-1 h-px" style="background:var(--rose-light);"></span>
                </div>
                <h3 class="text-2xl font-bold mb-3" style="color:var(--dark);">Leadership & Indépendance</h3>
                <p class="text-sm leading-relaxed mb-6" style="color:var(--gray);">Pouvoir personnel & liberté. Former des femmes influentes capables de diriger.</p>
                <ul class="space-y-2">
                    @foreach(['École du leadership féminin', "École de l'indépendance financière", 'Gestion & prise de décision', 'Influence & communication'] as $school)
                    <li class="flex items-center gap-2 text-sm" style="color:var(--dark);">
                        <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:var(--rose);"></span>
                        {{ $school }}
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Pilier 4 --}}
            <div class="card-pillar fade-up" style="transition-delay:0.3s;">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl mb-6" style="background:rgba(201,168,76,0.1);">
                    💆‍♀️
                </div>
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-xs font-bold uppercase tracking-widest" style="color:var(--gold);">Pilier 04</span>
                    <span class="flex-1 h-px" style="background:var(--gold-light);"></span>
                </div>
                <h3 class="text-2xl font-bold mb-3" style="color:var(--dark);">Bien-être & Selfcare</h3>
                <p class="text-sm leading-relaxed mb-6" style="color:var(--gray);">Équilibre & énergie. Des femmes épanouies dans tous les aspects de leur vie.</p>
                <ul class="space-y-2">
                    @foreach(['École du bien-être & équilibre de vie', 'Gestion du stress & repos', 'Image de soi & soin personnel', 'Ateliers énergie & vitalité'] as $school)
                    <li class="flex items-center gap-2 text-sm" style="color:var(--dark);">
                        <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:var(--gold);"></span>
                        {{ $school }}
                    </li>
                    @endforeach
                </ul>
            </div>

        </div>
    </div>
</section>

{{-- ═══════════════════ EVENTS TEASER ═══════════════════ --}}
@if($events->count() > 0)
<section class="py-24 px-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-end justify-between mb-12 fade-up">
            <div>
                <h2 class="section-title text-left">Prochains Événements</h2>
                <p class="text-lg mt-2" style="color:var(--gray);">Ne manquez pas nos prochaines rencontres</p>
            </div>
            <a href="{{ route('events.index') }}" class="btn-outline-rose hidden md:inline-flex">Voir tous →</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($events as $event)
            <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 fade-up group">
                @if($event->image)
                <div class="h-48 overflow-hidden">
                    <img src="{{ asset('storage/'.$event->image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
                @else
                <div class="h-48 flex items-center justify-center" style="background:linear-gradient(135deg,var(--rose-pale),var(--gold-light));">
                    <svg class="w-16 h-16 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                @endif

                <div class="p-6">
                    <div class="flex items-center gap-2 mb-3">
                        @if($event->is_paid)
                        <span class="text-xs px-2 py-1 rounded-full font-semibold" style="background:rgba(217,30,110,0.08);color:var(--rose);">Payant • {{ number_format($event->price, 0, ',', ' ') }} {{ $event->currency }}</span>
                        @else
                        <span class="text-xs px-2 py-1 rounded-full font-semibold" style="background:rgba(201,168,76,0.1);color:var(--gold-dark);">Gratuit</span>
                        @endif
                    </div>
                    <h3 class="text-lg font-bold mb-2" style="color:var(--dark);">{{ $event->title }}</h3>
                    <p class="text-sm mb-4" style="color:var(--gray);">{{ Str::limit($event->short_description ?? $event->description, 80) }}</p>
                    <div class="flex items-center gap-4 text-xs mb-5" style="color:var(--gray);">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ $event->event_date->format('d M Y') }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            {{ $event->city ?? $event->location }}
                        </span>
                    </div>
                    <a href="{{ route('events.show', $event->slug) }}" class="btn-rose w-full justify-center text-sm py-2.5">S'inscrire</a>
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-8 md:hidden">
            <a href="{{ route('events.index') }}" class="btn-outline-rose">Voir tous les événements</a>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════ CITATION ═══════════════════ --}}
<section class="py-24 px-6" style="background:var(--dark);">
    <div class="max-w-4xl mx-auto text-center fade-up">
        <div class="w-12 h-12 mx-auto mb-8 opacity-30">
            <svg fill="currentColor" class="text-white w-full h-full" viewBox="0 0 32 32"><path d="M10 8C6.686 8 4 10.686 4 14s2.686 6 6 6c.796 0 1.557-.159 2.254-.443C11.658 21.696 10.5 23.763 9 25.5L11.5 27C14.5 23.5 16 19.5 16 16c0-4.418-2.686-8-6-8zm16 0c-3.314 0-6 2.686-6 6s2.686 6 6 6c.796 0 1.557-.159 2.254-.443C27.658 21.696 26.5 23.763 25 25.5L27.5 27C30.5 23.5 32 19.5 32 16c0-4.418-2.686-8-6-8z"/></svg>
        </div>
        <blockquote class="text-3xl md:text-4xl font-bold italic leading-snug text-white mb-8" style="font-family:'Playfair Display',serif;">
            Une femme qui croit en elle-même est une force que rien<br>
            <span style="color:var(--gold);">ne peut arrêter.</span>
        </blockquote>
        <div class="flex items-center justify-center gap-3">
            <span class="w-8 h-0.5" style="background:var(--rose);"></span>
            <p class="text-sm" style="color:rgba(255,255,255,0.5);">Femmes Sans Limites</p>
            <span class="w-8 h-0.5" style="background:var(--gold);"></span>
        </div>
    </div>
</section>

{{-- ═══════════════════ CTA FINAL ═══════════════════ --}}
<section class="py-24 px-6" style="background:linear-gradient(135deg,var(--rose-pale) 0%, #FFF8EC 100%);">
    <div class="max-w-3xl mx-auto text-center fade-up">
        <h2 class="section-title mb-4">Prête à te transformer ?</h2>
        <p class="section-subtitle mb-10">
            Rejoins une communauté de femmes qui se soutiennent, s'élèvent et grandissent ensemble. Ton voyage commence ici.
        </p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="{{ route('events.index') }}" class="btn-rose text-base px-10 py-4">
                Voir les événements
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
            <a href="{{ route('contact') }}" class="btn-gold text-base px-10 py-4">
                Nous contacter
            </a>
        </div>
    </div>
</section>

@endsection
