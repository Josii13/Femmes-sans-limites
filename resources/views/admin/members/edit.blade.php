@extends('layouts.admin')
@section('title','Modifier '.$member->name)
@section('page-title','Modifier le membre')
@section('page-subtitle', $member->member_number)

@section('content')
<div class="max-w-2xl">
    <div class="admin-card">
        <form action="{{ route('admin.members.update', $member) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Nom complet *</label>
                    <input type="text" name="name" value="{{ old('name', $member->name) }}" class="form-input @error('name') border-red-400 @enderror">
                    @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" value="{{ old('email', $member->email) }}" class="form-input @error('email') border-red-400 @enderror">
                    @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="form-label">Profession *</label>
                <input type="text" name="profession" value="{{ old('profession', $member->profession) }}" class="form-input">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Pays *</label>
                    <input type="text" name="country" value="{{ old('country', $member->country) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Ville *</label>
                    <input type="text" name="city" value="{{ old('city', $member->city) }}" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Type de membre *</label>
                    <select name="type" class="form-input">
                        @foreach(['standard','gold','premium'] as $t)
                        <option value="{{ $t }}" @selected(old('type',$member->type)===$t)>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Statut *</label>
                    <select name="status" class="form-input">
                        <option value="active" @selected(old('status',$member->status)==='active')>Actif</option>
                        <option value="inactive" @selected(old('status',$member->status)==='inactive')>Inactif</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="form-label">Nouvelle photo (optionnel)</label>
                @if($member->photo)
                <div class="flex items-center gap-4 mb-3">
                    <img src="{{ asset('storage/'.$member->photo) }}" alt="" class="w-16 h-16 rounded-full object-cover">
                    <span class="text-xs text-gray-400">Photo actuelle</span>
                </div>
                @endif
                <input type="file" name="photo" accept="image/*" class="form-input">
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="btn-rose">Enregistrer les modifications</button>
                <a href="{{ route('admin.members.show', $member) }}" class="btn-gold">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
