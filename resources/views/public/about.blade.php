@extends('layouts.public')
@section('title', 'À propos — Femmes Sans Limites')
@section('description', 'Découvrez l\'histoire, la mission et les valeurs de Femmes Sans Limites, la plateforme d\'empowerment féminin.')

@section('content')

{{-- ══════════════════ HERO ══════════════════ --}}
<section class="relative bg-white overflow-hidden" style="min-height:72vh;">

    {{-- Fond diagonal warm --}}
    <div class="absolute top-0 right-0 bottom-0 hidden lg:block pointer-events-none" style="width:45%;background:var(--warm);clip-path:polygon(20% 0,100% 0,100% 100%,0% 100%);"></div>

    <div class="relative max-w-7xl mx-auto px-5 lg:px-8 w-full">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-0 items-center" style="min-height:72vh;padding-top:6rem;padding-bottom:3rem;">

            {{-- Texte --}}
            <div class="relative z-10 lg:pr-12">
                <span class="section-label fade-up">Notre histoire</span>
                <h1 class="text-5xl lg:text-6xl font-bold mt-4 mb-6 leading-tight fade-up" style="color:var(--dark);font-family:'Playfair Display',serif;">
                    Nées pour<br>
                    <em style="color:var(--rose);">briser les limites</em>
                </h1>
                <p class="text-xl leading-relaxed mb-8 fade-up" style="color:var(--gray);max-width:520px;">
                    FSL est bien plus qu'une association. C'est un mouvement de femmes décidées à transformer leur vie, leur carrière et leur impact sur le monde.
                </p>
                <div class="flex flex-wrap gap-4 fade-up">
                    <button @click="$store.modal.join = true" class="btn-rose px-8 py-3.5">Rejoindre le mouvement</button>
                    <a href="{{ route('events.index') }}" class="btn-outline px-8 py-3.5">Nos événements</a>
                </div>
            </div>

            {{-- Photos --}}
            <div class="relative fade-up" style="height:480px;">
                {{-- Déco --}}
                <div class="absolute rounded-full pointer-events-none" style="top:0;right:0;width:220px;height:220px;background:var(--gold-pale);z-index:0;"></div>

                {{-- Photo principale --}}
                <div class="absolute rounded-3xl overflow-hidden shadow-2xl" style="top:0;right:0;left:50px;bottom:80px;z-index:1;">
                    <img src="{{ asset('images/photo_01_3.png') }}" alt="Femme FSL" class="w-full h-full object-cover object-top">
                    <div class="absolute inset-0" style="background:linear-gradient(to top,rgba(15,10,12,0.4) 0%,transparent 50%);"></div>
                </div>

                {{-- Photo secondaire --}}
                <div class="absolute rounded-2xl overflow-hidden shadow-xl" style="bottom:0;left:0;width:42%;height:46%;z-index:2;border:4px solid white;">
                    <img src="{{ asset('images/photo_03_1.png') }}" alt="Membre FSL" class="w-full h-full object-cover object-top">
                </div>

                {{-- Badge fondation --}}
                <div class="absolute bg-white rounded-2xl px-4 py-3 shadow-xl" style="top:16px;left:0;z-index:3;border:1px solid var(--border);">
                    <p class="text-xs font-bold uppercase tracking-widest" style="color:var(--rose);">Fondée en 2020</p>
                    <p class="text-base font-bold" style="color:var(--dark);font-family:'Playfair Display',serif;">5 ans d'impact</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════ HISTOIRE ══════════════════ --}}
<section class="py-24 lg:py-32" style="background:var(--warm);">
    <div class="max-w-7xl mx-auto px-5 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
        <div class="fade-up">
            <span class="section-label">Depuis 2020</span>
            <h2 class="text-4xl font-bold mt-3 mb-8" style="color:var(--dark);font-family:'Playfair Display',serif;">Une conviction devenue mouvement</h2>
            <div class="space-y-5" style="color:var(--gray);">
                <p class="text-base leading-relaxed">Femmes Sans Limites est née d'une conviction profonde : chaque femme porte en elle une puissance infinie, trop souvent étouffée par des barrières mentales, sociales et culturelles.</p>
                <p class="text-base leading-relaxed">Ce qui a commencé comme un petit cercle d'échange entre femmes ambitieuses est devenu en quelques années une plateforme présente dans plus de 15 pays d'Afrique et de la diaspora, avec plus de 500 femmes actives dans la communauté.</p>
                <p class="text-base leading-relaxed">Chaque événement, chaque formation, chaque rencontre est conçu pour créer des déclics, ouvrir des possibilités et connecter des femmes qui se soutiennent mutuellement vers l'excellence.</p>
            </div>
        </div>

        {{-- Timeline --}}
        <div class="fade-up">
            <div class="space-y-0">
                @foreach([
                    ['2020','Fondation de FSL','Premier cercle de 12 femmes entrepreneurs à Abidjan. Le mouvement prend naissance.'],
                    ['2021','Premier Forum','Organisation du premier Forum des Femmes Leadères avec 150 participantes. Succès immédiat.'],
                    ['2022','Expansion régionale','Ouverture de communautés au Sénégal, Mali et Burkina Faso. 200+ membres actives.'],
                    ['2023','Lancement digital','Plateforme en ligne, mentorat à distance et événements hybrides. La diaspora rejoint le mouvement.'],
                    ['2024','Programme Gold & Premium','Création des niveaux d\'adhésion avancés avec accompagnement personnalisé.'],
                    ['2025','Aujourd\'hui','500+ femmes, 15+ pays, 50+ événements. Le mouvement continue de grandir.'],
                ] as [$year,$title,$desc])
                <div class="flex gap-5 pb-8 last:pb-0">
                    <div class="flex flex-col items-center">
                        <div class="w-3 h-3 rounded-full flex-shrink-0 mt-1" style="background:var(--rose);"></div>
                        <div class="w-px flex-1 mt-2" style="background:var(--border);"></div>
                    </div>
                    <div class="pb-2">
                        <span class="text-xs font-bold" style="color:var(--rose);">{{ $year }}</span>
                        <h4 class="text-base font-semibold mt-0.5 mb-1" style="color:var(--dark);">{{ $title }}</h4>
                        <p class="text-sm leading-relaxed" style="color:var(--gray);">{{ $desc }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════ MISSION & VISION ══════════════════ --}}
<section class="py-24 lg:py-32 bg-white">
    <div class="max-w-7xl mx-auto px-5 lg:px-8">
        <div class="text-center mb-16 fade-up">
            <span class="section-label">Ce qui nous guide</span>
            <h2 class="text-4xl font-bold mt-3" style="color:var(--dark);font-family:'Playfair Display',serif;">Mission & Vision</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="card-hover p-8 fade-up">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-6" style="background:var(--rose-pale);">
                    <svg class="w-6 h-6" style="color:var(--rose);" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/></svg>
                </div>
                <h3 class="text-2xl font-bold mb-4" style="color:var(--dark);font-family:'Playfair Display',serif;">Notre Mission</h3>
                <p class="text-base leading-relaxed" style="color:var(--gray);">Créer un écosystème bienveillant et exigeant où les femmes d'Afrique et de la diaspora développent leur plein potentiel — à travers l'éducation, le networking, le mentorat et l'action collective.</p>
            </div>
            <div class="card-hover p-8 fade-up">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-6" style="background:var(--gold-pale);">
                    <svg class="w-6 h-6" style="color:var(--gold);" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="text-2xl font-bold mb-4" style="color:var(--dark);font-family:'Playfair Display',serif;">Notre Vision</h3>
                <p class="text-base leading-relaxed" style="color:var(--gray);">Un continent africain où les femmes sont aux premières loges de leur destin — économiquement indépendantes, politiquement engagées, et socialement reconnues pour leur contribution au développement.</p>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════ VALEURS ══════════════════ --}}
<section class="py-24 lg:py-32" style="background:var(--warm);">
    <div class="max-w-7xl mx-auto px-5 lg:px-8">
        <div class="text-center mb-16 fade-up">
            <span class="section-label">Ce en quoi nous croyons</span>
            <h2 class="text-4xl font-bold mt-3" style="color:var(--dark);font-family:'Playfair Display',serif;">Nos valeurs fondamentales</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach([
                ['Authenticité','Être pleinement et courageusement soi-même, sans masque ni concession sur ses valeurs.','#D91E6E','var(--rose-pale)','✦'],
                ['Excellence','Viser toujours le meilleur de soi — dans son travail, ses relations et son impact.','#C9A84C','var(--gold-pale)','★'],
                ['Solidarité','S\'élever ensemble. Le succès des unes renforce celui de toutes.','#D91E6E','var(--rose-pale)','❤'],
                ['Impact','Agir concrètement, mesurer ses résultats et laisser une trace durable dans son milieu.','#C9A84C','var(--gold-pale)','◆'],
            ] as [$val,$desc,$col,$bg,$icon])
            <div class="fade-up rounded-2xl p-7 bg-white" style="border:1px solid var(--border);">
                <div class="text-2xl mb-4" style="color:{{ $col }};">{{ $icon }}</div>
                <h3 class="text-xl font-bold mb-3" style="color:var(--dark);font-family:'Playfair Display',serif;">{{ $val }}</h3>
                <p class="text-sm leading-relaxed" style="color:var(--gray);">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════ LA FONDATRICE ══════════════════ --}}
<section class="py-24 lg:py-32 bg-white">
    <div class="max-w-7xl mx-auto px-5 lg:px-8">
        <div class="text-center mb-14 fade-up">
            <span class="section-label">À la tête du mouvement</span>
            <h2 class="text-4xl font-bold mt-3" style="color:var(--dark);font-family:'Playfair Display',serif;">La fondatrice</h2>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">

            {{-- Photo fondatrice --}}
            <div class="flex justify-center fade-up">
                <div class="relative">
                    {{-- Déco --}}
                    <div class="absolute -top-5 -left-5 w-32 h-32 rounded-full pointer-events-none" style="background:var(--rose-pale);z-index:0;"></div>
                    <div class="absolute -bottom-5 -right-5 w-24 h-24 rounded-full pointer-events-none" style="background:var(--gold-pale);z-index:0;"></div>

                    {{-- Photo --}}
                    <div class="relative rounded-3xl overflow-hidden shadow-2xl" style="width:340px;height:400px;z-index:1;">
                        <img src="{{ asset('images/photo_03_2.png') }}" alt="Fondatrice FSL" class="w-full h-full object-cover object-top">
                        <div class="absolute inset-0" style="background:linear-gradient(to top,rgba(15,10,12,0.3),transparent 60%);"></div>
                    </div>

                    {{-- Badge --}}
                    <div class="absolute -bottom-6 left-1/2 -translate-x-1/2 bg-white rounded-2xl px-5 py-3.5 shadow-xl text-center" style="border:1px solid var(--border);z-index:2;white-space:nowrap;">
                        <p class="text-xs font-semibold" style="color:var(--rose);">Fondatrice & CEO</p>
                        <p class="text-base font-bold" style="color:var(--dark);font-family:'Playfair Display',serif;">Femmes Sans Limites</p>
                    </div>
                </div>
            </div>

            {{-- Bio --}}
            <div class="fade-up">
                <blockquote class="text-xl italic font-medium mb-8 leading-relaxed" style="color:var(--dark);font-family:'Playfair Display',serif;border-left:3px solid var(--rose);padding-left:1.5rem;">
                    « J'ai créé FSL parce que j'ai été cette femme qui se cherche — brillante mais bridée. Je sais qu'avec le bon entourage, les bonnes ressources et la bonne énergie, chaque femme peut dépasser ses peurs et vivre sa version la plus grande. »
                </blockquote>
                <p class="text-base leading-relaxed mb-5" style="color:var(--gray);">
                    Coach certifiée, conférencière internationale et entrepreneur en série, la fondatrice de FSL cumule 15 ans d'expérience en leadership féminin, développement organisationnel et transformation personnelle.
                </p>
                <p class="text-base leading-relaxed mb-8" style="color:var(--gray);">
                    Elle a accompagné plus de 1 000 femmes à travers ses programmes, conférences et coachings individuels, et continue à porter haut la vision d'un monde où les femmes africaines prennent toute leur place.
                </p>
                {{-- Métriques --}}
                <div class="grid grid-cols-3 gap-5 pt-6" style="border-top:1px solid var(--border);">
                    @foreach([['15 ans','d\'expérience'],['1 000+','femmes coachées'],['30+','pays visités']]) as [$n,$l])
                    <div>
                        <p class="text-2xl font-bold" style="color:var(--rose);font-family:'Playfair Display',serif;">{{ $n }}</p>
                        <p class="text-xs mt-1" style="color:var(--gray);">{{ $l }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════ GALERIE COMMUNAUTÉ ══════════════════ --}}
<section class="py-10 overflow-hidden" style="background:var(--warm);">
    <div class="max-w-7xl mx-auto px-5 lg:px-8 mb-6 fade-up">
        <span class="section-label">Notre communauté</span>
    </div>
    <div class="flex gap-4 px-5 lg:px-8 overflow-x-auto pb-4 snap-x snap-mandatory no-scrollbar">
        @foreach(['photo_02_2.png','photo_01_1.png','photo_03_3.png','photo_01_2.png','photo_03_4.png','photo_02_1.png']) as $img)
        <div class="snap-start flex-shrink-0 rounded-2xl overflow-hidden" style="width:280px;height:200px;">
            <img src="{{ asset('images/'.$img) }}" alt="FSL community" class="w-full h-full object-cover">
        </div>
        @endforeach
    </div>
</section>

{{-- ══════════════════ IMPACT ══════════════════ --}}
<section class="py-20" style="background:var(--dark);">
    <div class="max-w-7xl mx-auto px-5 lg:px-8">
        <div class="text-center mb-12 fade-up">
            <span class="text-xs font-bold uppercase tracking-widest" style="color:var(--gold);">Nos résultats</span>
            <h2 class="text-3xl font-bold mt-2 text-white" style="font-family:'Playfair Display',serif;">L'impact en chiffres</h2>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
            @foreach([
                ['500+','Femmes accompagnées'],
                ['15+','Pays représentés'],
                ['50+','Événements organisés'],
                ['1 000+','Heures de formation'],
            ] as [$n,$l])
            <div class="text-center fade-up">
                <p class="text-4xl font-bold mb-1" style="color:var(--rose);font-family:'Playfair Display',serif;">{{ $n }}</p>
                <p class="text-sm" style="color:rgba(255,255,255,0.5);">{{ $l }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════ CTA ══════════════════ --}}
<section class="py-24 bg-white">
    <div class="max-w-2xl mx-auto px-5 text-center fade-up">
        <div class="gold-divider mx-auto mb-8"></div>
        <h2 class="text-4xl font-bold mb-5" style="color:var(--dark);font-family:'Playfair Display',serif;">Rejoins le mouvement</h2>
        <p class="text-lg mb-10" style="color:var(--gray);">Prête à faire partie d'une communauté de femmes qui se soutiennent, s'élèvent et construisent ensemble ?</p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <button @click="$store.modal.join = true" class="btn-rose text-base px-10 py-4">Devenir membre</button>
            <a href="{{ route('events.index') }}" class="btn-outline text-base px-10 py-4">Voir nos événements</a>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush
