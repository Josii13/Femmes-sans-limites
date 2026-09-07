@extends('layouts.public')
@section('title', $ebook->title . ' — FSL Bibliothèque')
@section('description', Str::limit($ebook->description, 160))
@section('og_type', 'book')
@section('og_image', $ebook->image ? asset('storage/'.$ebook->image) : asset('logo_FSL.png'))

@push('seo')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "Book",
  "name": @json($ebook->title),
  "description": @json(Str::limit($ebook->description, 200)),
  "url": "{{ url()->current() }}",
  "publisher": {
    "@type": "Organization",
    "name": "Femme Sans Limites",
    "url": "{{ config('app.url') }}"
  }
  @if($ebook->image)
  ,"image": "{{ asset('storage/'.$ebook->image) }}"
  @endif
}
</script>
@endpush

@section('content')

@php
    // Trois états possibles, résolus une seule fois : la barre flottante et le bloc
    // dans la page doivent toujours proposer la même action.
    $isSale = $ebook->isPurchasable();
    $ctaUrl = $isSale ? route('ebooks.buy', $ebook->slug) : ($ebook->cta_url ?: null);
    $ctaLabel = $isSale ? 'Acheter et télécharger' : ($ebook->cta_label ?: 'Accéder à l’ebook');
@endphp

<section class="pt-20 pb-14 lg:pb-16 bg-white relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none" style="background:radial-gradient(ellipse at 70% 50%,var(--rose-pale) 0%,transparent 55%),radial-gradient(ellipse at 20% 80%,var(--gold-pale) 0%,transparent 50%);"></div>
    <div class="max-w-6xl mx-auto px-5 lg:px-8 relative">

        {{-- Fil d'Ariane : réduit à un retour arrière sur mobile, complet à partir de sm. --}}
        <nav class="flex items-center gap-2 text-sm mb-6 lg:mb-8 fade-up" style="color:var(--gray);">
            <a href="{{ route('ebooks.index') }}" class="inline-flex items-center gap-1.5 hover:underline shrink-0" style="color:var(--rose);">
                <svg class="w-4 h-4 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Bibliothèque
            </a>
            <span class="hidden sm:flex items-center gap-2 min-w-0">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="shrink-0">{{ $ebook->category }}</span>
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="truncate">{{ $ebook->title }}</span>
            </span>
        </nav>

        {{--
            L'ordre du DOM est l'ordre MOBILE — titre, couverture, achat, description, note —
            pour que le prix et le bouton restent atteignables sans traverser tout le texte.
            Sur grand écran, le placement explicite en lignes/colonnes rétablit la mise en
            page d'origine : couverture à gauche, texte puis achat à droite.
        --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-y-7 lg:gap-y-0 lg:gap-x-16 items-start">

            {{-- Titre --}}
            <div class="fade-up lg:col-start-2 lg:row-start-1 lg:mb-6">
                <span class="section-label">{{ $ebook->category }}</span>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold mt-2 lg:mt-3 leading-tight" style="color:var(--dark);font-family:'Playfair Display',serif;">
                    {{ $ebook->title }}
                </h1>
            </div>

            {{-- Couverture --}}
            <div class="fade-up lg:col-start-1 lg:row-start-1 lg:row-span-4">
                <div class="relative rounded-2xl overflow-hidden shadow-xl lg:shadow-2xl mx-auto w-full max-w-[13rem] sm:max-w-xs lg:max-w-sm" style="aspect-ratio:3/4;">
                    @if($ebook->image)
                    <img src="{{ asset('storage/'.$ebook->image) }}" alt="Couverture de l'ebook : {{ $ebook->title }}" class="w-full h-full object-cover">
                    @else
                    <div class="w-full h-full flex flex-col items-center justify-center gap-4 p-6 lg:p-8"
                         style="background:linear-gradient(145deg,var(--rose-pale) 0%,var(--gold-pale) 100%);">
                        <svg class="w-12 h-12 lg:w-16 lg:h-16 opacity-25" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <p class="text-center text-sm lg:text-base font-bold opacity-40" style="color:var(--dark);">{{ $ebook->title }}</p>
                    </div>
                    @endif

                    <div class="absolute top-3 left-3 lg:top-4 lg:left-4">
                        <span class="text-[11px] lg:text-xs font-bold px-2.5 py-1 lg:px-3 lg:py-1.5 rounded-full" style="background:rgba(217,30,110,0.9);color:white;">{{ $ebook->category }}</span>
                    </div>

                    @if($ebook->hasActivePromo())
                    <div class="absolute top-3 right-3 lg:top-4 lg:right-4">
                        <span class="inline-flex flex-col items-center leading-none font-bold text-white rounded-full px-3 py-2 shadow-lg" style="background:var(--dark);">
                            <span class="text-sm">−{{ $ebook->promoDiscountPercent() }}%</span>
                            <span class="text-[9px] font-semibold uppercase tracking-wider mt-0.5 opacity-70">Promo</span>
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            {{--
                Bloc d'achat. L'identifiant sert de repère à la barre flottante :
                celle-ci ne s'affiche que lorsque ce bloc n'est pas à l'écran.
            --}}
            <div id="ebook-cta" class="fade-up lg:col-start-2 lg:row-start-4">
                @if($isSale)
                {{-- Vente directe : encadré sur mobile pour détacher le prix du texte, sobre sur desktop. --}}
                <div class="rounded-2xl border p-5 bg-[var(--rose-pale)] border-[var(--rose-mid)] lg:rounded-none lg:border-0 lg:bg-transparent lg:p-0">
                    <x-ebook-price :ebook="$ebook" variant="page" />
                    @if($ebook->hasActivePromo())
                    <p class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold" style="color:var(--rose);">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Offre valable jusqu'au {{ $ebook->promo_ends_at->translatedFormat('d F Y à H:i') }}
                    </p>
                    @endif

                    <a href="{{ $ctaUrl }}"
                       class="btn-rose w-full lg:w-auto inline-flex items-center justify-center gap-3 px-8 py-4 text-base mt-4">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Acheter et télécharger
                    </a>

                    <p class="mt-3 flex items-start gap-1.5 text-xs leading-relaxed" style="color:var(--gray);">
                        <svg class="w-3.5 h-3.5 mt-px shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>Paiement sécurisé — livraison immédiate par email.</span>
                    </p>
                    <div class="flex flex-wrap gap-1.5 mt-2.5">
                        @foreach(['Wave', 'Orange Money', 'MTN', 'Moov', 'Carte'] as $method)
                        <span class="text-[11px] font-medium px-2 py-0.5 rounded-md bg-white" style="color:var(--gray);border:1px solid var(--border);">{{ $method }}</span>
                        @endforeach
                    </div>
                </div>

                @elseif($ctaUrl)
                {{-- Lien externe (partenaire) --}}
                <a href="{{ $ctaUrl }}" target="_blank" rel="noopener noreferrer"
                   class="btn-rose w-full lg:w-auto inline-flex items-center justify-center gap-3 px-8 py-4 text-base">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ $ctaLabel }}
                </a>
                <p class="mt-3 text-xs text-center lg:text-left" style="color:var(--gray);">
                    Disponible via notre partenaire Charriow
                    <svg class="w-3 h-3 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </p>

                @else
                {{-- Ni vente directe (prix + PDF) ni lien partenaire : on oriente vers le contact. --}}
                <div class="rounded-2xl p-5" style="background:var(--rose-pale);border:1px solid var(--border);">
                    <p class="text-sm font-semibold mb-1" style="color:var(--dark);">Bientôt disponible</p>
                    <p class="text-sm leading-relaxed" style="color:var(--gray);">Cet ebook n’est pas encore proposé au téléchargement. Écris-nous pour être prévenue de sa sortie.</p>
                    <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 mt-3 text-sm font-semibold" style="color:var(--rose);">
                        Nous contacter
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>
                @endif
            </div>

            {{-- Description --}}
            <div class="fade-up lg:col-start-2 lg:row-start-2 lg:mb-6">
                <p class="text-[15px] sm:text-base leading-relaxed" style="color:var(--gray);">
                    {{ $ebook->description }}
                </p>
            </div>

            {{-- Mot de l'autrice --}}
            @if($ebook->author_note)
            <div class="fade-up lg:col-start-2 lg:row-start-3 lg:mb-8">
                <blockquote class="rounded-2xl p-5 italic leading-relaxed" style="background:var(--rose-pale);border-left:4px solid var(--rose);color:var(--rose);">
                    <svg class="w-6 h-6 mb-2 opacity-40" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
                    {{ $ebook->author_note }}
                </blockquote>
            </div>
            @endif

        </div>
    </div>
</section>

{{-- Autres ebooks --}}
@if($others->isNotEmpty())
<section class="py-12 lg:py-16" style="background:var(--warm);">
    <div class="max-w-6xl mx-auto px-5 lg:px-8">
        <h2 class="text-xl lg:text-2xl font-bold mb-6 lg:mb-8 fade-up" style="color:var(--dark);font-family:'Playfair Display',serif;">À découvrir aussi</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
            @foreach($others as $other)
            <a href="{{ route('ebooks.show', $other->slug) }}" class="card-hover group overflow-hidden block fade-up">
                <div class="relative overflow-hidden rounded-xl" style="aspect-ratio:3/4;">
                    @if($other->image)
                    <img src="{{ asset('storage/'.$other->image) }}" alt="{{ $other->title }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                    <div class="w-full h-full flex items-center justify-center" style="background:linear-gradient(145deg,var(--rose-pale) 0%,var(--gold-pale) 100%);">
                        <svg class="w-10 h-10 opacity-25" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    @endif
                    <div class="absolute inset-0 flex items-end p-3" style="background:linear-gradient(to top, rgba(0,0,0,0.65) 0%, transparent 55%);">
                        <p class="text-white text-xs font-semibold leading-snug line-clamp-2">{{ $other->title }}</p>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($ctaUrl)
{{--
    Barre d'achat flottante, mobile uniquement. Elle apparaît dès que le bloc
    d'achat de la page quitte l'écran — donc aussi avant de l'avoir atteint —
    de sorte que l'action reste toujours à portée de pouce.

    Elle s'efface dès que le pied de page entre à l'écran : sinon elle en
    recouvrirait la dernière ligne (mentions légales, CGU).

    Elle doit rester hors de tout élément .fade-up : leur `transform` créerait un
    bloc conteneur et casserait le `position: fixed`.
--}}
<div x-data="{ ctaOffScreen: true, footerInView: false }"
     x-init="
        const cta = document.getElementById('ebook-cta');
        if (cta) {
            new IntersectionObserver(([e]) => ctaOffScreen = ! e.isIntersecting).observe(cta);
        }
        const footer = document.querySelector('footer');
        if (footer) {
            new IntersectionObserver(([e]) => footerInView = e.isIntersecting).observe(footer);
        }
     "
     x-show="ctaOffScreen && ! footerInView"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="translate-y-full"
     x-transition:enter-end="translate-y-0"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="translate-y-0"
     x-transition:leave-end="translate-y-full"
     x-cloak
     class="fixed inset-x-0 bottom-0 z-40 lg:hidden bg-white"
     style="border-top:1px solid var(--border);box-shadow:0 -4px 24px rgba(15,10,12,0.1);padding-bottom:env(safe-area-inset-bottom);">
    <div class="flex items-center gap-3 px-4 py-3">
        @if($isSale)
        <div class="min-w-0 shrink-0">
            <p class="text-[11px] font-medium leading-none" style="color:var(--gray);">{{ $ebook->hasActivePromo() ? 'Prix promo' : 'Prix' }}</p>
            <x-ebook-price :ebook="$ebook" variant="bar" class="mt-0.5" />
        </div>
        @endif
        <a href="{{ $ctaUrl }}" @if(! $isSale) target="_blank" rel="noopener noreferrer" @endif
           class="btn-rose flex-1 inline-flex items-center justify-center gap-2 py-3.5 text-sm">
            @if($isSale)
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            Acheter maintenant
            @else
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="truncate">{{ $ctaLabel }}</span>
            @endif
        </a>
    </div>
</div>
@endif

@endsection
