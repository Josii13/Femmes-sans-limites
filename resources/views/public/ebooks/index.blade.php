@extends('layouts.public')
@section('title', 'Ebooks — Femme Sans Limites')
@section('description', 'Découvrez les ebooks FSL : leadership, finance, entrepreneuriat, bien-être. Des ressources pensées pour les femmes ambitieuses d\'Afrique et de la diaspora.')
@section('og_type', 'website')

@section('content')

{{-- Hero --}}
<!-- <section class="pt-20 pb-14 bg-white relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none" style="background:radial-gradient(ellipse at 70% 50%,var(--rose-pale) 0%,transparent 55%),radial-gradient(ellipse at 20% 80%,var(--gold-pale) 0%,transparent 50%);"></div>
    <div class="max-w-7xl mx-auto px-5 lg:px-8 relative">
        <div class="max-w-2xl">
            <span class="section-label fade-up">Bibliothèque FSL</span>
            <h1 class="text-5xl lg:text-6xl font-bold mt-3 mb-5 fade-up delay-100" style="color:var(--dark);font-family:'Playfair Display',serif;">
                Nos Ebooks
            </h1>
            <p class="text-lg leading-relaxed fade-up delay-200" style="color:var(--gray);">
                Des ressources concrètes rédigées par et pour des femmes ambitieuses. Leadership, finances, entrepreneuriat, bien-être — choisis ton prochain levier de croissance.
            </p>
        </div>
    </div>
</section> -->

{{-- Filtres par catégorie --}}
@if($categories->isNotEmpty())
<div class="bg-white sticky top-16 z-20" style="border-bottom:1px solid var(--border);">
    <div class="max-w-7xl mx-auto px-5 lg:px-8">
        <div class="flex items-center gap-2 py-3 overflow-x-auto no-scrollbar">
            <a href="{{ route('ebooks.index') }}"
               class="flex-shrink-0 text-sm font-semibold px-4 py-1.5 rounded-full transition-all {{ !request('categorie') ? 'text-white' : 'hover:bg-gray-50' }}"
               style="{{ !request('categorie') ? 'background:var(--rose);' : 'color:var(--gray);' }}">
                Tous
            </a>
            @foreach($categories as $cat)
            <a href="{{ route('ebooks.index', ['categorie' => $cat]) }}"
               class="flex-shrink-0 text-sm font-semibold px-4 py-1.5 rounded-full transition-all {{ request('categorie') === $cat ? 'text-white' : 'hover:bg-gray-50' }}"
               style="{{ request('categorie') === $cat ? 'background:var(--rose);' : 'color:var(--gray);' }}">
                {{ $cat }}
            </a>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Grille ebooks --}}
<section class="py-16 lg:py-20" style="background:var(--warm);">
    <div class="max-w-7xl mx-auto px-5 lg:px-8">

        @if($ebooks->isEmpty())
        <div class="text-center py-24 fade-up">
            <div class="w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-5" style="background:var(--rose-pale);">
                <svg class="w-9 h-9" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <h3 class="text-2xl font-bold mb-3" style="color:var(--dark);font-family:'Playfair Display',serif;">Voir tout</h3>
            <p class="text-base mb-8" style="color:var(--gray);">Nos ebooks arrivent prochainement. Rejoins la communauté pour être notifiée en avant-première !</p>
            <button @click="$store.modal.join = true" class="btn-rose">Rejoindre FSL</button>
        </div>

        @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5 lg:gap-6">
            @foreach($ebooks as $ebook)
            <a href="{{ route('ebooks.show', $ebook->slug) }}" class="group flex flex-col fade-up overflow-hidden rounded-2xl bg-white hover:shadow-xl transition-all duration-300 hover:-translate-y-1" style="border:1px solid var(--border);">

                {{-- Cover --}}
                <div class="relative flex-shrink-0 overflow-hidden rounded-t-2xl" style="aspect-ratio:3/4;">
                    @if($ebook->image)
                    <img src="{{ asset('storage/'.$ebook->image) }}"
                         alt="{{ $ebook->title }}"
                         loading="lazy"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                    <div class="w-full h-full flex flex-col items-center justify-center gap-2 p-4"
                         style="background:linear-gradient(145deg,var(--rose-pale) 0%,var(--gold-pale) 100%);">
                        <svg class="w-8 h-8 opacity-30" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <p class="text-center text-xs font-semibold opacity-40 leading-snug" style="color:var(--dark);">{{ $ebook->title }}</p>
                    </div>
                    @endif

                    {{-- Category badge --}}
                    <div class="absolute top-2.5 left-2.5">
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:rgba(217,30,110,0.88);color:white;">{{ $ebook->category }}</span>
                    </div>

                    {{-- Hover overlay --}}
                    <div class="absolute inset-0 flex items-end p-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300" style="background:linear-gradient(to top, rgba(217,30,110,0.8) 0%, transparent 50%);">
                        <span class="text-white text-xs font-semibold flex items-center gap-1">
                            Voir l'ebook
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </span>
                    </div>
                </div>

                {{-- Titre + label CTA --}}
                <div class="p-3.5 flex flex-col gap-1">
                    <h3 class="text-sm font-bold leading-snug line-clamp-2" style="color:var(--dark);font-family:'Playfair Display',serif;">
                        {{ $ebook->title }}
                    </h3>
                    @if($ebook->isPurchasable())
                    <p class="text-xs font-bold" style="color:var(--rose);">{{ number_format($ebook->price, 0, ',', ' ') }} {{ $ebook->currency }}</p>
                    @elseif($ebook->cta_label)
                    <p class="text-xs font-medium" style="color:var(--rose);">{{ $ebook->cta_label }}</p>
                    @endif
                </div>

            </a>
            @endforeach
        </div>

        {{-- CTA bottom --}}
        <div class="text-center mt-16 fade-up">
            <p class="text-sm mb-4" style="color:var(--gray);">Tu veux être informée des nouveaux ebooks en avant-première ?</p>
            <button @click="$store.modal.join = true" class="btn-rose text-sm px-8 py-3">
                Rejoindre la communauté FSL
            </button>
            <p class="text-xs mt-6" style="color:var(--gray);">Déjà acheté un ebook ? <a href="{{ route('ebooks.resend') }}" style="color:var(--rose);text-decoration:underline;">Renvoyer mon lien de téléchargement</a></p>
        </div>
        @endif

    </div>
</section>

@endsection
