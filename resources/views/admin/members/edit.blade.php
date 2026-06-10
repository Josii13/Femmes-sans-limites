@extends('layouts.admin')
@section('title','Modifier '.$member->name)
@section('page-title','Modifier le membre')
@section('page-subtitle', $member->member_number)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6" x-data="{ photoPreview: null }">

    {{-- Formulaire (3/5) --}}
    <div class="lg:col-span-3">
        <div class="admin-card">

            @if($errors->any())
            <div class="mb-5 px-4 py-3 rounded-xl text-sm" style="background:#FEF2F2;border:1px solid #FCA5A5;color:#991B1B;">
                <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            <form action="{{ route('admin.members.update', $member) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Nom complet <span style="color:var(--rose)">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $member->name) }}" class="form-input @error('name') border-red-400 @enderror" required>
                        @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Email <span style="color:var(--rose)">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $member->email) }}" class="form-input @error('email') border-red-400 @enderror" required>
                        @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="form-label">Téléphone</label>
                    <input type="tel" name="phone" value="{{ old('phone', $member->phone) }}" class="form-input" placeholder="+225 07 00 00 00 00">
                </div>

                <div>
                    <label class="form-label">Profession <span style="color:var(--rose)">*</span></label>
                    <input type="text" name="profession" value="{{ old('profession', $member->profession) }}" class="form-input" required>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Pays <span style="color:var(--rose)">*</span></label>
                        <input type="text" name="country" value="{{ old('country', $member->country) }}" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Ville <span style="color:var(--rose)">*</span></label>
                        <input type="text" name="city" value="{{ old('city', $member->city) }}" class="form-input" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Type de membre <span style="color:var(--rose)">*</span></label>
                        <select name="type" class="form-input">
                            @foreach(['standard','gold','premium'] as $t)
                            <option value="{{ $t }}" @selected(old('type',$member->type)===$t)>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs mt-1" style="color:var(--gray);">La carte sera régénérée si le type change.</p>
                    </div>
                    <div>
                        <label class="form-label">Statut <span style="color:var(--rose)">*</span></label>
                        <select name="status" class="form-input">
                            <option value="pending" @selected(old('status',$member->status)==='pending')>En attente</option>
                            <option value="active" @selected(old('status',$member->status)==='active')>Actif</option>
                            <option value="rejected" @selected(old('status',$member->status)==='rejected')>Refusé</option>
                            <option value="expired" @selected(old('status',$member->status)==='expired')>Expiré</option>
                            <option value="suspended" @selected(old('status',$member->status)==='suspended')>Suspendu</option>
                        </select>
                    </div>
                </div>

                {{-- Photo --}}
                <div>
                    <label class="form-label">Photo</label>
                    <div class="flex items-center gap-4 mb-3">
                        <div class="w-16 h-16 rounded-full overflow-hidden flex-shrink-0 flex items-center justify-center font-bold text-white text-xl"
                             style="background:{{ $member->badge_color }};">
                            @if($member->photo)
                            <img src="{{ asset('storage/'.$member->photo) }}" alt="" class="w-full h-full object-cover">
                            @else
                            {{ mb_substr($member->name, 0, 1) }}
                            @endif
                        </div>
                        <div class="flex-1">
                            <input type="file" name="photo" accept="image/*" class="form-input"
                                   @change="
                                       if ($event.target.files[0]) {
                                           const r = new FileReader();
                                           r.onload = e => photoPreview = e.target.result;
                                           r.readAsDataURL($event.target.files[0]);
                                       }
                                   ">
                            <p class="text-xs mt-1" style="color:var(--gray);">JPG/PNG max 3 Mo. La carte sera régénérée.</p>
                        </div>
                        <div x-show="photoPreview" x-cloak class="w-16 h-16 rounded-full overflow-hidden flex-shrink-0">
                            <img :src="photoPreview" alt="" class="w-full h-full object-cover">
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn-rose">Enregistrer</button>
                    <a href="{{ route('admin.members.show', $member) }}" class="btn-gold">Annuler</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Aperçu carte (2/5) --}}
    <div class="lg:col-span-2 space-y-4">
        <div class="admin-card space-y-4">
            <div>
                <h3 class="font-semibold text-sm" style="color:var(--dark);">Carte actuelle</h3>
                <p class="text-xs mt-0.5" style="color:var(--gray);">Design {{ ucfirst($member->type) }}</p>
            </div>

            @if($member->card_path && Storage::disk('public')->exists($member->card_path))
            <div class="overflow-hidden rounded-xl shadow-md" style="background:linear-gradient(135deg,#1a0a1e,#2d0e2b);">
                <img src="{{ asset('storage/'.$member->card_path) }}?v={{ filemtime(Storage::disk('public')->path($member->card_path)) }}"
                     alt="Carte membre"
                     class="w-full object-contain">
            </div>

            <div class="grid grid-cols-2 gap-2">
                <a href="{{ route('admin.members.download-card', $member) }}"
                   class="text-center text-xs py-2 rounded-lg font-medium text-white"
                   style="background:var(--gold);">
                    ⬇ Télécharger
                </a>
                <form action="{{ route('admin.members.regenerate-card', $member) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="w-full text-xs py-2 rounded-lg font-medium border transition-colors"
                            style="border-color:var(--border);color:var(--gray);">
                        ↺ Régénérer
                    </button>
                </form>
            </div>

            <p class="text-xs text-center" style="color:var(--gray);">
                {{ number_format(Storage::disk('public')->size($member->card_path) / 1024, 1) }} Ko
                · générée le {{ \Carbon\Carbon::createFromTimestamp(filemtime(Storage::disk('public')->path($member->card_path)))->format('d/m/Y') }}
            </p>
            @else
            <div class="flex flex-col items-center justify-center py-8 rounded-xl border-2 border-dashed" style="border-color:var(--border);">
                <svg class="w-8 h-8 mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                <p class="text-xs text-gray-400 mb-3">Aucune carte générée</p>
                <form action="{{ route('admin.members.regenerate-card', $member) }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs px-4 py-1.5 rounded-lg text-white" style="background:var(--rose);">
                        Générer
                    </button>
                </form>
            </div>
            @endif
        </div>

        {{-- Info régénération auto --}}
        <div class="px-4 py-3 rounded-xl text-xs" style="background:#F0FDF4;color:#166534;">
            <svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            La carte est automatiquement régénérée si vous changez le <strong>type</strong> ou la <strong>photo</strong> du membre.
        </div>
    </div>

</div>
@endsection
