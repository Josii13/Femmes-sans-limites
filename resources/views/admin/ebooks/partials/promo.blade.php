{{--
    Bloc « promotion » partagé par les formulaires de création et d'édition.
    Attend une variable $ebook, éventuellement nulle à la création.
--}}
@php
    $ebook = $ebook ?? null;
    $toInput = fn ($date) => $date ? $date->format('Y-m-d\TH:i') : '';
@endphp

<div class="rounded-xl border p-5 space-y-4" style="border-color:#FCD34D;background:#FFFBEB;"
     x-data="{
        prix: {{ (int) old('price', $ebook?->price ?? 0) }},
        promo: {{ (int) old('promo_price', $ebook?->promo_price ?? 0) }},
        get remise() {
            if (! this.prix || ! this.promo || this.promo >= this.prix) return null;
            return Math.round((1 - this.promo / this.prix) * 100);
        }
     }"
     x-init="
        {{-- Le champ « prix normal » vit dans le bloc voisin : on suit ses saisies
             pour recalculer la remise en direct. --}}
        const prixInput = document.querySelector('[name=price]');
        if (prixInput) {
            prix = Number(prixInput.value || 0);
            prixInput.addEventListener('input', () => prix = Number(prixInput.value || 0));
        }
     ">
    <div>
        <p class="font-semibold text-sm mb-1" style="color:var(--dark);">🏷️ Promotion à durée limitée</p>
        <p class="text-xs text-gray-500">
            Le prix normal s'affiche <span class="line-through">barré</span> à côté du prix promotionnel.
            À la date de fin, le prix normal reprend effet automatiquement — rien à faire.
        </p>

        @if($ebook?->hasActivePromo())
        <p class="text-xs mt-2 font-semibold" style="color:#059669;">
            ✓ Promotion en cours (−{{ $ebook->promoDiscountPercent() }} %), jusqu'au
            {{ $ebook->promo_ends_at->translatedFormat('d F Y à H:i') }}.
        </p>
        @elseif($ebook?->hasScheduledPromo())
        <p class="text-xs mt-2 font-semibold" style="color:#B45309;">
            ⏳ Promotion programmée : elle démarrera le {{ $ebook->promo_starts_at->translatedFormat('d F Y à H:i') }}.
        </p>
        @elseif($ebook?->hasExpiredPromo())
        <p class="text-xs mt-2" style="color:var(--gray);">
            Promotion terminée le {{ $ebook->promo_ends_at->translatedFormat('d F Y à H:i') }} — le prix normal s'applique.
            Videz le prix promotionnel pour retirer complètement la promotion.
        </p>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="form-label">Prix promotionnel</label>
            <input type="number" step="1" min="0" name="promo_price" x-model.number="promo"
                   value="{{ old('promo_price', $ebook?->promo_price ? (int) $ebook->promo_price : '') }}"
                   class="form-input @error('promo_price') border-red-400 @enderror" placeholder="3500">
            <p class="text-xs mt-1" x-show="remise !== null" x-cloak style="color:#059669;">
                Remise de <strong x-text="remise + ' %'"></strong> affichée au visiteur.
            </p>
            <p class="text-xs text-gray-400 mt-1" x-show="remise === null">Vide = aucune promotion.</p>
            @error('promo_price')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Début <span class="font-normal text-gray-400">(vide = tout de suite)</span></label>
            <input type="datetime-local" name="promo_starts_at"
                   value="{{ old('promo_starts_at', $toInput($ebook?->promo_starts_at)) }}"
                   class="form-input @error('promo_starts_at') border-red-400 @enderror">
            @error('promo_starts_at')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Fin <span style="color:var(--rose)">*</span></label>
            <input type="datetime-local" name="promo_ends_at"
                   value="{{ old('promo_ends_at', $toInput($ebook?->promo_ends_at)) }}"
                   class="form-input @error('promo_ends_at') border-red-400 @enderror">
            <p class="text-xs text-gray-400 mt-1">Obligatoire dès qu'un prix promotionnel est saisi.</p>
            @error('promo_ends_at')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>
    </div>
</div>
