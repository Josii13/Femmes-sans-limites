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

<section class="pt-20 pb-16 bg-white relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none" style="background:radial-gradient(ellipse at 70% 50%,var(--rose-pale) 0%,transparent 55%),radial-gradient(ellipse at 20% 80%,var(--gold-pale) 0%,transparent 50%);"></div>
    <div class="max-w-6xl mx-auto px-5 lg:px-8 relative">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm mb-8 fade-up" style="color:var(--gray);">
            <a href="{{ route('ebooks.index') }}" class="hover:underline" style="color:var(--rose);">Bibliothèque</a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span>{{ $ebook->category }}</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="truncate max-w-xs">{{ $ebook->title }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-start">

            {{-- Cover --}}
            <div class="fade-up">
                <div class="relative rounded-2xl overflow-hidden shadow-2xl mx-auto max-w-xs lg:max-w-sm" style="aspect-ratio:3/4;">
                    @if($ebook->image)
                    <img src="{{ asset('storage/'.$ebook->image) }}" alt="Couverture de l'ebook : {{ $ebook->title }}" class="w-full h-full object-cover">
                    @else
                    <div class="w-full h-full flex flex-col items-center justify-center gap-4 p-8"
                         style="background:linear-gradient(145deg,var(--rose-pale) 0%,var(--gold-pale) 100%);">
                        <svg class="w-16 h-16 opacity-25" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <p class="text-center text-base font-bold opacity-40" style="color:var(--dark);">{{ $ebook->title }}</p>
                    </div>
                    @endif

                    {{-- Category badge --}}
                    <div class="absolute top-4 left-4">
                        <span class="text-xs font-bold px-3 py-1.5 rounded-full" style="background:rgba(217,30,110,0.9);color:white;">{{ $ebook->category }}</span>
                    </div>
                </div>
            </div>

            {{-- Content --}}
            <div class="fade-up delay-100">
                <span class="section-label">{{ $ebook->category }}</span>
                <h1 class="text-4xl lg:text-5xl font-bold mt-3 mb-6 leading-tight" style="color:var(--dark);font-family:'Playfair Display',serif;">
                    {{ $ebook->title }}
                </h1>

                <p class="text-base leading-relaxed mb-8" style="color:var(--gray);">
                    {{ $ebook->description }}
                </p>

                @if($ebook->author_note)
                <blockquote class="rounded-2xl p-5 mb-8 italic leading-relaxed" style="background:var(--rose-pale);border-left:4px solid var(--rose);color:var(--rose);">
                    <svg class="w-6 h-6 mb-2 opacity-40" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
                    {{ $ebook->author_note }}
                </blockquote>
                @endif

                @if($ebook->isPurchasable())
                {{-- Vente directe sur le site --}}
                <div class="flex items-baseline gap-2 mb-4">
                    <span class="text-3xl font-bold" style="color:var(--dark);font-family:'Playfair Display',serif;">{{ number_format($ebook->price, 0, ',', ' ') }}</span>
                    <span class="text-sm font-semibold" style="color:var(--gray);">{{ $ebook->currency }}</span>
                </div>
                <a href="{{ route('ebooks.buy', $ebook->slug) }}"
                   class="btn-rose inline-flex items-center gap-3 px-8 py-4 text-base">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Acheter et télécharger
                </a>
                <p class="mt-3 text-xs" style="color:var(--gray);">
                    🔒 Paiement sécurisé (Wave, Orange Money, MTN, Moov, carte) — livraison immédiate par email.
                </p>
                @elseif($ebook->cta_url)
                {{-- Lien externe (partenaire) --}}
                <a href="{{ $ebook->cta_url }}" target="_blank" rel="noopener noreferrer"
                   class="btn-rose inline-flex items-center gap-3 px-8 py-4 text-base">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{-- Le libellé peut être vide (option « Aucun ») : on garde un bouton lisible. --}}
                    {{ $ebook->cta_label ?: 'Accéder à l’ebook' }}
                </a>
                <p class="mt-3 text-xs" style="color:var(--gray);">
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
        </div>
    </div>
</section>

{{-- Autres ebooks --}}
@if($others->isNotEmpty())
<section class="py-16" style="background:var(--warm);">
    <div class="max-w-6xl mx-auto px-5 lg:px-8">
        <h2 class="text-2xl font-bold mb-8 fade-up" style="color:var(--dark);font-family:'Playfair Display',serif;">À découvrir aussi</h2>
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

@endsection
