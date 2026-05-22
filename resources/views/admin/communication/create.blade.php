@extends('layouts.admin')
@section('title','Nouvelle campagne')
@section('page-title','Communication')
@section('page-subtitle','Nouvelle campagne')

@section('content')
<div class="max-w-4xl"
     x-data="{
        type: '{{ old('type', $prefill?->type ?? 'text') }}',
        target: '{{ old('target_type','all') }}',
        sendMode: '{{ old('send_mode','draft') }}',
        hasImage(){ return ['text_image','text_image_cta'].includes(this.type) },
        hasCta(){ return ['text_cta','text_image_cta'].includes(this.type) },
        applyTemplate(url) {
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(t => {
                    this.type = t.type;
                    this.$refs.subject.value  = t.subject  || '';
                    this.$refs.body.value     = t.body     || '';
                    this.$refs.ctaLabel.value = t.cta_label || '';
                    this.$refs.ctaUrl.value   = t.cta_url  || '';
                });
        }
     }">

@if($errors->any())
<div class="mb-6 px-4 py-3 rounded-xl text-sm" style="background:#FEF2F2;border:1px solid #FCA5A5;color:#991B1B;">
    <ul class="space-y-1">@foreach($errors->all() as $e)<li class="flex gap-2"><span style="color:var(--rose)">•</span>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form action="{{ route('admin.communication.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
@csrf

{{-- Sélecteur de template --}}
@if($templates->isNotEmpty())
<div class="admin-card space-y-3">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold" style="color:var(--dark);">Utiliser un template</h3>
            <p class="text-xs mt-0.5" style="color:var(--gray);">Sélectionnez un template pour pré-remplir le formulaire automatiquement.</p>
        </div>
        <a href="{{ route('admin.communication.templates.index') }}" class="text-xs font-medium" style="color:var(--rose);">Gérer les templates →</a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach($templates as $t)
        <button type="button"
                @click="applyTemplate('{{ route('admin.communication.templates.apply', $t) }}')"
                class="text-left border-2 rounded-xl p-3 transition-all hover:border-rose-300 hover:bg-rose-50 group"
                style="border-color:var(--border);">
            <div class="flex items-center justify-between gap-2 mb-1.5">
                <p class="text-xs font-bold truncate group-hover:text-rose-600" style="color:var(--dark);">{{ $t->name }}</p>
                <span class="text-white text-[9px] font-bold px-2 py-0.5 rounded-full flex-shrink-0"
                      style="background:{{ $t->type_badge_color }};">{{ $t->type_label }}</span>
            </div>
            <p class="text-[11px] truncate" style="color:var(--gray);">{{ $t->subject }}</p>
        </button>
        @endforeach
    </div>
    @if($prefill)
    <div class="text-xs px-3 py-2 rounded-lg flex items-center gap-2" style="background:#F0FDF4;color:#166534;">
        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        Template <strong>{{ $prefill->name }}</strong> appliqué — modifiez librement le contenu ci-dessous.
    </div>
    @endif
</div>
@endif

{{-- Infos de base --}}
<div class="admin-card space-y-5">
    <h3 class="text-sm font-bold pb-3" style="color:var(--dark);border-bottom:1px solid var(--border);">Informations de la campagne</h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div class="col-span-2 sm:col-span-1">
            <label class="form-label">Nom interne <span style="color:var(--rose)">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}" class="form-input" placeholder="Ex: Newsletter Mai 2026" required>
            <p class="text-xs mt-1" style="color:var(--gray);">Visible uniquement dans le back office.</p>
        </div>
        <div class="col-span-2 sm:col-span-1">
            <label class="form-label">Objet de l'email <span style="color:var(--rose)">*</span></label>
            <input type="text" name="subject" x-ref="subject" value="{{ old('subject', $prefill?->subject ?? '') }}" class="form-input" placeholder="Ex: 🌸 Une invitation spéciale pour toi" required>
        </div>
    </div>

    <div>
        <label class="form-label">Type de message <span style="color:var(--rose)">*</span></label>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-1">
            @foreach([
                ['text','Texte','M4 6h16M4 12h16M4 18h7'],
                ['text_image','Texte + Image','M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ['text_cta','Texte + CTA','M13 10V3L4 14h7v7l9-11h-7z'],
                ['text_image_cta','Texte + Image + CTA','M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'],
            ] as [$val,$label,$icon])
            <label class="relative cursor-pointer">
                <input type="radio" name="type" value="{{ $val }}" x-model="type" class="sr-only" @if(old('type','text')===$val) checked @endif>
                <div class="border-2 rounded-xl p-3 text-center transition-all"
                     :class="type === '{{ $val }}' ? 'border-rose-400 bg-rose-50' : 'border-gray-200 hover:border-gray-300'">
                    <svg class="w-5 h-5 mx-auto mb-1.5" :style="type === '{{ $val }}' ? 'color:var(--rose)' : 'color:#9CA3AF'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $icon }}"/></svg>
                    <p class="text-xs font-medium" :style="type === '{{ $val }}' ? 'color:var(--rose)' : 'color:var(--gray)'">{{ $label }}</p>
                </div>
            </label>
            @endforeach
        </div>
    </div>
</div>

{{-- Contenu --}}
<div class="admin-card space-y-5">
    <h3 class="text-sm font-bold pb-3" style="color:var(--dark);border-bottom:1px solid var(--border);">Contenu du message</h3>

    <div>
        <label class="form-label">Corps du message <span style="color:var(--rose)">*</span></label>
        <textarea name="body" x-ref="body" rows="8" class="form-input resize-none font-mono text-sm" placeholder="Bonjour {prenom},&#10;&#10;Nous avons le plaisir de..." required>{{ old('body', $prefill?->body ?? '') }}</textarea>
        <div class="mt-2 flex flex-wrap gap-1.5">
            @foreach(['{prenom}','{nom}','{numero}','{type}','{ville}','{pays}','{profession}'] as $tag)
            <button type="button"
                onclick="const t=document.querySelector('[x-ref=body]');const s=t.selectionStart;t.value=t.value.slice(0,s)+'{{ $tag }}'+t.value.slice(t.selectionEnd);t.focus();t.selectionEnd=s+{{ strlen($tag) }};"
                class="text-[11px] px-2 py-0.5 rounded font-mono font-medium border transition-colors"
                style="border-color:var(--rose-mid);color:var(--rose);background:var(--rose-pale);">{{ $tag }}</button>
            @endforeach
        </div>
        <p class="text-xs mt-1.5" style="color:var(--gray);">Cliquez sur une variable pour l'insérer à la position du curseur — elle sera remplacée par les données réelles à l'envoi.</p>
    </div>

    <div x-show="hasImage()">
        <label class="form-label">Image</label>
        <input type="file" name="image" accept="image/*" class="form-input" onchange="previewImg(this,'img-preview-c')">
        <div id="img-preview-c" class="hidden mt-3">
            <img id="img-preview-c-img" src="" alt="" class="h-36 rounded-xl object-cover shadow-sm">
        </div>
        <p class="text-xs mt-1" style="color:var(--gray);">JPG, PNG — max 5 Mo. Sera affichée après le texte.</p>
    </div>

    <div x-show="hasCta()" class="grid grid-cols-1 sm:grid-cols-2 gap-5 p-4 rounded-xl" style="background:var(--rose-pale);">
        <div>
            <label class="form-label">Label du bouton <span style="color:var(--rose)">*</span></label>
            <input type="text" name="cta_label" x-ref="ctaLabel" value="{{ old('cta_label', $prefill?->cta_label ?? '') }}" class="form-input" placeholder="En savoir plus">
        </div>
        <div>
            <label class="form-label">Lien du bouton <span style="color:var(--rose)">*</span></label>
            <input type="url" name="cta_url" x-ref="ctaUrl" value="{{ old('cta_url', $prefill?->cta_url ?? '') }}" class="form-input" placeholder="https://...">
        </div>
    </div>
</div>

{{-- Ciblage --}}
<div class="admin-card space-y-5">
    <h3 class="text-sm font-bold pb-3" style="color:var(--dark);border-bottom:1px solid var(--border);">Destinataires</h3>

    <div>
        <label class="form-label">Cible <span style="color:var(--rose)">*</span></label>
        <select name="target_type" x-model="target" class="form-input">
            <option value="all">Tous les membres actifs</option>
            <option value="standard">Membres Standard uniquement</option>
            <option value="gold">Membres Gold uniquement</option>
            <option value="premium">Membres Premium uniquement</option>
            <option value="single">Un seul membre</option>
            <option value="custom">Sélection manuelle</option>
        </select>
    </div>

    {{-- Membre unique --}}
    <div x-show="target === 'single'">
        <label class="form-label">Choisir le membre <span style="color:var(--rose)">*</span></label>
        <select name="target_member_id" class="form-input">
            <option value="">-- Sélectionner --</option>
            @foreach($members as $m)
            <option value="{{ $m->id }}" @selected(old('target_member_id') == $m->id)>
                {{ $m->name }} — {{ $m->email }} ({{ ucfirst($m->type) }})
            </option>
            @endforeach
        </select>
    </div>

    {{-- Sélection multiple --}}
    <div x-show="target === 'custom'">
        <label class="form-label">Sélectionner les membres <span style="color:var(--rose)">*</span></label>
        <p class="text-xs mb-2" style="color:var(--gray);">Maintenir Ctrl (ou Cmd sur Mac) pour sélectionner plusieurs membres.</p>
        <select name="target_member_ids[]" multiple class="form-input" style="height:160px;">
            @foreach($members as $m)
            <option value="{{ $m->id }}" @if(in_array($m->id, old('target_member_ids',[]) ?? [])) selected @endif>
                {{ $m->name }} — {{ $m->email }} ({{ ucfirst($m->type) }})
            </option>
            @endforeach
        </select>
    </div>

    <div class="text-xs px-3 py-2.5 rounded-lg" style="background:#F0FDF4;color:#166534;">
        <svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Seuls les membres avec statut <strong>actif</strong> et un email valide recevront la campagne.
    </div>
</div>

{{-- Envoi --}}
<div class="admin-card space-y-5">
    <h3 class="text-sm font-bold pb-3" style="color:var(--dark);border-bottom:1px solid var(--border);">Envoi</h3>

    <div class="grid grid-cols-3 gap-3">
        @foreach([['draft','Brouillon','Enregistrer sans envoyer','✎'],['now','Envoyer maintenant','Envoi immédiat','↗'],['scheduled','Programmer','Choisir une date','⏰']] as [$val,$label,$desc,$icon])
        <label class="cursor-pointer">
            <input type="radio" name="send_mode" value="{{ $val }}" x-model="sendMode" class="sr-only" @if(old('send_mode','draft')===$val) checked @endif>
            <div class="border-2 rounded-xl p-4 text-center transition-all"
                 :class="sendMode === '{{ $val }}' ? 'border-rose-400 bg-rose-50' : 'border-gray-200 hover:border-gray-300'">
                <p class="text-lg mb-1">{{ $icon }}</p>
                <p class="text-sm font-bold" :style="sendMode === '{{ $val }}' ? 'color:var(--rose)' : 'color:var(--dark)'">{{ $label }}</p>
                <p class="text-xs" style="color:var(--gray);">{{ $desc }}</p>
            </div>
        </label>
        @endforeach
    </div>

    <div x-show="sendMode === 'scheduled'">
        <label class="form-label">Date et heure d'envoi <span style="color:var(--rose)">*</span></label>
        <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="form-input"
               min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}">
    </div>

    @if(old('send_mode') === 'now' || old('send_mode') === 'scheduled')
    <div class="px-4 py-3 rounded-xl text-sm" style="background:#FEF3C7;border:1px solid #FCD34D;color:#92400E;">
        ⚠️ Attention : l'envoi est immédiat et irréversible. Vérifiez bien votre message et votre cible.
    </div>
    @endif
</div>

<div x-data="{ showPreview: false }" class="pt-2 space-y-4">

    {{-- Aperçu rapide --}}
    <div class="admin-card p-0 overflow-hidden" x-show="showPreview" x-cloak>
        <div class="flex items-center justify-between px-5 py-3" style="background:#F8F7F9;border-bottom:1px solid var(--border);">
            <p class="text-xs font-semibold" style="color:var(--dark);">Aperçu du contenu — rendu approximatif</p>
            <button type="button" @click="showPreview = false" class="text-xs" style="color:var(--gray);">Fermer ✕</button>
        </div>
        <div class="p-6 max-w-xl mx-auto">
            <div class="rounded-2xl overflow-hidden" style="border:1px solid var(--border);">
                <div class="px-8 py-5 text-center" style="background:#1A0A10;">
                    <p style="font-family:Georgia,serif;font-size:18px;font-weight:700;color:#D91E6E;margin:0;">Femme Sans Limites</p>
                </div>
                <div class="px-8 py-6">
                    <p class="text-sm font-semibold mb-1" style="color:#9CA3AF;">Objet :</p>
                    <p class="text-base font-bold mb-5" style="color:#1A0A10;" x-text="$refs.subject.value || '(objet vide)'"></p>
                    <div class="text-sm leading-relaxed whitespace-pre-line" style="color:#3D3450;" x-text="$refs.body.value || '(corps vide)'"></div>
                    <template x-if="hasCta() && $refs.ctaLabel.value">
                        <div class="mt-6 text-center">
                            <span class="inline-block px-6 py-2.5 rounded-full text-white text-sm font-bold" style="background:#D91E6E;" x-text="$refs.ctaLabel.value"></span>
                        </div>
                    </template>
                </div>
                <div class="px-8 py-4 text-center text-xs" style="background:#F9F8FB;border-top:1px solid #EEEBF0;color:#9CA3AF;">© {{ date('Y') }} Femme Sans Limites</div>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="btn-rose">
            <span x-text="sendMode === 'now' ? 'Envoyer la campagne' : (sendMode === 'scheduled' ? 'Programmer l\'envoi' : 'Enregistrer le brouillon')"></span>
        </button>
        <button type="button" @click="showPreview = !showPreview" class="btn-gold flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            <span x-text="showPreview ? 'Masquer l\'aperçu' : 'Aperçu rapide'"></span>
        </button>
        <a href="{{ route('admin.communication.index') }}" class="btn-outline">Annuler</a>
    </div>
</div>

</form>
</div>

@push('scripts')
<script>
function previewImg(input, id) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById(id+'-img').src = e.target.result;
            document.getElementById(id).classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
