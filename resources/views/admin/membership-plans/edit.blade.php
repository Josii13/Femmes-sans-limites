@extends('layouts.admin')
@section('title','Modifier un tarif')
@section('page-title','Tarif — '.$plan->name)
@section('page-subtitle','Niveau « '.$plan->type.' »')

@section('content')
<div class="max-w-xl">
    <form action="{{ route('admin.membership-plans.update', $plan) }}" method="POST" class="admin-card space-y-5">
        @csrf @method('PUT')

        @if($errors->any())
        <div class="p-3 rounded-xl text-sm" style="background:#FEF2F2;border:1px solid #FECACA;color:#dc2626;">
            <ul class="space-y-0.5 list-none">@foreach($errors->all() as $error)<li>• {{ $error }}</li>@endforeach</ul>
        </div>
        @endif

        <div>
            <label class="form-label">Nom affiché <span style="color:var(--rose)">*</span></label>
            <input type="text" name="name" value="{{ old('name', $plan->name) }}" class="form-input" required>
        </div>

        <div>
            <label class="form-label">Description</label>
            <textarea name="description" rows="3" class="form-input resize-none"
                      placeholder="Ce que ce niveau apporte, en une phrase.">{{ old('description', $plan->description) }}</textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="form-label">Tarif</label>
                <input type="number" step="1" min="0" name="price"
                       value="{{ old('price', $plan->price !== null ? (int) $plan->price : '') }}" class="form-input" placeholder="15000">
                <p class="text-xs text-gray-400 mt-1">Vide = gratuit.</p>
            </div>
            <div>
                <label class="form-label">Devise <span style="color:var(--rose)">*</span></label>
                <input type="text" name="currency" value="{{ old('currency', $plan->currency) }}" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Durée (mois) <span style="color:var(--rose)">*</span></label>
                <input type="number" min="1" max="60" name="duration_months"
                       value="{{ old('duration_months', $plan->duration_months) }}" class="form-input" required>
            </div>
        </div>

        <div>
            <label class="form-label">Ordre d’affichage</label>
            <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $plan->sort_order) }}" class="form-input">
        </div>

        <label class="flex items-start gap-3 p-3 rounded-xl cursor-pointer" style="background:var(--warm);">
            {{-- Champ caché : une case décochée n’est pas soumise par le navigateur. --}}
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="mt-0.5" @checked(old('is_active', $plan->is_active))>
            <span class="text-xs leading-relaxed" style="color:var(--gray);">
                <strong style="color:var(--dark);">Proposer ce niveau aux membres</strong><br>
                Décochez pour le retirer de la page de renouvellement sans supprimer le niveau,
                qui reste inscrit sur les cartes existantes.
            </span>
        </label>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-rose">Enregistrer</button>
            <a href="{{ route('admin.membership-plans.index') }}" class="btn-gold">Annuler</a>
        </div>
    </form>
</div>
@endsection
