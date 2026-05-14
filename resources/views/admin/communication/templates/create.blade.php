@extends('layouts.admin')
@section('title','Nouveau template')
@section('page-title','Communication')
@section('page-subtitle','Nouveau template')

@section('content')
<div class="max-w-3xl"
     x-data="{
        type: '{{ old('type','text') }}',
        hasCta(){ return ['text_cta','text_image_cta'].includes(this.type) },
     }">

@if($errors->any())
<div class="mb-6 px-4 py-3 rounded-xl text-sm" style="background:#FEF2F2;border:1px solid #FCA5A5;color:#991B1B;">
    <ul class="space-y-1">@foreach($errors->all() as $e)<li class="flex gap-2"><span style="color:var(--rose)">•</span>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form action="{{ route('admin.communication.templates.store') }}" method="POST" class="space-y-6">
@csrf

<div class="admin-card space-y-5">
    <h3 class="text-sm font-bold pb-3" style="color:var(--dark);border-bottom:1px solid var(--border);">Informations</h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div class="col-span-2 sm:col-span-1">
            <label class="form-label">Nom du template <span style="color:var(--rose)">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" class="form-input" placeholder="Ex: Newsletter mensuelle" required>
            <p class="text-xs mt-1" style="color:var(--gray);">Visible uniquement dans le back office.</p>
        </div>
        <div class="col-span-2 sm:col-span-1">
            <label class="form-label">Objet de l'email <span style="color:var(--rose)">*</span></label>
            <input type="text" name="subject" value="{{ old('subject') }}" class="form-input" placeholder="Ex: 🌸 Actualités FSL — [Mois]" required>
        </div>
    </div>

    <div>
        <label class="form-label">Type de message <span style="color:var(--rose)">*</span></label>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-1">
            @foreach([
                ['text','Texte','M4 6h16M4 12h16M4 18h7'],
                ['text_image','Texte + Image','M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ['text_cta','Texte + CTA','M13 10V3L4 14h7v7l9-11h-7z'],
                ['text_image_cta','Tout','M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'],
            ] as [$val,$label,$icon])
            <label class="cursor-pointer">
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

<div class="admin-card space-y-5">
    <h3 class="text-sm font-bold pb-3" style="color:var(--dark);border-bottom:1px solid var(--border);">Contenu</h3>

    <div>
        <label class="form-label">Corps du message <span style="color:var(--rose)">*</span></label>
        <textarea name="body" rows="10" class="form-input resize-none font-mono text-sm" placeholder="Bonjour {prenom},&#10;&#10;[Corps du message ici...]&#10;&#10;Cordialement,&#10;L'équipe FSL" required>{{ old('body') }}</textarea>
        <div class="mt-2 flex flex-wrap gap-1.5">
            @foreach(['{prenom}','{nom}','{numero}','{type}','{ville}','{pays}','{profession}'] as $tag)
            <span class="text-[11px] px-2 py-0.5 rounded font-mono font-medium border select-all cursor-pointer"
                  style="border-color:var(--rose-mid);color:var(--rose);background:var(--rose-pale);">{{ $tag }}</span>
            @endforeach
        </div>
        <p class="text-xs mt-1.5" style="color:var(--gray);">Ces variables seront remplacées automatiquement par les données du destinataire à l'envoi.</p>
        <p class="text-xs mt-1" style="color:var(--gray);">Utilisez [Prénom], [Nom de l'événement], [Mois] comme variables à remplacer lors de la création de la campagne.</p>
    </div>

    <div x-show="hasCta()" class="grid grid-cols-1 sm:grid-cols-2 gap-5 p-4 rounded-xl" style="background:var(--rose-pale);">
        <div>
            <label class="form-label">Label du bouton CTA</label>
            <input type="text" name="cta_label" value="{{ old('cta_label') }}" class="form-input" placeholder="En savoir plus">
        </div>
        <div>
            <label class="form-label">Lien du bouton CTA</label>
            <input type="url" name="cta_url" value="{{ old('cta_url') }}" class="form-input" placeholder="https://...">
        </div>
    </div>
</div>

<div class="flex gap-3 pt-2">
    <button type="submit" class="btn-rose">Enregistrer le template</button>
    <a href="{{ route('admin.communication.templates.index') }}" class="btn-gold">Annuler</a>
</div>

</form>
</div>
@endsection
