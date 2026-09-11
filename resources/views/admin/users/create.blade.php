@extends('layouts.admin')
@section('title','Nouveau compte')
@section('page-title','Nouveau compte')
@section('page-subtitle','Donner accès au back-office')

@section('content')
<div class="max-w-xl">
    <form action="{{ route('admin.users.store') }}" method="POST" class="admin-card space-y-5">
        @csrf

        @if($errors->any())
        <div class="p-3 rounded-xl text-sm" style="background:#FEF2F2;border:1px solid #FECACA;color:#dc2626;">
            <ul class="space-y-0.5 list-none">@foreach($errors->all() as $error)<li>• {{ $error }}</li>@endforeach</ul>
        </div>
        @endif

        <div>
            <label class="form-label">Nom <span style="color:var(--rose)">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" class="form-input" required>
        </div>

        <div>
            <label class="form-label">Email <span style="color:var(--rose)">*</span></label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-input" required>
            {{-- Aucun mot de passe n'est choisi ici : un mot de passe transmis par
                 un tiers finit partagé. La personne définit le sien via le lien reçu. --}}
            <p class="text-xs mt-1" style="color:var(--gray);">
                Un email sera envoyé à cette adresse pour définir le mot de passe.
            </p>
        </div>

        <div>
            <label class="form-label">Rôle <span style="color:var(--rose)">*</span></label>
            <select name="role" class="form-input" required>
                @foreach(\App\Models\User::ROLE_LABELS as $value => $label)
                <option value="{{ $value }}" @selected(old('role', \App\Models\User::ROLE_EDITOR) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <p class="text-xs mt-1" style="color:var(--gray);">
                Accordez le minimum nécessaire : le rôle se modifie à tout moment.
            </p>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-rose">Créer le compte</button>
            <a href="{{ route('admin.users.index') }}" class="btn-gold">Annuler</a>
        </div>
    </form>
</div>
@endsection
