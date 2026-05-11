@extends('layouts.public')
@section('title', 'Femmes Sans Limites — Brise tes limites, révèle ta puissance')

@section('content')

{{-- ══════════════════ HERO ══════════════════ --}}
<section class="min-h-screen flex items-center bg-white overflow-hidden relative">
    {{-- Décor arrière-plan --}}
    <div class="absolute top-0 right-0 w-[500px] h-[500px] rounded-full pointer-events-none" style="background:radial-gradient(circle,var(--rose-pale) 0%,transparent 70%);transform:translate(30%,-20%);"></div>
    <div class="absolute bottom-0 left-0 w-[350px] h-[350px] rounded-full pointer-events-none" style="background:radial-gradient(circle,var(--gold-pale) 0%,transparent 70%);transform:translate(-30%,20%);"></div>

    <div class="max-w-7xl mx-auto px-5 lg:px-8 w-full py-20 lg:py-0">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center min-h-screen lg:min-h-0 lg:py-20">

            {{-- Texte --}}
            <div>
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-semibold mb-8" style="background:var(--rose-pale);color:var(--rose);">
                    <span class="w-1.5 h-1.5 rounded-full inline-block" style="background:var(--rose);"></span>
                    Plateforme de transformation féminine
                </div>

                <h1 class="text-5xl sm:text-6xl lg:text-7xl font-bold leading-[1.06] mb-6" style="color:var(--dark);font-family:'Playfair Display',serif;">
                    Brise tes<br>
                    <em style="color:var(--rose);font-style:italic;">limites.</em><br>
                    Révèle ta<br>
                    puissance.
                </h1>

                <p class="text-lg leading-relaxed mb-10 max-w-md" style="color:var(--gray);">
                    Femmes Sans Limites accompagne les femmes ambitieuses d'Afrique et de la diaspora vers leur plein potentiel — à travers des événements, du mentorat et une communauté puissante.
                </p>

                <div class="flex flex-wrap gap-4 mb-12">
                    <button @click="$store.modal.join = true" class="btn-rose text-base px-8 py-4">
                        Rejoindre la communauté
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </button>
                    <a href="{{ route('events.index') }}" class="btn-outline text-base px-8 py-4">
                        Voir les événements
                    </a>
                </div>

                {{-- Stats inline --}}
                <div class="flex flex-wrap gap-8">
                    @foreach([['500+','Femmes'],['15+','Pays'],['50+','Événements'],['5','Ans d\'impact']] as [$n,$l])
                    <div>
                        <p class="text-2xl font-bold" style="color:var(--dark);font-family:'Playfair Display',serif;">{{ $n }}</p>
                        <p class="text-xs font-medium uppercase tracking-wide" style="color:var(--gray);">{{ $l }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Visual --}}
            <div class="relative flex justify-center lg:justify-end">
                {{-- Cercle principal --}}
                <div class="relative w-72 h-72 sm:w-80 sm:h-80 lg:w-96 lg:h-96">
                    <div class="absolute inset-0 rounded-full" style="background:linear-gradient(135deg,var(--rose-pale),var(--gold-pale));"></div>
                    <div class="absolute inset-4 rounded-full flex items-center justify-center" style="background:white;box-shadow:0 8px 40px rgba(217,30,110,0.1);">
                        <img src="{{ asset('logo_FSL.png') }}" alt="FSL" class="w-40 h-auto opacity-90">
                    </div>

                    {{-- Badge flottant --}}
                    <div class="absolute -bottom-4 -left-6 bg-white rounded-2xl px-4 py-3 shadow-lg" style="border:1px solid var(--border);">
                        <p class="text-xs font-semibold mb-0.5" style="color:var(--rose);">Nouveau membre</p>
                        <p class="text-sm font-bold" style="color:var(--dark);">Aïcha Koné</p>
                        <p class="text-xs" style="color:var(--gray);">Abidjan, Côte d'Ivoire</p>
                    </div>

                    {{-- Badge flottant 2 --}}
                    <div class="absolute -top-4 -right-4 bg-white rounded-2xl px-4 py-3 shadow-lg" style="border:1px solid var(--border);">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0" style="background:var(--gold);">★</div>
                            <div>
                                <p class="text-xs font-semibold" style="color:var(--dark);">Gold Member</p>
                                <p class="text-xs" style="color:var(--gray);">Dakar, Sénégal</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ══════════════════ STATS BAND ══════════════════ --}}
<section style="background:var(--dark);">
    <div class="max-w-7xl mx-auto px-5 lg:px-8 py-10">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-0 divide-y-2 lg:divide-y-0 lg:divide-x divide-white/10">
            @foreach([
                ['500+', 'Femmes accompagnées', 'et en croissance chaque jour'],
                ['15+',  'Pays représentés',     'en Afrique et dans la diaspora'],
                ['50+',  'Événements organisés', 'panels, formations, masterclasses'],
                ['5',    'Ans d\'existence',      'un mouvement ancré dans la durée'],
            ] as [$num, $title, $sub])
            <div class="text-center lg:px-8 py-4 lg:py-0">
                <p class="text-4xl font-bold mb-1 text-white" style="font-family:'Playfair Display',serif;">{{ $num }}</p>
                <p class="text-sm font-semibold text-white/80 mb-0.5">{{ $title }}</p>
                <p class="text-xs text-white/40">{{ $sub }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════ MISSION ══════════════════ --}}
<section class="py-24 lg:py-32" style="background:var(--warm);">
    <div class="max-w-5xl mx-auto px-5 lg:px-8 text-center">
        <span class="section-label fade-up">Notre raison d'être</span>
        <h2 class="text-4xl lg:text-5xl font-bold mt-3 mb-8 fade-up" style="color:var(--dark);font-family:'Playfair Display',serif;">
            Chaque femme porte en elle<br class="hidden sm:block">
            une <em style="color:var(--rose);">puissance illimitée</em>
        </h2>
        <div class="gold-divider mx-auto mb-10 fade-up"></div>
        <p class="text-lg leading-relaxed mb-6 fade-up" style="color:var(--gray);max-width:700px;margin-left:auto;margin-right:auto;">
            Femmes Sans Limites est née d'une conviction profonde : les femmes d'Afrique et de la diaspora portent un potentiel immense, trop souvent bridé par des barrières mentales, sociales et culturelles. Notre mission est de briser ces barrières.
        </p>
        <p class="text-lg leading-relaxed fade-up" style="color:var(--gray);max-width:700px;margin-left:auto;margin-right:auto;">
            Nous créons des espaces sûrs et stimulants où les femmes se connectent à leur puissance authentique, développent leurs compétences et prennent leur place avec confiance — dans leur carrière, leurs affaires et leur vie.
        </p>
    </div>
</section>

{{-- ══════════════════ PILIERS ══════════════════ --}}
<section class="py-24 lg:py-32 bg-white">
    <div class="max-w-7xl mx-auto px-5 lg:px-8">
        <div class="text-center mb-16 fade-up">
            <span class="section-label">Nos domaines d'action</span>
            <h2 class="text-4xl lg:text-5xl font-bold mt-3" style="color:var(--dark);font-family:'Playfair Display',serif;">4 piliers pour te transformer</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach([
                [
                    'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z"/></svg>',
                    'title' => 'Développement Personnel',
                    'desc' => 'Libère tes blocages, renforce ta confiance et développe un mindset de leader.',
                    'items' => ['Coaching individuel & collectif', 'Ateliers de leadership', 'Développement de la confiance', 'Gestion du stress & résilience'],
                    'color' => 'var(--rose)',
                    'bg' => 'var(--rose-pale)',
                ],
                [
                    'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>',
                    'title' => 'Formation & Compétences',
                    'desc' => 'Acquiers des outils concrets pour performer dans ton domaine.',
                    'items' => ['Masterclasses sectorielles', 'E-learning & formations en ligne', 'Certification de compétences', 'Mentorat professionnel'],
                    'color' => 'var(--gold)',
                    'bg' => 'var(--gold-pale)',
                ],
                [
                    'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605"/></svg>',
                    'title' => 'Leadership & Entrepreneuriat',
                    'desc' => 'Prends ta place de leader et développe ton indépendance économique.',
                    'items' => ['Panels avec des femmes leaders', 'Réseau d\'entrepreneures', 'Accès aux investisseurs', 'Visibilité & personal branding'],
                    'color' => 'var(--rose)',
                    'bg' => 'var(--rose-pale)',
                ],
                [
                    'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>',
                    'title' => 'Bien-être & Équilibre',
                    'desc' => 'Prends soin de toi pour mieux rayonner dans tous les aspects de ta vie.',
                    'items' => ['Santé mentale & bien-être', 'Équilibre vie pro & perso', 'Nutrition & sport', 'Cercles de partage & sororité'],
                    'color' => 'var(--gold)',
                    'bg' => 'var(--gold-pale)',
                ],
            ] as $pilier)
            <div class="card-pillar fade-up group">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-5 transition-all duration-300" style="background:{{ $pilier['bg'] }};color:{{ $pilier['color'] }};">
                    {!! $pilier['icon'] !!}
                </div>
                <h3 class="text-lg font-bold mb-3" style="color:var(--dark);font-family:'Playfair Display',serif;">{{ $pilier['title'] }}</h3>
                <p class="text-sm leading-relaxed mb-4" style="color:var(--gray);">{{ $pilier['desc'] }}</p>
                <ul class="space-y-1.5">
                    @foreach($pilier['items'] as $item)
                    <li class="flex items-center gap-2 text-xs" style="color:var(--gray);">
                        <span class="w-1 h-1 rounded-full flex-shrink-0" style="background:{{ $pilier['color'] }};"></span>
                        {{ $item }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════ TÉMOIGNAGES ══════════════════ --}}
<section class="py-24 lg:py-32" style="background:var(--dark);">
    <div class="max-w-7xl mx-auto px-5 lg:px-8">
        <div class="text-center mb-16 fade-up">
            <span class="text-xs font-bold uppercase tracking-widest" style="color:var(--rose);">Elles témoignent</span>
            <h2 class="text-4xl font-bold mt-3 text-white" style="font-family:'Playfair Display',serif;">Ce qu'elles disent de FSL</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach([
                [
                    'quote' => 'FSL m\'a donné les outils et la confiance pour lancer mon cabinet de conseil. En moins d\'un an, j\'emploie 8 personnes. Ce réseau a changé ma trajectoire.',
                    'name' => 'Dr. Aminata Sow',
                    'role' => 'Médecin & Consultante',
                    'location' => 'Abidjan, Côte d\'Ivoire',
                    'type' => 'Premium',
                ],
                [
                    'quote' => 'Ce n\'est pas juste un réseau professionnel. C\'est une famille de femmes qui se battent pour les mêmes valeurs. Je me sens moins seule dans mon parcours entrepreneurial.',
                    'name' => 'Khadija Mbaye',
                    'role' => 'CEO & Fondatrice, KM Group',
                    'location' => 'Dakar, Sénégal',
                    'type' => 'Gold',
                ],
                [
                    'quote' => 'Après 10 ans dans le secteur corporate à Paris, FSL m\'a aidée à trouver mon vrai leadership et à revenir aux sources pour construire quelque chose qui m\'appartient vraiment.',
                    'name' => 'Bintou Diarra',
                    'role' => 'Directrice & Business Angel',
                    'location' => 'Paris & Bamako',
                    'type' => 'Premium',
                ],
            ] as $t)
            <div class="fade-up rounded-2xl p-7 flex flex-col" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);">
                <svg class="w-8 h-8 mb-5 flex-shrink-0" style="color:var(--rose);opacity:0.7;" fill="currentColor" viewBox="0 0 24 24"><path d="M4.583 17.321C3.553 16.227 3 15 3 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 01-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179zm10 0C13.553 16.227 13 15 13 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 01-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179z"/></svg>
                <p class="text-sm leading-relaxed flex-1 mb-6" style="color:rgba(255,255,255,0.7);">{{ $t['quote'] }}</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white flex-shrink-0 text-sm" style="background:var(--rose);">{{ substr($t['name'],0,1) }}</div>
                    <div>
                        <p class="text-sm font-semibold text-white">{{ $t['name'] }}</p>
                        <p class="text-xs" style="color:rgba(255,255,255,0.45);">{{ $t['role'] }} · {{ $t['location'] }}</p>
                    </div>
                    <span class="ml-auto text-xs px-2 py-1 rounded-full font-semibold flex-shrink-0" style="background:{{ $t['type']==='Premium' ? 'rgba(217,30,110,0.2)' : 'rgba(201,168,76,0.2)' }};color:{{ $t['type']==='Premium' ? 'var(--rose)' : 'var(--gold)' }};">{{ $t['type'] }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════ ÉVÉNEMENTS ══════════════════ --}}
@if($events->isNotEmpty())
<section class="py-24 lg:py-32 bg-white">
    <div class="max-w-7xl mx-auto px-5 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-12 fade-up">
            <div>
                <span class="section-label">Agenda</span>
                <h2 class="text-4xl font-bold mt-2" style="color:var(--dark);font-family:'Playfair Display',serif;">Prochains événements</h2>
            </div>
            <a href="{{ route('events.index') }}" class="btn-outline text-sm px-6 py-2.5 flex-shrink-0">Tous les événements</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($events as $event)
            <div class="card-hover flex flex-col fade-up overflow-hidden">
                {{-- Image --}}
                <div class="h-44 flex-shrink-0 relative overflow-hidden">
                    @if($event->image)
                    <img src="{{ asset('storage/'.$event->image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
                    @else
                    <div class="w-full h-full" style="background:linear-gradient(135deg,var(--rose-pale),var(--gold-pale));"></div>
                    <img src="{{ asset('logo_FSL.png') }}" alt="" class="absolute inset-0 m-auto w-20 opacity-15">
                    @endif
                    {{-- Badge prix --}}
                    <div class="absolute top-3 left-3">
                        @if($event->is_paid)
                        <span class="text-xs font-semibold px-3 py-1 rounded-full bg-white" style="color:var(--rose);">{{ number_format($event->price,0,',',' ') }} {{ $event->currency }}</span>
                        @else
                        <span class="text-xs font-semibold px-3 py-1 rounded-full bg-white" style="color:var(--gold-dark);">Gratuit</span>
                        @endif
                    </div>
                </div>
                {{-- Content --}}
                <div class="p-5 flex flex-col flex-1">
                    <div class="flex items-center gap-2 text-xs mb-3" style="color:var(--gray);">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ $event->event_date->isoFormat('D MMM YYYY [·] HH[h]mm') }}
                        <span class="mx-1">·</span>
                        <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ $event->city }}
                    </div>
                    <h3 class="text-lg font-bold mb-2 flex-1" style="color:var(--dark);font-family:'Playfair Display',serif;">{{ $event->title }}</h3>
                    <p class="text-sm leading-relaxed mb-5" style="color:var(--gray);">{{ Str::limit($event->short_description ?? $event->description, 90) }}</p>
                    @if($event->is_sold_out)
                    <div class="btn-outline w-full text-sm py-2.5 cursor-not-allowed opacity-50 text-center rounded-full">Complet</div>
                    @else
                    <a href="{{ route('events.show', $event->slug) }}" class="btn-rose w-full text-sm py-2.5">S'inscrire →</a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ══════════════════ COMMENT ÇA MARCHE ══════════════════ --}}
<section class="py-24 lg:py-32" style="background:var(--warm);">
    <div class="max-w-7xl mx-auto px-5 lg:px-8">
        <div class="text-center mb-16 fade-up">
            <span class="section-label">Comment ça marche</span>
            <h2 class="text-4xl font-bold mt-3" style="color:var(--dark);font-family:'Playfair Display',serif;">Rejoindre FSL en 3 étapes</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12">
            @foreach([
                ['01', 'Soumets ta candidature', 'Remplis le formulaire en ligne avec tes informations. Ton profil est examiné par notre équipe sous 48h ouvrées.', 'var(--rose)'],
                ['02', 'Validation & activation', 'Notre équipe valide ton profil et t\'envoie ta carte membre numérique personnalisée par email.', 'var(--gold)'],
                ['03', 'Intègre la communauté', 'Accède aux événements, au réseau et aux ressources exclusives. Commence ton voyage de transformation.', 'var(--rose)'],
            ] as [$num, $title, $desc, $color])
            <div class="fade-up text-center lg:text-left">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-bold text-white text-lg mx-auto lg:mx-0 mb-6" style="background:{{ $color }};font-family:'Playfair Display',serif;">{{ $num }}</div>
                <h3 class="text-xl font-bold mb-3" style="color:var(--dark);font-family:'Playfair Display',serif;">{{ $title }}</h3>
                <p class="text-sm leading-relaxed" style="color:var(--gray);">{{ $desc }}</p>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-14 fade-up">
            <button @click="$store.modal.join = true" class="btn-rose text-base px-10 py-4">
                Commencer maintenant
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </button>
        </div>
    </div>
</section>

{{-- ══════════════════ CTA FINAL ══════════════════ --}}
<section class="py-24" style="background:var(--rose);">
    <div class="max-w-3xl mx-auto px-5 lg:px-8 text-center fade-up">
        <h2 class="text-4xl lg:text-5xl font-bold text-white mb-5" style="font-family:'Playfair Display',serif;">
            Ton heure est venue.
        </h2>
        <p class="text-lg mb-10" style="color:rgba(255,255,255,0.8);">
            Rejoins des centaines de femmes qui ont décidé de ne plus subir leurs limites — mais de les transcender.
        </p>
        <div class="flex flex-wrap items-center justify-center gap-4">
            <button @click="$store.modal.join = true" class="btn-dark text-base px-10 py-4">
                Rejoindre la communauté
            </button>
            <a href="{{ route('about') }}" class="text-base font-semibold text-white/80 hover:text-white transition-colors underline underline-offset-4">
                En savoir plus
            </a>
        </div>
    </div>
</section>

@endsection
