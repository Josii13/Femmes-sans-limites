{{--
    Formulaire partagé entre création et édition.
    Attend $testimonial (éventuellement null) et $members.
--}}
@php $testimonial = $testimonial ?? null; @endphp

<div class="admin-card space-y-5"
     x-data="{ quote: @js(old('quote', $testimonial?->quote ?? '')) }">

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="form-label">Nom de la personne <span style="color:var(--rose)">*</span></label>
            <input type="text" name="name" value="{{ old('name', $testimonial?->name) }}"
                   class="form-input @error('name') border-red-400 @enderror" placeholder="Awa Traoré" required>
            @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Fonction et ville</label>
            <input type="text" name="role" value="{{ old('role', $testimonial?->role) }}"
                   class="form-input @error('role') border-red-400 @enderror" placeholder="Entrepreneur · Abidjan">
            @error('role')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label class="form-label">Témoignage <span style="color:var(--rose)">*</span></label>
        <textarea name="quote" rows="5" x-model="quote" minlength="40" maxlength="600" required
                  class="form-input resize-none @error('quote') border-red-400 @enderror"
                  placeholder="Ce que cette personne a réellement dit à propos de la communauté…">{{ old('quote', $testimonial?->quote) }}</textarea>
        <p class="text-xs mt-1 flex items-center justify-between" style="color:var(--gray);">
            <span>Entre 40 et 600 caractères.</span>
            <span x-text="quote.length + ' / 600'" :style="quote.length > 600 ? 'color:#dc2626' : ''"></span>
        </p>
        @error('quote')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="rounded-xl border p-5 space-y-4" style="border-color:var(--rose-mid);background:var(--rose-pale);">
        <div>
            <p class="font-semibold text-sm mb-1" style="color:var(--dark);">Photo</p>
            <p class="text-xs text-gray-500">
                Rattachez le témoignage à une membre pour reprendre sa photo automatiquement,
                ou téléversez-en une. Sans photo, l’initiale du prénom est affichée.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="form-label">Membre citée <span class="font-normal text-gray-400">(optionnel)</span></label>
                <select name="member_id" class="form-input @error('member_id') border-red-400 @enderror">
                    <option value="">— Personne extérieure à la communauté —</option>
                    @foreach($members as $member)
                    <option value="{{ $member->id }}" @selected((int) old('member_id', $testimonial?->member_id) === $member->id)>
                        {{ $member->name }}@if($member->profession) — {{ $member->profession }}@endif
                    </option>
                    @endforeach
                </select>
                @error('member_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Photo dédiée</label>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="form-input py-2 text-sm cursor-pointer">
                @if($testimonial?->photoUrl())
                <div class="flex items-center gap-2 mt-2">
                    <img src="{{ $testimonial->photoUrl() }}" alt="" class="w-10 h-10 rounded-full object-cover object-top">
                    <span class="text-xs" style="color:var(--gray);">Photo actuelle — téléversez pour remplacer.</span>
                </div>
                @endif
                @error('photo')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="form-label">Ordre d’affichage</label>
            <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $testimonial?->sort_order ?? 0) }}" class="form-input">
            <p class="text-xs text-gray-400 mt-1">Le plus petit nombre passe en premier.</p>
        </div>
        <div class="flex items-end">
            <label class="flex items-start gap-3 p-3 rounded-xl cursor-pointer w-full" style="background:var(--warm);">
                {{-- Champ caché : une case décochée n’est pas soumise par le navigateur. --}}
                <input type="hidden" name="is_published" value="0">
                <input type="checkbox" name="is_published" value="1" class="mt-0.5"
                       @checked(old('is_published', $testimonial?->is_published ?? true))>
                <span class="text-xs leading-relaxed" style="color:var(--gray);">
                    <strong style="color:var(--dark);">Afficher sur le site public</strong><br>
                    Décochez pour préparer un témoignage sans le publier.
                </span>
            </label>
        </div>
    </div>
</div>
