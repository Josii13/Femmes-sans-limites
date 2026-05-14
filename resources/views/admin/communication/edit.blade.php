@extends('layouts.admin')
@section('title','Modifier — '.$campaign->title)
@section('page-title','Communication')
@section('page-subtitle','Modifier la campagne')

@section('content')
<div class="max-w-4xl"
     x-data="{
        type: '{{ old('type', $campaign->type) }}',
        target: '{{ old('target_type', $campaign->target_type) }}',
        sendMode: '{{ old('send_mode','draft') }}',
        hasImage(){ return ['text_image','text_image_cta'].includes(this.type) },
        hasCta(){ return ['text_cta','text_image_cta'].includes(this.type) },
        applyTemplate(url) {
            if(!confirm('Appliquer ce template ? Le contenu actuel sera remplacé.')) return;
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

<form action="{{ route('admin.communication.update', $campaign) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
@csrf @method('PUT')

{{-- Sélecteur de template --}}
@if($templates->isNotEmpty())
<div class="admin-card space-y-3">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold" style="color:var(--dark);">Appliquer un template</h3>
            <p class="text-xs mt-0.5" style="color:var(--gray);">Le contenu actuel sera remplacé par celui du template sélectionné.</p>
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
</div>
@endif

<div class="admin-card space-y-5">
    <h3 class="text-sm font-bold pb-3" style="color:var(--dark);border-bottom:1px solid var(--border);">Informations</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div class="col-span-2 sm:col-span-1">
            <label class="form-label">Nom interne <span style="color:var(--rose)">*</span></label>
            <input type="text" name="title" value="{{ old('title',$campaign->title) }}" class="form-input" required>
        </div>
        <div class="col-span-2 sm:col-span-1">
            <label class="form-label">Objet de l'email <span style="color:var(--rose)">*</span></label>
            <input type="text" name="subject" x-ref="subject" value="{{ old('subject',$campaign->subject) }}" class="form-input" required>
        </div>
    </div>

    <div>
        <label class="form-label">Type de message</label>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-1">
            @foreach([['text','Texte'],['text_image','Texte + Image'],['text_cta','Texte + CTA'],['text_image_cta','Texte + Image + CTA']] as [$val,$label])
            <label class="cursor-pointer">
                <input type="radio" name="type" value="{{ $val }}" x-model="type" class="sr-only">
                <div class="border-2 rounded-xl p-3 text-center transition-all"
                     :class="type === '{{ $val }}' ? 'border-rose-400 bg-rose-50' : 'border-gray-200 hover:border-gray-300'">
                    <p class="text-xs font-medium" :style="type === '{{ $val }}' ? 'color:var(--rose)' : 'color:var(--gray)'">{{ $label }}</p>
                </div>
            </label>
            @endforeach
        </div>
    </div>
</div>

<div class="admin-card space-y-5">
    <h3 class="text-sm font-bold pb-3" style="color:var(--dark);border-bottom:1px solid var(--border);">Contenu</h3>

    <div>
        <label class="form-label">Corps du message <span style="color:var(--rose)">*</span></label>
        <textarea name="body" x-ref="body" rows="8" class="form-input resize-none font-mono text-sm" required>{{ old('body',$campaign->body) }}</textarea>
        <div class="mt-2 flex flex-wrap gap-1.5">
            @foreach(['{prenom}','{nom}','{numero}','{type}','{ville}','{pays}','{profession}'] as $tag)
            <button type="button"
                onclick="const t=document.querySelector('[x-ref=body]');const s=t.selectionStart;t.value=t.value.slice(0,s)+'{{ $tag }}'+t.value.slice(t.selectionEnd);t.focus();t.selectionEnd=s+{{ strlen($tag) }};"
                class="text-[11px] px-2 py-0.5 rounded font-mono font-medium border transition-colors"
                style="border-color:var(--rose-mid);color:var(--rose);background:var(--rose-pale);">{{ $tag }}</button>
            @endforeach
        </div>
        <p class="text-xs mt-1.5" style="color:var(--gray);">Cliquez sur une variable pour l'insérer — remplacée automatiquement par les données du destinataire à l'envoi.</p>
    </div>

    <div x-show="hasImage()">
        <label class="form-label">Image</label>
        @if($campaign->image)
        <div class="flex items-center gap-4 mb-3">
            <img src="{{ asset('storage/'.$campaign->image) }}" alt="" class="h-20 rounded-xl object-cover shadow-sm">
            <span class="text-xs" style="color:var(--gray);">Image actuelle — choisir un fichier pour remplacer</span>
        </div>
        @endif
        <input type="file" name="image" accept="image/*" class="form-input" onchange="previewImg(this,'img-prev-e')">
        <div id="img-prev-e" class="hidden mt-3"><img id="img-prev-e-img" src="" alt="" class="h-36 rounded-xl object-cover shadow-sm"></div>
    </div>

    <div x-show="hasCta()" class="grid grid-cols-1 sm:grid-cols-2 gap-5 p-4 rounded-xl" style="background:var(--rose-pale);">
        <div>
            <label class="form-label">Label du bouton</label>
            <input type="text" name="cta_label" x-ref="ctaLabel" value="{{ old('cta_label',$campaign->cta_label) }}" class="form-input" placeholder="En savoir plus">
        </div>
        <div>
            <label class="form-label">Lien du bouton</label>
            <input type="url" name="cta_url" x-ref="ctaUrl" value="{{ old('cta_url',$campaign->cta_url) }}" class="form-input" placeholder="https://...">
        </div>
    </div>
</div>

<div class="admin-card space-y-5">
    <h3 class="text-sm font-bold pb-3" style="color:var(--dark);border-bottom:1px solid var(--border);">Destinataires</h3>
    <div>
        <label class="form-label">Cible</label>
        <select name="target_type" x-model="target" class="form-input">
            <option value="all" @selected(old('target_type',$campaign->target_type)==='all')>Tous les membres actifs</option>
            <option value="standard" @selected(old('target_type',$campaign->target_type)==='standard')>Membres Standard</option>
            <option value="gold" @selected(old('target_type',$campaign->target_type)==='gold')>Membres Gold</option>
            <option value="premium" @selected(old('target_type',$campaign->target_type)==='premium')>Membres Premium</option>
            <option value="single" @selected(old('target_type',$campaign->target_type)==='single')>Un seul membre</option>
            <option value="custom" @selected(old('target_type',$campaign->target_type)==='custom')>Sélection manuelle</option>
        </select>
    </div>
    <div x-show="target === 'single'">
        <label class="form-label">Membre</label>
        <select name="target_member_id" class="form-input">
            <option value="">-- Sélectionner --</option>
            @foreach($members as $m)
            <option value="{{ $m->id }}" @selected(old('target_member_id',$campaign->target_member_id) == $m->id)>
                {{ $m->name }} — {{ $m->email }} ({{ ucfirst($m->type) }})
            </option>
            @endforeach
        </select>
    </div>
    <div x-show="target === 'custom'">
        <label class="form-label">Sélectionner les membres</label>
        <select name="target_member_ids[]" multiple class="form-input" style="height:160px;">
            @foreach($members as $m)
            <option value="{{ $m->id }}" @if(in_array($m->id, old('target_member_ids', $campaign->target_member_ids ?? []))) selected @endif>
                {{ $m->name }} — {{ $m->email }} ({{ ucfirst($m->type) }})
            </option>
            @endforeach
        </select>
    </div>
</div>

<div class="admin-card space-y-5">
    <h3 class="text-sm font-bold pb-3" style="color:var(--dark);border-bottom:1px solid var(--border);">Envoi</h3>
    <div class="grid grid-cols-3 gap-3">
        @foreach([['draft','Brouillon','✎'],['now','Envoyer maintenant','↗'],['scheduled','Programmer','⏰']] as [$val,$label,$icon])
        <label class="cursor-pointer">
            <input type="radio" name="send_mode" value="{{ $val }}" x-model="sendMode" class="sr-only">
            <div class="border-2 rounded-xl p-4 text-center transition-all"
                 :class="sendMode === '{{ $val }}' ? 'border-rose-400 bg-rose-50' : 'border-gray-200 hover:border-gray-300'">
                <p class="text-lg mb-1">{{ $icon }}</p>
                <p class="text-sm font-bold" :style="sendMode === '{{ $val }}' ? 'color:var(--rose)' : 'color:var(--dark)'">{{ $label }}</p>
            </div>
        </label>
        @endforeach
    </div>
    <div x-show="sendMode === 'scheduled'">
        <label class="form-label">Date et heure</label>
        <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', $campaign->scheduled_at?->format('Y-m-d\TH:i')) }}" class="form-input"
               min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}">
    </div>
</div>

<div class="flex flex-wrap gap-3 pt-2">
    <button type="submit" class="btn-rose">
        <span x-text="sendMode === 'now' ? 'Envoyer la campagne' : (sendMode === 'scheduled' ? 'Programmer l\'envoi' : 'Enregistrer')"></span>
    </button>
    <a href="{{ route('admin.communication.preview', $campaign) }}" target="_blank" data-no-spinner class="btn-gold flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
        Aperçu du mail
    </a>
    <a href="{{ route('admin.communication.show', $campaign) }}" class="btn-outline">Annuler</a>
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
