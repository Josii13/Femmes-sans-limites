{{--
    Moyens de paiement acceptés, dessinés en SVG inline.

    Pourquoi pas des fichiers images : six logos = six requêtes et six fichiers à
    ne pas oublier au déploiement, pour un bloc qui pèse moins de 3 Ko en ligne.
    En SVG le rendu reste net sur tous les écrans et suit la taille du texte.

    `label` : la phrase de réassurance au-dessus (false pour n'afficher que les logos).
--}}
@props(['label' => 'Paiement sécurisé — livraison immédiate par email.'])

<div {{ $attributes }}>
    @if($label)
    <p class="flex items-start gap-1.5 text-xs leading-relaxed" style="color:var(--gray);">
        <svg class="w-3.5 h-3.5 mt-px shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        <span>{{ $label }}</span>
    </p>
    @endif

    @php
        // Une seule définition du cadre : les logos n'ont pas tous le même fond
        // (MTN est jaune, Visa est blanc), la pastille blanche les égalise.
        $tile = 'inline-flex items-center justify-center bg-white rounded-lg px-2 h-8';
    @endphp

    <div class="flex flex-wrap items-center gap-2 {{ $label ? 'mt-2.5' : '' }}">

        {{-- Wave --}}
        <span class="{{ $tile }}" style="border:1px solid var(--border);">
            <svg height="18" viewBox="0 0 66 24" role="img" aria-label="Wave">
                <rect width="24" height="24" rx="6" fill="#1AC8F0"/>
                <path d="M5 14.2c1.7-3.6 3.3-3.6 5 0s3.3 3.6 5 0" fill="none" stroke="#fff" stroke-width="2.3" stroke-linecap="round"/>
                <text x="29" y="17.5" font-family="Arial,Helvetica,sans-serif" font-size="14" font-weight="700" fill="#0E3A4F">Wave</text>
            </svg>
        </span>

        {{-- Orange Money : le carré orange avec le mot-symbole, comme la marque. --}}
        <span class="{{ $tile }}" style="border:1px solid var(--border);">
            <svg height="18" viewBox="0 0 78 24" role="img" aria-label="Orange Money">
                <rect width="31" height="24" fill="#FF7900"/>
                <text x="15.5" y="15.5" text-anchor="middle" font-family="Arial,Helvetica,sans-serif" font-size="8.5" font-weight="700" fill="#fff">orange</text>
                <text x="35" y="17.5" font-family="Arial,Helvetica,sans-serif" font-size="13" font-weight="700" fill="#1A0A10">Money</text>
            </svg>
        </span>

        {{-- MTN --}}
        <span class="{{ $tile }}" style="border:1px solid var(--border);">
            <svg height="18" viewBox="0 0 52 24" role="img" aria-label="MTN Mobile Money">
                <rect width="52" height="24" rx="12" fill="#FFCB05"/>
                <text x="26" y="17" text-anchor="middle" font-family="Arial,Helvetica,sans-serif" font-size="13" font-weight="700" fill="#000" letter-spacing="0.5">MTN</text>
            </svg>
        </span>

        {{-- Moov Africa --}}
        <span class="{{ $tile }}" style="border:1px solid var(--border);">
            <svg height="18" viewBox="0 0 58 24" role="img" aria-label="Moov Africa Money">
                <rect width="58" height="24" rx="5" fill="#005BAA"/>
                <text x="27" y="17.5" text-anchor="middle" font-family="Arial,Helvetica,sans-serif" font-size="14" font-weight="700" fill="#fff">moov</text>
                <circle cx="50" cy="7" r="3.2" fill="#F58220"/>
            </svg>
        </span>

        {{-- Visa --}}
        <span class="{{ $tile }}" style="border:1px solid var(--border);">
            <svg height="18" viewBox="0 0 50 24" role="img" aria-label="Visa">
                <text x="25" y="18" text-anchor="middle" font-family="Arial,Helvetica,sans-serif" font-size="16" font-weight="700" font-style="italic" fill="#1A1F71" letter-spacing="0.5">VISA</text>
            </svg>
        </span>

        {{-- Mastercard --}}
        <span class="{{ $tile }}" style="border:1px solid var(--border);">
            <svg height="18" viewBox="0 0 42 24" role="img" aria-label="Mastercard">
                <circle cx="15" cy="12" r="9" fill="#EB001B"/>
                <circle cx="27" cy="12" r="9" fill="#F79E1B"/>
                {{-- Zone commune aux deux disques : la teinte orange foncé du logo. --}}
                <path d="M21 5.292a9 9 0 0 0 0 13.416 9 9 0 0 0 0-13.416Z" fill="#FF5F00"/>
            </svg>
        </span>
    </div>
</div>
