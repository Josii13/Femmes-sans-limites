{{--
    Bande de chiffres de l'association, alimentée par la base via site_stats().

    Aucun nombre n'est écrit en dur : ce qui s'affiche est ce que contient
    réellement la plateforme. Les chiffres à zéro sont écartés en amont, et la
    bande disparaît entièrement s'il reste moins de deux chiffres à montrer —
    mieux vaut pas de bande qu'une bande qui sonne creux.

    Disposition en flex centré plutôt qu'en grille fixe : le nombre de chiffres
    varie (2, 3 ou 4) et la bande doit rester équilibrée dans tous les cas.
--}}
@props(['eyebrow' => null, 'title' => null])

@if(site_stats()->hasBanner())
<section class="{{ $title ? 'py-20' : 'py-14' }}" style="background:var(--dark);">
    <div class="max-w-7xl mx-auto px-5 lg:px-8">

        @if($title)
        <div class="text-center mb-12 fade-up">
            @if($eyebrow)
            <span class="text-xs font-bold uppercase tracking-widest" style="color:var(--gold);">{{ $eyebrow }}</span>
            @endif
            <h2 class="text-3xl font-bold mt-2 text-white" style="font-family:'Playfair Display',serif;">{{ $title }}</h2>
        </div>
        @endif

        <div class="flex flex-wrap justify-center gap-x-12 gap-y-10 lg:gap-x-20" data-stagger="120">
            @foreach(site_stats()->banner() as $stat)
            <div class="text-center fade-up" style="min-width:8rem;">
                <p class="text-4xl font-bold mb-2 counter"
                   data-target="{{ $stat['value'] }}"
                   style="color:{{ $stat['accent'] }};font-family:'Playfair Display',serif;">{{ $stat['value'] }}</p>
                <p class="text-sm" style="color:rgba(255,255,255,0.45);">{{ $stat['label'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
