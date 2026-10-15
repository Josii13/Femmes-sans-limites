{{--
    Moyens de paiement acceptés.

    Les logos fournis par les marques vivent dans public/images/payments, tous
    ramenés à 64 px de haut : affichés à 28 px, ils restent nets sur les écrans
    2x et 3x sans peser. Les attributs width/height sont renseignés pour que la
    place soit réservée avant le chargement — sinon le bloc saute sous le bouton
    d'achat, juste au moment où l'acheteuse s'apprête à cliquer.

    Moov et Visa attendent encore leur fichier : ils sont dessinés en SVG, à
    remplacer par l'image officielle dès qu'elle est disponible.

    `label` : la phrase de réassurance au-dessus (false pour n'afficher que les logos).
--}}
@props(['label' => 'Paiement sécurisé — livraison immédiate par email.'])

@php
    $logos = [
        ['file' => 'wave.png', 'alt' => 'Wave', 'w' => 64, 'h' => 64],
        ['file' => 'orange-money.png', 'alt' => 'Orange Money', 'w' => 191, 'h' => 64],
        ['file' => 'mtn-momo.png', 'alt' => 'MTN MoMo', 'w' => 106, 'h' => 64],
    ];
    // Une seule définition du cadre : les logos ont chacun leur fond (cyan, noir,
    // jaune, transparent), la pastille blanche les pose sur une base commune.
    $tile = 'inline-flex items-center justify-center bg-white rounded-lg px-2 h-9 overflow-hidden';
@endphp

<div {{ $attributes }}>
    @if($label)
    <p class="flex items-start gap-1.5 text-xs leading-relaxed" style="color:var(--gray);">
        <svg class="w-3.5 h-3.5 mt-px shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        <span>{{ $label }}</span>
    </p>
    @endif

    <div class="flex flex-wrap items-center gap-2 {{ $label ? 'mt-2.5' : '' }}">

        @foreach($logos as $logo)
        <span class="{{ $tile }}" style="border:1px solid var(--border);">
            <img src="{{ asset('images/payments/'.$logo['file']) }}" alt="{{ $logo['alt'] }}"
                 width="{{ $logo['w'] }}" height="{{ $logo['h'] }}"
                 class="h-7 w-auto rounded-sm" loading="lazy" decoding="async">
        </span>
        @endforeach

        {{-- Moov Africa — en attente du fichier officiel. --}}
        <span class="{{ $tile }}" style="border:1px solid var(--border);">
            <svg height="20" viewBox="0 0 58 24" role="img" aria-label="Moov Africa Money">
                <rect width="58" height="24" rx="5" fill="#005BAA"/>
                <text x="27" y="17.5" text-anchor="middle" font-family="Arial,Helvetica,sans-serif" font-size="14" font-weight="700" fill="#fff">moov</text>
                <circle cx="50" cy="7" r="3.2" fill="#F58220"/>
            </svg>
        </span>

        {{-- Visa — en attente du fichier officiel. --}}
        <span class="{{ $tile }}" style="border:1px solid var(--border);">
            <svg height="20" viewBox="0 0 50 24" role="img" aria-label="Visa">
                <text x="25" y="18" text-anchor="middle" font-family="Arial,Helvetica,sans-serif" font-size="16" font-weight="700" font-style="italic" fill="#1A1F71" letter-spacing="0.5">VISA</text>
            </svg>
        </span>
    </div>
</div>
