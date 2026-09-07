{{--
    Affichage du prix d'un ebook, promotion comprise : prix normal barré + prix
    promotionnel + étiquette de remise. Mutualisé entre la fiche, les cartes de la
    bibliothèque, la page de paiement et la barre flottante, afin qu'un seul endroit
    décide de ce que voit le visiteur.

    Le montant réellement encaissé vient de Ebook::effectivePrice() — la même méthode
    que celle utilisée par le tunnel de paiement.

    variant : page | card | bar
--}}
@props(['ebook', 'variant' => 'page'])

@php
    $promo = $ebook->hasActivePromo();
    $fmt = fn ($amount) => number_format((float) $amount, 0, ',', ' ');

    $styles = [
        'page' => [
            'wrap' => 'flex flex-wrap items-baseline gap-x-3 gap-y-1',
            'now' => 'text-3xl lg:text-4xl font-bold',
            'currency' => 'text-sm font-semibold',
            'was' => 'text-base line-through',
            'badge' => 'text-xs font-bold px-2 py-0.5 rounded-md',
        ],
        'card' => [
            'wrap' => 'flex flex-wrap items-baseline gap-x-2',
            'now' => 'text-xs font-bold',
            'currency' => 'text-xs font-bold',
            'was' => 'text-[11px] line-through',
            'badge' => 'text-[10px] font-bold px-1.5 py-0.5 rounded',
        ],
        'bar' => [
            'wrap' => 'flex items-baseline gap-2',
            'now' => 'text-lg font-bold leading-tight',
            'currency' => 'text-xs font-semibold ml-1',
            'was' => 'text-xs line-through',
            'badge' => 'hidden',
        ],
    ][$variant];

    $serif = in_array($variant, ['page', 'bar'], true) ? "font-family:'Playfair Display',serif;" : '';
@endphp

<div {{ $attributes->merge(['class' => $styles['wrap']]) }}>
    <span class="{{ $styles['now'] }}" style="color:{{ $promo ? 'var(--rose)' : 'var(--dark)' }};{{ $serif }}">
        {{ $fmt($ebook->effectivePrice()) }}<span class="{{ $styles['currency'] }} ml-1" style="color:var(--gray);">{{ $ebook->currency }}</span>
    </span>

    @if($promo)
    <span class="{{ $styles['was'] }}" style="color:var(--gray-light);">{{ $fmt($ebook->price) }}</span>
    <span class="{{ $styles['badge'] }}" style="background:var(--rose);color:white;">−{{ $ebook->promoDiscountPercent() }}&nbsp;%</span>
    @endif
</div>
