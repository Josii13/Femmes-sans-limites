@extends('layouts.admin')
@section('title','Modifier le compte')
@section('page-title','Modifier le compte')
@section('page-subtitle', $user->name)

@section('content')
<div class="max-w-xl">
    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="admin-card space-y-5">
        @csrf @method('PUT')

        @if($errors->any())
        <div class="p-3 rounded-xl text-sm" style="background:#FEF2F2;border:1px solid #FECACA;color:#dc2626;">
            <ul class="space-y-0.5 list-none">@foreach($errors->all() as $error)<li>• {{ $error }}</li>@endforeach</ul>
        </div>
        @endif

        <div>
            <label class="form-label">Nom <span style="color:var(--rose)">*</span></label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-input" required>
        </div>

        <div>
            <label class="form-label">Email <span style="color:var(--rose)">*</span></label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-input" required>
        </div>

        <div>
            <label class="form-label">Rôle <span style="color:var(--rose)">*</span></label>
            <select name="role" class="form-input" required>
                @foreach(\App\Models\User::ROLE_LABELS as $value => $label)
                <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @if($user->id === auth()->id() && $user->isOwner())
            <p class="text-xs mt-1" style="color:#B45309;">
                Vous ne pouvez pas retirer votre propre rôle de propriétaire : confiez-le d’abord à quelqu’un d’autre.
            </p>
            @endif
        </div>

        <div class="pt-4" style="border-top:1px solid var(--border);">
            <p class="text-sm font-semibold mb-3" style="color:var(--dark);">Réinitialiser le mot de passe</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Nouveau mot de passe</label>
                    <input type="password" name="password" class="form-input" autocomplete="new-password" minlength="8">
                </div>
                <div>
                    <label class="form-label">Confirmation</label>
                    <input type="password" name="password_confirmation" class="form-input" autocomplete="new-password" minlength="8">
                </div>
            </div>
            <p class="text-xs mt-1" style="color:var(--gray);">Laissez vide pour ne pas le changer.</p>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-rose">Enregistrer</button>
            <a href="{{ route('admin.users.index') }}" class="btn-gold">Annuler</a>
        </div>
    </form>
</div>
@endsection
