@extends('layouts.public')
@section('title', 'Ebooks — Femmes Sans Limites')
@section('description', 'Découvrez les ebooks FSL : leadership, finance, entrepreneuriat, bien-être. Des ressources pensées pour les femmes ambitieuses d\'Afrique et de la diaspora.')

@section('content')

{{-- Hero --}}
<section class="pt-28 pb-16 bg-white relative overflow-hidden">
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
</section>

{{-- Filters by category --}}
@if($categories->isNotEmpty())
<div class="bg-white border-b" style="border-color:var(--border);">
    <div class="max-w-7xl mx-auto px-5 lg:px-8">
        <div class="flex items-center gap-2 py-4 overflow-x-auto no-scrollbar">
            <a href="{{ route('ebooks.index') }}"
               class="flex-shrink-0 text-sm font-medium px-4 py-2 rounded-full transition-all {{ !request('categorie') ? 'text-white' : 'hover:bg-gray-50' }}"
               style="{{ !request('categorie') ? 'background:var(--rose);' : 'color:var(--gray);' }}">
                Tous
            </a>
            @foreach($categories as $cat)
            <a href="{{ route('ebooks.index', ['categorie' => $cat]) }}"
               class="flex-shrink-0 text-sm font-medium px-4 py-2 rounded-full transition-all {{ request('categorie') === $cat ? 'text-white' : 'hover:bg-gray-50' }}"
               style="{{ request('categorie') === $cat ? 'background:var(--rose);' : 'color:var(--gray);' }}">
                {{ $cat }}
            </a>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Ebooks grid --}}
<section class="py-16 lg:py-20" style="background:var(--warm);">
    <div class="max-w-7xl mx-auto px-5 lg:px-8">

        @if($ebooks->isEmpty())
        <div class="text-center py-24 fade-up">
            <div class="w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-5" style="background:var(--rose-pale);">
                <svg class="w-9 h-9" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <h3 class="text-2xl font-bold mb-3" style="color:var(--dark);font-family:'Playfair Display',serif;">Bientôt disponible</h3>
            <p class="text-base mb-8" style="color:var(--gray);">Nos ebooks arrivent prochainement. Rejoins la communauté pour être notifiée en avant-première !</p>
            <button @click="$store.modal.join = true" class="btn-rose">Rejoindre FSL</button>
        </div>

        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($ebooks as $ebook)
            <article class="card-hover flex flex-col fade-up group overflow-hidden">

                {{-- Cover --}}
                <div class="relative flex-shrink-0 overflow-hidden" style="aspect-ratio:3/4;">
                    @if($ebook->image)
                    <img src="{{ asset('storage/'.$ebook->image) }}"
                         alt="{{ $ebook->title }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                    <div class="w-full h-full flex flex-col items-center justify-center gap-3 p-6"
                         style="background:linear-gradient(145deg,var(--rose-pale) 0%,var(--gold-pale) 100%);">
                        <svg class="w-12 h-12 opacity-30" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <p class="text-center text-sm font-semibold opacity-50" style="color:var(--dark);">{{ $ebook->title }}</p>
                    </div>
                    @endif

                    {{-- Category badge --}}
                    <div class="absolute top-3 left-3">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full" style="background:rgba(217,30,110,0.9);color:white;">{{ $ebook->category }}</span>
                    </div>
                </div>

                {{-- Content --}}
                <div class="p-5 flex flex-col flex-1">
                    <h3 class="text-base font-bold mb-2 leading-snug" style="color:var(--dark);font-family:'Playfair Display',serif;">
                        {{ $ebook->title }}
                    </h3>

                    <p class="text-sm leading-relaxed mb-4 flex-1" style="color:var(--gray);">
                        {{ Str::limit($ebook->description, 120) }}
                    </p>

                    {{-- Mot de l'autrice --}}
                    @if($ebook->author_note)
                    <blockquote class="rounded-xl p-3 mb-4 italic text-xs leading-relaxed" style="background:var(--rose-pale);color:var(--rose);border-left:3px solid var(--rose);">
                        « {{ Str::limit($ebook->author_note, 100) }} »
                    </blockquote>
                    @endif

                    {{-- CTA --}}
                    <a href="{{ $ebook->cta_url }}" target="_blank" rel="noopener noreferrer"
                       class="btn-rose w-full justify-center text-sm py-3 mt-auto">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        {{ $ebook->cta_label }}
                    </a>
                </div>
            </article>
            @endforeach
        </div>

        {{-- Bottom CTA --}}
        <div class="text-center mt-16 fade-up">
            <p class="text-sm mb-4" style="color:var(--gray);">Tu veux être informée des nouveaux ebooks en avant-première ?</p>
            <button @click="$store.modal.join = true" class="btn-rose text-sm px-8 py-3">
                Rejoindre la communauté FSL
            </button>
        </div>
        @endif

    </div>
</section>

@endsection
