@extends('layouts.public')
@section('title', 'Rejoindre la communauté — Femme Sans Limites')
@section('description', 'Intègre la communauté Femme Sans Limites et accède à des événements, formations et un réseau de femmes leaders d\'Afrique et de la diaspora.')

@section('content')

{{-- Hero --}}
<section class="pt-28 pb-20 bg-white relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 rounded-full pointer-events-none" style="background:radial-gradient(circle,var(--rose-pale),transparent 70%);transform:translate(30%,-30%);"></div>
    <div class="max-w-7xl mx-auto px-5 lg:px-8">
        <div class="max-w-3xl fade-up">
            <span class="section-label">Adhésion FSL</span>
            <h1 class="text-5xl lg:text-6xl font-bold mt-4 mb-6" style="color:var(--dark);font-family:'Playfair Display',serif;">
                Rejoins le mouvement<br><em style="color:var(--rose);">des femmes leaders</em>
            </h1>
            <p class="text-xl leading-relaxed mb-10" style="color:var(--gray);max-width:560px;">
                Des femmes d'Afrique et de la diaspora se soutiennent, s'élèvent et construisent ensemble au sein de Femme Sans Limites.
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
                <button @click="$store.modal.join = true" class="btn-rose text-base px-10 py-4">
                    Postuler maintenant
                </button>
                <a href="{{ route('about') }}" class="btn-outline text-base px-10 py-4">
                    En savoir plus
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Benefits --}}
<section class="py-20 lg:py-28" style="background:var(--warm);">
    <div class="max-w-7xl mx-auto px-5 lg:px-8">
        <div class="text-center mb-14 fade-up">
            <span class="section-label">Ce que tu gagnes</span>
            <h2 class="text-4xl font-bold mt-3" style="color:var(--dark);font-family:'Playfair Display',serif;">Les avantages de la communauté</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach([
                ['Accès aux événements', 'Forums, panels, masterclasses et ateliers exclusifs organisés toute l\'année à travers l\'Afrique et en ligne.', 'var(--rose-pale)', 'var(--rose)', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>'],
                ['Réseau de femmes leaders', 'Connecte-toi avec des femmes entrepreneurs, cadres et professionnelles d\'Afrique et de la diaspora.', 'var(--gold-pale)', 'var(--gold)', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>'],
                ['Formations & Masterclasses', 'Accède à des contenus de développement personnel et professionnel conçus par et pour les femmes africaines ambitieuses.', 'var(--rose-pale)', 'var(--rose)', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>'],
                ['Mentorat personnalisé', 'Les membres Gold et Premium bénéficient d\'un accompagnement individuel avec des coachs et mentors certifiés.', 'var(--gold-pale)', 'var(--gold)', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>'],
                ['Carte de membre officielle', 'Reçois ta carte de membre FSL personnalisée — ton passeport pour la communauté et les événements.', 'var(--rose-pale)', 'var(--rose)', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>'],
                ['Communauté bienveillante', 'Un espace safe, positif et exigeant où les femmes se soutiennent mutuellement sans jugement ni concurrence toxique.', 'var(--gold-pale)', 'var(--gold)', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>'],
            ] as [$title, $desc, $bg, $color, $icon])
            <div class="card-hover p-7 fade-up">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-5" style="background:{{ $bg }};">
                    <svg class="w-5 h-5" style="color:{{ $color }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $icon !!}</svg>
                </div>
                <h3 class="text-lg font-bold mb-2" style="color:var(--dark);font-family:'Playfair Display',serif;">{{ $title }}</h3>
                <p class="text-sm leading-relaxed" style="color:var(--gray);">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Tiers --}}
<section class="py-20 lg:py-28 bg-white">
    <div class="max-w-7xl mx-auto px-5 lg:px-8">
        <div class="text-center mb-14 fade-up">
            <span class="section-label">Niveaux d'adhésion</span>
            <h2 class="text-4xl font-bold mt-3" style="color:var(--dark);font-family:'Playfair Display',serif;">Choisis ton parcours</h2>
            <p class="text-base mt-4" style="color:var(--gray);">Tu rejoins toujours en tant que membre Standard — l'équipe peut ensuite faire évoluer ton statut.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">

            {{-- Standard --}}
            <div class="rounded-2xl p-8 fade-up" style="background:var(--warm);border:2px solid var(--border);">
                <div class="w-10 h-10 rounded-full flex items-center justify-center mb-5" style="background:#E5E7EB;">
                    <span class="text-sm font-bold text-gray-500">S</span>
                </div>
                <h3 class="text-xl font-bold mb-1" style="color:var(--dark);font-family:'Playfair Display',serif;">Standard</h3>
                <p class="text-xs mb-6" style="color:var(--gray);">Point d'entrée dans la communauté</p>
                <ul class="space-y-2.5 text-sm" style="color:var(--gray);">
                    @foreach(['Accès aux événements publics', 'Carte de membre FSL', 'Communauté en ligne', 'Newsletter exclusive'] as $item)
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ $item }}
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Gold --}}
            <div class="rounded-2xl p-8 fade-up relative" style="background:var(--gold-pale);border:2px solid var(--gold);">
                <div class="absolute top-4 right-4">
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full text-white" style="background:var(--gold);">Populaire</span>
                </div>
                <div class="w-10 h-10 rounded-full flex items-center justify-center mb-5" style="background:var(--gold);">
                    <span class="text-sm font-bold text-white">G</span>
                </div>
                <h3 class="text-xl font-bold mb-1" style="color:var(--dark);font-family:'Playfair Display',serif;">Gold</h3>
                <p class="text-xs mb-6" style="color:var(--gray);">Pour les ambitieuses engagées</p>
                <ul class="space-y-2.5 text-sm" style="color:var(--gray);">
                    @foreach(['Tout le Standard', 'Accès prioritaire aux événements', 'Sessions de mentorat mensuel', 'Masterclasses exclusives', 'Groupe privé Gold'] as $item)
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" style="color:var(--gold-dark);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ $item }}
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Premium --}}
            <div class="rounded-2xl p-8 fade-up" style="background:var(--dark);border:2px solid var(--dark);">
                <div class="w-10 h-10 rounded-full flex items-center justify-center mb-5" style="background:var(--rose);">
                    <span class="text-sm font-bold text-white">P</span>
                </div>
                <h3 class="text-xl font-bold mb-1 text-white" style="font-family:'Playfair Display',serif;">Premium</h3>
                <p class="text-xs mb-6" style="color:rgba(255,255,255,0.4);">L'expérience FSL complète</p>
                <ul class="space-y-2.5 text-sm" style="color:rgba(255,255,255,0.7);">
                    @foreach(['Tout le Gold', 'Coaching individuel mensuel', 'Accès VIP tous événements', 'Programme leadership avancé', 'Réseau C-Suite & investisseurs'] as $item)
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ $item }}
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- Process --}}
<section class="py-20" style="background:var(--warm);">
    <div class="max-w-4xl mx-auto px-5 lg:px-8">
        <div class="text-center mb-14 fade-up">
            <span class="section-label">Comment ça marche</span>
            <h2 class="text-4xl font-bold mt-3" style="color:var(--dark);font-family:'Playfair Display',serif;">En 3 étapes simples</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach([
                ['01', 'Tu soumets ta candidature', 'Remplis le formulaire en 2 minutes avec tes informations de base et une photo pour ta carte de membre.'],
                ['02', 'L\'équipe valide ton profil', 'Notre équipe examine ta candidature et active ton compte sous 48h. Tu reçois ta carte de membre.'],
                ['03', 'Tu rejoins la communauté', 'Accède aux événements, formations et au réseau FSL. Bienvenue dans le mouvement !'],
            ] as [$n, $title, $desc])
            <div class="text-center fade-up">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-5" style="background:var(--rose);color:white;">
                    <span class="text-lg font-bold" style="font-family:'Playfair Display',serif;">{{ $n }}</span>
                </div>
                <h3 class="text-lg font-bold mb-2" style="color:var(--dark);font-family:'Playfair Display',serif;">{{ $title }}</h3>
                <p class="text-sm leading-relaxed" style="color:var(--gray);">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-24 bg-white">
    <div class="max-w-xl mx-auto px-5 text-center fade-up">
        <div class="gold-divider mx-auto mb-8"></div>
        <h2 class="text-4xl font-bold mb-5" style="color:var(--dark);font-family:'Playfair Display',serif;">Prête à rejoindre FSL ?</h2>
        <p class="text-lg mb-10" style="color:var(--gray);">Ta candidature prend 2 minutes. L'équipe te revient sous 48h.</p>
        <button @click="$store.modal.join = true" class="btn-rose text-base px-12 py-4">
            Postuler maintenant
        </button>
        <p class="text-xs mt-6" style="color:var(--gray);">Adhésion Standard par défaut. 100% gratuit pour commencer.</p>
    </div>
</section>

@endsection
