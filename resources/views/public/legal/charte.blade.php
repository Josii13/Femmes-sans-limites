@extends('layouts.public')
@section('title', 'Règles de la communauté — Femme Sans Limites')
@section('description', 'La charte de la communauté Femme Sans Limites : respect, confidentialité, participation, communication, partage, intégrité et responsabilité.')
@section('og_type', 'article')

@section('content')

<section class="pt-28 pb-16 bg-white relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none" style="background:radial-gradient(ellipse at 30% 50%,var(--rose-pale) 0%,transparent 55%);"></div>
    <div class="max-w-4xl mx-auto px-5 lg:px-8 relative">
        <span class="section-label fade-up">Notre charte</span>
        <h1 class="text-4xl lg:text-5xl font-bold mt-3 mb-5 fade-up delay-100" style="color:var(--dark);font-family:'Playfair Display',serif;">
            Règles de la communauté
        </h1>
        <p class="text-lg leading-relaxed fade-up delay-200" style="color:var(--gray);max-width:620px;">
            Ces sept règles ne sont pas des formalités : elles sont ce qui permet à chaque femme
            de s’exprimer, d’apprendre et d’évoluer dans un environnement sain.
        </p>
    </div>
</section>

<section class="py-16" style="background:var(--warm);">
    <div class="max-w-4xl mx-auto px-5 lg:px-8">
        <div class="bg-white rounded-3xl p-6 sm:p-8 lg:p-12 shadow-sm" style="border:1px solid var(--border);">

            {{--
                Source unique des règles : la page publique, le formulaire d'adhésion et
                l'espace membre lisent tous ce composant. Une règle modifiée ici l'est partout.
            --}}
            <x-community-rules />

            {{-- Engagement --}}
            <div class="mt-10 pt-8 rounded-2xl p-6" style="background:var(--rose-pale);border:1px solid var(--rose-mid);">
                <p class="text-xs font-bold uppercase tracking-widest mb-2" style="color:var(--rose);">Notre engagement</p>
                <p class="text-base leading-relaxed" style="color:var(--dark);">
                    En rejoignant Femme Sans Limites, chaque membre reconnaît avoir pris connaissance
                    de ces règles et s’engage à les respecter, afin de contribuer à un espace où chaque
                    femme peut apprendre, évoluer, s’exprimer et grandir dans un environnement sain.
                </p>
            </div>

            <p class="text-xs mt-6" style="color:var(--gray);">
                Version {{ \App\Support\CommunityRules::VERSION }} · en vigueur depuis le
                {{ \App\Support\CommunityRules::effectiveDate()->translatedFormat('d F Y') }}.
            </p>
        </div>

        {{-- Appel à rejoindre, pour qui découvre la charte avant d'adhérer --}}
        @guest('member')
        <div class="text-center mt-12 fade-up">
            <p class="text-sm mb-4" style="color:var(--gray);">Ces valeurs te parlent ?</p>
            <button @click="$store.modal.join = true" class="btn-rose text-base px-10 py-4">
                Rejoindre la communauté
            </button>
        </div>
        @endguest
    </div>
</section>

@endsection
