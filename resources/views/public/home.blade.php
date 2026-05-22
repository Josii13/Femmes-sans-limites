@extends('layouts.public')
@section('title', 'Femmes Sans Limites — Plateforme d\'empowerment féminin')
@section('description', 'FSL connecte, forme et inspire les femmes d\'Afrique et de la diaspora pour révéler leur pleine puissance.')
@section('og_type', 'website')

@push('seo')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "WebSite",
  "name": "Femmes Sans Limites",
  "url": "{{ config('app.url') }}",
  "description": "FSL connecte, forme et inspire les femmes d'Afrique et de la diaspora pour révéler leur pleine puissance.",
  "potentialAction": {
    "@@type": "SearchAction",
    "target": "{{ config('app.url') }}/events?q={search_term_string}",
    "query-input": "required name=search_term_string"
  }
}
</script>
@endpush

@section('content')

{{-- ══════════════════ HERO ══════════════════ --}}
<section class="relative bg-white overflow-hidden" style="min-height:92vh;">

    {{-- Fond diagonal warm (visible desktop) --}}
    <div class="absolute top-0 right-0 bottom-0 hidden lg:block pointer-events-none" style="width:48%;background:var(--warm);clip-path:polygon(18% 0,100% 0,100% 100%,0% 100%);"></div>

    {{-- Titre mobile uniquement (avant l'image) --}}
    <div class="lg:hidden relative px-5 pt-24 pb-4">
        <h1 class="font-bold fade-up" style="font-family:'Playfair Display',serif;color:var(--dark);font-size:clamp(2.6rem,10vw,3.5rem);line-height:1.08;letter-spacing:-0.02em;">
            Révèle ta<br>
            <em style="color:var(--rose);">puissance</em><br>
            intérieure.
        </h1>
    </div>

    <div class="relative max-w-7xl mx-auto px-5 lg:px-8 w-full">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-0 items-center" style="min-height:92vh;padding-top:0;padding-bottom:3rem;">

            {{-- Gauche : texte éditorial --}}
            <div class="relative z-10 lg:pr-14 order-2 lg:order-1 lg:pt-20">

                {{-- Titre desktop uniquement --}}
                <h1 class="hidden lg:block font-bold mb-8 fade-up" style="font-family:'Playfair Display',serif;color:var(--dark);font-size:clamp(2.8rem,5.5vw,5rem);line-height:1.08;letter-spacing:-0.02em;">
                    Révèle ta<br>
                    <em style="color:var(--rose);">puissance</em><br>
                    intérieure.
                </h1>

                <p class="text-base font-semibold italic mb-3 fade-up" style="color:var(--rose);max-width:460px;">
                    Brise les barrières invisibles. Révèle pleinement ton potentiel.
                </p>
                <p class="text-lg leading-relaxed mb-10 fade-up" style="color:var(--gray);max-width:460px;">
                    Femme Sans Limites est une communauté de transformation qui accompagne les femmes afin de les aider à dépasser leurs limites intérieures et devenir la meilleure version d'elles-mêmes.
                </p>

                <div class="flex flex-wrap gap-4 mb-14 fade-up">
                    <button @click="$store.modal.join = true" class="btn-rose text-base px-10 py-4">
                        Rejoindre la communauté
                    </button>
                    <a href="{{ route('events.index') }}" class="btn-outline text-base px-10 py-4">
                        Voir les événements →
                    </a>
                </div>

                {{-- Stats --}}
                <!-- <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 fade-up">
                    @foreach([['500+','Membres'],['15+','Pays'],['50+','Événements'],['5 ans','D\'impact']] as [$n,$l])
                    <div>
                        <p class="text-3xl font-bold" style="color:var(--dark);font-family:'Playfair Display',serif;">{{ $n }}</p>
                        <p class="text-xs mt-1" style="color:var(--gray);">{{ $l }}</p>
                    </div>
                    @endforeach
                </div> -->
            </div>

            {{-- Droite : collage photo --}}
            <div class="relative scale-up lg:mt-0 order-1 lg:order-2" style="height:560px;">

                {{-- Cercles déco animés --}}
                <div class="absolute rounded-full pointer-events-none spin-slow" style="top:8px;right:8px;width:260px;height:260px;background:conic-gradient(var(--gold-pale),var(--rose-pale),var(--gold-pale));opacity:0.6;z-index:0;"></div>
                <div class="absolute rounded-full pointer-events-none" style="bottom:60px;left:4px;width:110px;height:110px;background:var(--rose-pale);z-index:0;"></div>

                {{-- Photo principale avec parallax --}}
                <div class="absolute rounded-3xl overflow-hidden shadow-2xl img-reveal" style="top:0;right:0;left:55px;bottom:95px;z-index:1;">
                    <img src="{{ site_img('home_hero_main') }}" alt="Membre FSL en pleine conférence" class="w-full h-full object-cover object-top" data-parallax="0.08">
                    <div class="absolute inset-x-0 bottom-0 h-2/5" style="background:linear-gradient(to top,rgba(15,10,12,0.55),transparent);"></div>
                    <div class="absolute bottom-5 left-5">
                        <p class="text-xs font-semibold uppercase tracking-widest mb-0.5" style="color:rgba(255,255,255,0.65);">Membre FSL</p>
                        <p class="text-base font-bold text-white" style="font-family:'Playfair Display',serif;">Leader & Entrepreneur</p>
                    </div>
                </div>

                {{-- Photo secondaire --}}
                <div class="absolute rounded-2xl overflow-hidden shadow-xl img-reveal" style="bottom:0;left:0;width:44%;height:48%;z-index:2;border:4px solid white;" data-delay="200">
                    <img src="{{ site_img('home_hero_secondary') }}" alt="Membre FSL — leader et entrepreneur" class="w-full h-full object-cover object-top">
                </div>

                {{-- Badge membres flottant --}}
                <!-- <div class="float-badge absolute bg-white rounded-2xl shadow-xl px-4 py-3" style="top:12px;left:0;z-index:3;border:1px solid var(--border);">
                    <div class="flex items-center gap-3">
                        <div class="flex -space-x-2">
                            <img src="{{ asset('images/photo_01_2.png') }}" class="w-8 h-8 rounded-full object-cover object-top border-2 border-white" alt="">
                            <img src="{{ asset('images/photo_03_4.png') }}" class="w-8 h-8 rounded-full object-cover object-top border-2 border-white" alt="">
                            <img src="{{ asset('images/photo_03_1.png') }}" class="w-8 h-8 rounded-full object-cover object-top border-2 border-white" alt="">
                        </div>
                        <div>
                            <p class="text-xs font-bold" style="color:var(--dark);">+500 membres</p>
                            <p class="text-xs" style="color:var(--rose);">actives ce mois ✦</p>
                        </div>
                    </div>
                </div> -->

                {{-- Badge pays flottant --}}
                <!-- <div class="float-badge2 absolute bg-white rounded-xl shadow-lg px-3 py-2.5" style="z-index:3;border:1px solid var(--border);bottom:46%;right:0;transform:translateY(50%);">
                    <p class="text-sm font-bold" style="color:var(--rose);font-family:'Playfair Display',serif;">15+</p>
                    <p class="text-xs" style="color:var(--gray);">pays représentés</p>
                </div> -->
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════ BANDE CHIFFRES ══════════════════ --}}
<!-- <section class="py-14" style="background:var(--dark);">
    <div class="max-w-7xl mx-auto px-5 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8" data-stagger="120">
            @foreach([
                ['500', '+', 'Femmes accompagnées','var(--rose)'],
                ['15',  '+', 'Pays d\'Afrique & diaspora','var(--gold)'],
                ['50',  '+', 'Événements organisés','var(--rose)'],
                ['1000','+', 'Heures de formation','var(--gold)'],
            ] as [$target,$suffix,$l,$c])
            <div class="text-center fade-up">
                <p class="text-4xl font-bold mb-2 counter" data-target="{{ $target }}" data-suffix="{{ $suffix }}" style="color:{{ $c }};font-family:'Playfair Display',serif;">{{ $target }}{{ $suffix }}</p>
                <p class="text-sm" style="color:rgba(255,255,255,0.45);">{{ $l }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section> -->

{{-- ══════════════════ COMMUNAUTÉ ══════════════════ --}}
<section class="py-20 lg:py-28 overflow-hidden" style="background:var(--warm);">
    <div class="max-w-7xl mx-auto px-5 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">

            {{-- Photo groupe --}}
            <div class="relative fade-left order-1">
                <div class="rounded-3xl overflow-hidden shadow-xl img-reveal" style="aspect-ratio:4/3;">
                    <img src="{{ site_img('home_adn_group') }}" alt="Femmes de la communauté FSL réunies" loading="lazy" class="w-full h-full object-cover">
                    <div class="absolute inset-0" style="background:linear-gradient(135deg,rgba(217,30,110,0.12),transparent 60%);"></div>
                </div>
                <div class="float-badge2 absolute -bottom-5 -right-4 lg:-right-6 bg-white rounded-2xl px-5 py-4 shadow-xl" style="border:1px solid var(--border);z-index:2;">
                    <p class="text-xs font-bold uppercase tracking-widest mb-1" style="color:var(--rose);">La force du collectif</p>
                    <p class="text-lg font-bold" style="color:var(--dark);font-family:'Playfair Display',serif;">S'élever ensemble</p>
                </div>
                <div class="absolute -top-5 -left-5 w-20 h-20 rounded-full pointer-events-none" style="background:var(--rose-pale);z-index:-1;"></div>
            </div>

            {{-- Texte --}}
            <div class="order-2 fade-right">
                <span class="section-label">Notre ADN</span>
                <h2 class="text-4xl lg:text-5xl font-bold mt-4 mb-8 leading-tight" style="color:var(--dark);font-family:'Playfair Display',serif;">
                    Plus qu'une communauté.<br>
                    <em style="color:var(--rose);">Un mouvement.</em>
                </h2>
                <p class="text-base font-semibold mb-3" style="color:var(--dark);">
                    Plus qu'une communauté, nous sommes une famille.
                </p>
                <p class="text-base leading-relaxed mb-6" style="color:var(--gray);">
                    Aujourd'hui, trouver un espace bienveillant où des femmes avancent ensemble, sans jalousie ni compétition malsaine, est devenu rare. Avec Femme Sans Limites, chaque femme a l'opportunité de grandir, apprendre, se reconstruire et se challenger aux côtés d'autres femmes dans un environnement sain, inspirant et motivant. Ici, nous croyons qu'une femme élevée peut élever une autre.
                </p>
                <ul class="space-y-3 mb-10">
                    @foreach(['Événements et forums exclusifs','Mentorat et coaching personnalisé','Réseau de 500+ femmes leaders','Formations pratiques et actionables'] as $item)
                    <li class="flex items-center gap-3 text-sm font-medium" style="color:var(--dark);">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0" style="background:var(--rose-pale);">
                            <svg class="w-3 h-3" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        {{ $item }}
                    </li>
                    @endforeach
                </ul>
                <div class="flex flex-wrap gap-4">
                    <button @click="$store.modal.join = true" class="btn-rose">Rejoindre FSL</button>
                    <a href="{{ route('about') }}" class="btn-outline">Notre histoire</a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════ PILIERS ══════════════════ --}}
<section class="py-20 lg:py-28 bg-white">
    <div class="max-w-7xl mx-auto px-5 lg:px-8">
        <div class="text-center mb-14 fade-up">
            <span class="section-label">Ce que nous offrons</span>
            <h2 class="text-4xl font-bold mt-3" style="color:var(--dark);font-family:'Playfair Display',serif;">4 piliers de transformation</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5" data-stagger="150">
            @foreach([
                ['Développement personnel','Construire des femmes fortes, conscientes et mentalement solides.','var(--rose-pale)','var(--rose)','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>'],
                ['Formation & Compétences','Donner des outils concrets pour évoluer professionnellement.','var(--gold-pale)','var(--gold)','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>'],
                ['Leadership & Indépendance','Former des femmes influentes, capables de diriger et créer leur liberté.','var(--rose-pale)','var(--rose)','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>'],
                ['Bien-être & Selfcare','Aider les femmes à prendre soin d\'elles-mêmes afin de préserver leur équilibre, leur paix intérieure et leur épanouissement personnel.','var(--gold-pale)','var(--gold)','<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>'],
            ] as [$title,$desc,$bg,$color,$icon])
            <div class="card-pillar fade-up">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-5" style="background:{{ $bg }};">
                    <svg class="w-6 h-6" style="color:{{ $color }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $icon !!}</svg>
                </div>
                <h3 class="text-xl font-bold mb-3" style="color:var(--dark);font-family:'Playfair Display',serif;">{{ $title }}</h3>
                <p class="text-sm leading-relaxed" style="color:var(--gray);">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════ TÉMOIGNAGES ══════════════════ --}}
<section class="py-20 lg:py-28" style="background:var(--dark);">
    <div class="max-w-7xl mx-auto px-5 lg:px-8">
        <div class="text-center mb-14 fade-up">
            <span class="text-xs font-bold uppercase tracking-widest" style="color:var(--gold);">Ce qu'elles disent</span>
            <h2 class="text-4xl font-bold mt-3 text-white" style="font-family:'Playfair Display',serif;">La parole aux membres</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6" data-stagger="120">
            @foreach([
                ['home_temoignage_1','Khadija Mbaye','Entrepreneur · Dakar','FSL a transformé ma façon de voir les affaires. J\'ai rencontré mes deux associées actuelles lors du Forum 2023. Ce réseau est une vraie chance — des femmes qui se soutiennent vraiment, sans jalousie.'],
                ['home_temoignage_2','Dr. Aminata Sow','Médecin · Abidjan','J\'hésitais à me lancer en parallèle à ma carrière médicale. Le mentorat FSL m\'a donné les outils et la confiance. Aujourd\'hui mon cabinet attire 3× plus de patientes grâce à ma visibilité.'],
                ['home_temoignage_3','Bintou Diarra','Cadre finance · Paris','Être dans la diaspora et rester connectée à l\'Afrique, c\'est possible avec FSL. J\'ai trouvé une communauté qui me comprend et m\'inspire à rester ambitieuse, peu importe où je vis.'],
            ] as [$photoKey,$name,$role,$quote])
            <div class="rounded-2xl p-7 flex flex-col fade-up" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);">
                <svg class="w-8 h-8 mb-5 flex-shrink-0" style="color:var(--rose);" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
                <p class="text-sm leading-relaxed flex-1 mb-6" style="color:rgba(255,255,255,0.7);">{{ $quote }}</p>
                <div class="flex items-center gap-3 pt-5" style="border-top:1px solid rgba(255,255,255,0.1);">
                    <img src="{{ site_img($photoKey) }}" alt="{{ $name }}" loading="lazy" class="w-11 h-11 rounded-full object-cover object-top flex-shrink-0">
                    <div>
                        <p class="text-sm font-bold text-white">{{ $name }}</p>
                        <p class="text-xs" style="color:rgba(255,255,255,0.4);">{{ $role }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════ ÉVÉNEMENTS ══════════════════ --}}
@if(isset($events) && $events->isNotEmpty())
<section class="py-20 lg:py-28" style="background:var(--warm);">
    <div class="max-w-7xl mx-auto px-5 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-12 fade-up">
            <div>
                <span class="section-label">Agenda</span>
                <h2 class="text-4xl font-bold mt-3" style="color:var(--dark);font-family:'Playfair Display',serif;">Prochains événements</h2>
            </div>
            <a href="{{ route('events.index') }}" class="btn-outline text-sm whitespace-nowrap self-start sm:self-auto">Voir tout →</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" data-stagger="100">
            @foreach($events->take(3) as $event)
            <article class="card-hover flex flex-col fade-up overflow-hidden group">
                <div class="h-48 relative overflow-hidden flex-shrink-0 img-reveal">
                    @if($event->image)
                    <img src="{{ asset('storage/'.$event->image) }}" alt="{{ $event->title }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                    <div class="w-full h-full flex items-center justify-center" style="background:linear-gradient(135deg,var(--rose-pale),var(--gold-pale));"><img src="{{ asset('logo_FSL.png') }}" alt="" class="w-16 opacity-15"></div>
                    @endif
                    <div class="absolute top-3 left-3">
                        @if($event->is_paid)
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-white shadow-sm" style="color:var(--rose);">{{ number_format($event->price,0,',',' ') }} {{ $event->currency }}</span>
                        @else
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-white shadow-sm" style="color:var(--gold-dark);">Gratuit</span>
                        @endif
                    </div>
                    <div class="absolute top-3 right-3 bg-white rounded-xl shadow-sm px-3 py-1.5 text-center">
                        <p class="text-xs font-bold uppercase" style="color:var(--rose);">{{ $event->event_date->isoFormat('MMM') }}</p>
                        <p class="text-lg font-bold leading-tight" style="color:var(--dark);">{{ $event->event_date->format('d') }}</p>
                    </div>
                </div>
                <div class="p-5 flex flex-col flex-1">
                    <p class="text-xs mb-2" style="color:var(--gray);">{{ $event->location }}{{ $event->city ? ', '.$event->city : '' }}</p>
                    <h3 class="text-lg font-bold mb-2 flex-1" style="color:var(--dark);font-family:'Playfair Display',serif;">{{ $event->title }}</h3>
                    <p class="text-sm leading-relaxed mb-5" style="color:var(--gray);">{{ Str::limit($event->short_description ?? $event->description, 90) }}</p>
                    <div class="flex items-center justify-between pt-4" style="border-top:1px solid var(--border);">
                        <span class="text-xs" style="color:var(--gray);">{{ $event->event_date->isoFormat('HH[h]mm') }}</span>
                        <a href="{{ route('events.show', $event->slug) }}" class="btn-rose text-xs px-4 py-2">S'inscrire →</a>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ══════════════════ COMMENT ÇA MARCHE ══════════════════ --}}
<section class="py-20 lg:py-28 bg-white">
    <div class="max-w-5xl mx-auto px-5 lg:px-8">
        <div class="text-center mb-16 fade-up">
            <span class="section-label">Le parcours</span>
            <h2 class="text-4xl font-bold mt-3" style="color:var(--dark);font-family:'Playfair Display',serif;">Rejoindre FSL en 3 étapes</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8" data-stagger="150">
            @foreach([
                ['01','Tu postules','Remplis le formulaire en 2 minutes. Aucun document requis.'],
                ['02','On valide','Notre équipe active ton compte sous 48h. Tu reçois ta carte de membre FSL.'],
                ['03','Tu t\'élèves','Accède aux événements, formations et au réseau. La communauté t\'attend.'],
            ] as [$n,$title,$desc])
            <div class="text-center fade-up">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-6" style="background:var(--rose);">
                    <span class="text-white font-bold text-lg" style="font-family:'Playfair Display',serif;">{{ $n }}</span>
                </div>
                <h3 class="text-lg font-bold mb-3" style="color:var(--dark);font-family:'Playfair Display',serif;">{{ $title }}</h3>
                <p class="text-sm leading-relaxed" style="color:var(--gray);">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-14 fade-up">
            <button @click="$store.modal.join = true" class="btn-rose text-base px-12 py-4">Postuler maintenant</button>
        </div>
    </div>
</section>

{{-- ══════════════════ CTA FINAL PHOTO ══════════════════ --}}
<section class="relative overflow-hidden" style="min-height:500px;">
    <div class="absolute inset-0">
        <img src="{{ site_img('home_cta_bg') }}" alt="Femmes FSL lors d'un événement communautaire" loading="lazy" class="w-full h-full object-cover object-center">
        <div class="absolute inset-0" style="background:linear-gradient(135deg,rgba(15,10,12,0.85),rgba(217,30,110,0.45));"></div>
    </div>
    <div class="relative max-w-3xl mx-auto px-5 text-center py-28 fade-up">
        <span class="text-xs font-bold uppercase tracking-widest" style="color:rgba(255,255,255,0.55);">Tu brises les barrières</span>
        <h2 class="text-4xl lg:text-5xl font-bold mt-4 mb-6 text-white leading-tight" style="font-family:'Playfair Display',serif;">
            Prête à briser tes limites ?
        </h2>
        <p class="text-lg mb-10 mx-auto" style="color:rgba(255,255,255,0.75);max-width:480px;">
            Les femmes t'attendent, rejoins nous maintenant !
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <button @click="$store.modal.join = true" class="btn-rose text-base px-12 py-4">
                Devenir membre
            </button>
            <a href="{{ route('events.index') }}" class="btn text-base px-12 py-4 text-white" style="border:1.5px solid rgba(255,255,255,0.35);">
                Voir les événements
            </a>
        </div>
    </div>
</section>

@endsection
