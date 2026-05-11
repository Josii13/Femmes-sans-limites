@extends('layouts.public')
@section('title', 'À propos — Femmes Sans Limites')
@section('description', 'Découvrez l\'histoire, la mission et les valeurs de Femmes Sans Limites, la plateforme d\'empowerment féminin.')

@section('content')

{{-- ══════════════════ HERO ══════════════════ --}}
<section class="pt-28 pb-20 bg-white relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 rounded-full pointer-events-none" style="background:radial-gradient(circle,var(--rose-pale),transparent 70%);transform:translate(40%,-40%);"></div>
    <div class="max-w-7xl mx-auto px-5 lg:px-8">
        <div class="max-w-3xl">
            <span class="section-label fade-up">Notre histoire</span>
            <h1 class="text-5xl lg:text-6xl font-bold mt-4 mb-6 fade-up" style="color:var(--dark);font-family:'Playfair Display',serif;">
                Nées pour<br><em style="color:var(--rose);">briser les limites</em>
            </h1>
            <p class="text-xl leading-relaxed fade-up" style="color:var(--gray);max-width:600px;">
                FSL est bien plus qu'une association. C'est un mouvement de femmes décidées à transformer leur vie, leur carrière et leur impact sur le monde.
            </p>
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
                <p class="text-base leading-relaxed">Femmes Sans Limites est née en 2020 d'une conviction profonde : chaque femme porte en elle une puissance infinie, trop souvent étouffée par des barrières mentales, sociales et culturelles que la société lui a imposées depuis l'enfance.</p>
                <p class="text-base leading-relaxed">Ce qui a commencé comme un petit cercle d'échange entre femmes ambitieuses est devenu en quelques années une plateforme reconnue, présente dans plus de 15 pays d'Afrique et de la diaspora, avec plus de 500 femmes actives dans la communauté.</p>
                <p class="text-base leading-relaxed">Chaque événement, chaque formation, chaque rencontre est conçu pour créer des déclics, ouvrir des possibilités et connecter des femmes qui se soutiennent mutuellement vers l'excellence.</p>
            </div>
        </div>

        {{-- Timeline --}}
        <div class="fade-up">
            <div class="space-y-0">
                @foreach([
                    ['2020', 'Fondation de FSL', 'Premier cercle de 12 femmes entrepreneurs à Abidjan. Le mouvement prend naissance.'],
                    ['2021', 'Premier Forum', 'Organisation du premier Forum des Femmes Leadères avec 150 participantes. Succès immédiat.'],
                    ['2022', 'Expansion régionale', 'Ouverture de communautés au Sénégal, Mali et Burkina Faso. 200+ membres actives.'],
                    ['2023', 'Lancement digital', 'Plateforme en ligne, mentorat à distance et événements hybrides. La diaspora rejoint le mouvement.'],
                    ['2024', 'Programme Gold & Premium', 'Création des niveaux d\'adhésion avancés avec accompagnement personnalisé.'],
                    ['2025', 'Aujourd\'hui', '500+ femmes, 15+ pays, 50+ événements. Le mouvement continue de grandir.'],
                ] as [$year, $title, $desc])
                <div class="flex gap-5 pb-8 last:pb-0 relative">
                    <div class="flex flex-col items-center">
                        <div class="w-3 h-3 rounded-full flex-shrink-0 mt-1" style="background:var(--rose);"></div>
                        <div class="w-0.5 flex-1 mt-2" style="background:var(--border);"></div>
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
                ['Authenticité', 'Être pleinement et courageusement soi-même, sans masque ni concession sur ses valeurs.', '#D91E6E', 'var(--rose-pale)', '✦'],
                ['Excellence', 'Viser toujours le meilleur de soi — dans son travail, ses relations et son impact.', '#C9A84C', 'var(--gold-pale)', '★'],
                ['Solidarité', 'S\'élever ensemble. Le succès des unes renforce celui de toutes.', '#D91E6E', 'var(--rose-pale)', '❤'],
                ['Impact', 'Agir concrètement, mesurer ses résultats et laisser une trace durable dans son milieu.', '#C9A84C', 'var(--gold-pale)', '◆'],
            ] as [$val, $desc, $col, $bg, $icon])
            <div class="fade-up rounded-2xl p-7" style="background:white;border:1px solid var(--border);">
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
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-12 fade-up">
                <span class="section-label">À la tête du mouvement</span>
                <h2 class="text-4xl font-bold mt-3" style="color:var(--dark);font-family:'Playfair Display',serif;">La fondatrice</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center fade-up">
                {{-- Photo placeholder --}}
                <div class="flex justify-center">
                    <div class="relative">
                        <div class="w-64 h-64 rounded-3xl" style="background:linear-gradient(135deg,var(--rose-pale),var(--gold-pale));"></div>
                        <div class="absolute inset-4 rounded-2xl flex items-center justify-center" style="background:white;border:1px solid var(--border);">
                            <div class="text-center px-6">
                                <div class="w-16 h-16 rounded-full mx-auto mb-3 flex items-center justify-center text-white text-2xl font-bold" style="background:var(--rose);">F</div>
                                <p class="text-sm font-semibold" style="color:var(--dark);">Fondatrice FSL</p>
                                <p class="text-xs mt-1" style="color:var(--gray);">Coach & Conférencière</p>
                            </div>
                        </div>
                        {{-- Badge --}}
                        <div class="absolute -bottom-4 -right-4 bg-white rounded-xl px-4 py-2.5 shadow-lg" style="border:1px solid var(--border);">
                            <p class="text-xs font-semibold" style="color:var(--rose);">5 ans d'impact</p>
                            <p class="text-sm font-bold" style="color:var(--dark);">500+ vies touchées</p>
                        </div>
                    </div>
                </div>
                {{-- Bio --}}
                <div>
                    <blockquote class="text-xl italic font-medium mb-6 leading-relaxed" style="color:var(--dark);font-family:'Playfair Display',serif;">
                        « J'ai créé FSL parce que j'ai été cette femme qui se cherche — brillante mais bridée. Je sais qu'avec le bon entourage, les bonnes ressources et la bonne énergie, chaque femme peut dépasser ses peurs et vivre sa version la plus grande. »
                    </blockquote>
                    <p class="text-base leading-relaxed mb-6" style="color:var(--gray);">
                        Coach certifiée, conférencière internationale et entrepreneur en série, la fondatrice de FSL cumule 15 ans d'expérience en leadership féminin, développement organisationnel et transformation personnelle.
                    </p>
                    <p class="text-base leading-relaxed" style="color:var(--gray);">
                        Elle a accompagné plus de 1 000 femmes à travers ses programmes, conférences et coachings individuels, et continue à porter haut la vision d'un monde où les femmes africaines prennent toute leur place.
                    </p>
                </div>
            </div>
        </div>
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
                ['500+', 'Femmes accompagnées'],
                ['15+', 'Pays représentés'],
                ['50+', 'Événements organisés'],
                ['1 000+', 'Heures de formation'],
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
        <h2 class="text-4xl font-bold mb-5" style="color:var(--dark);font-family:'Playfair Display',serif;">
            Rejoins le mouvement
        </h2>
        <p class="text-lg mb-10" style="color:var(--gray);">Prête à faire partie d'une communauté de femmes qui se soutiennent, s'élèvent et construisent ensemble ?</p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <button @click="$store.modal.join = true" class="btn-rose text-base px-10 py-4">
                Devenir membre
            </button>
            <a href="{{ route('events.index') }}" class="btn-outline text-base px-10 py-4">
                Voir nos événements
            </a>
        </div>
    </div>
</section>

@endsection
