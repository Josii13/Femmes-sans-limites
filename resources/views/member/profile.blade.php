@extends('layouts.member')
@section('title', 'Mon profil')

@section('content')

<h1 class="text-2xl font-bold mb-6" style="color:var(--dark);font-family:'Playfair Display',serif;">Mon profil</h1>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Informations --}}
    <form action="{{ route('member.profile.update') }}" method="POST" enctype="multipart/form-data" class="lg:col-span-2 card p-6 space-y-5">
        @csrf @method('PUT')

        @if($errors->any() && ! $errors->has('current_password'))
        <div class="p-3 rounded-xl text-sm" style="background:#FEF2F2;border:1px solid #FECACA;color:#dc2626;">
            <ul class="space-y-0.5 list-none">@foreach($errors->all() as $error)<li>• {{ $error }}</li>@endforeach</ul>
        </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="form-label">Nom complet <span style="color:var(--rose)">*</span></label>
                <input type="text" name="name" value="{{ old('name', $member->name) }}" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Email</label>
                <input type="email" value="{{ $member->email }}" class="form-input bg-gray-50" disabled>
                {{-- Non modifiable : l'email identifie l'adhésion et relie les
                     inscriptions et les achats. Le changer demande une vérification. --}}
                <p class="text-xs mt-1" style="color:var(--gray);">Pour changer d’adresse, écrivez-nous.</p>
            </div>
            <div>
                <label class="form-label">Téléphone</label>
                <input type="tel" name="phone" value="{{ old('phone', $member->phone) }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Profession <span style="color:var(--rose)">*</span></label>
                <input type="text" name="profession" value="{{ old('profession', $member->profession) }}" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Pays <span style="color:var(--rose)">*</span></label>
                <input type="text" name="country" value="{{ old('country', $member->country) }}" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Ville <span style="color:var(--rose)">*</span></label>
                <input type="text" name="city" value="{{ old('city', $member->city) }}" class="form-input" required>
            </div>
        </div>

        <div>
            <label class="form-label">Photo</label>
            <div class="flex items-center gap-4">
                @if($member->photo)
                <img src="{{ asset('storage/'.$member->photo) }}" alt="" class="w-16 h-16 rounded-full object-cover object-top flex-shrink-0">
                @endif
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="form-input py-2 text-sm cursor-pointer">
            </div>
            <p class="text-xs mt-1" style="color:var(--gray);">
                Changer votre photo régénère votre carte de membre. JPG, PNG ou WEBP — max 3 Mo.
            </p>
        </div>

        {{-- Préférences de visibilité et de contact --}}
        <div class="space-y-3 pt-2">
            <label class="flex items-start gap-3 p-3 rounded-xl cursor-pointer" style="background:var(--warm);">
                <input type="hidden" name="show_in_gallery" value="0">
                <input type="checkbox" name="show_in_gallery" value="1" class="mt-0.5"
                       @checked(old('show_in_gallery', $member->show_in_gallery))>
                <span class="text-xs leading-relaxed" style="color:var(--gray);">
                    <strong style="color:var(--dark);">Afficher ma photo sur le site public</strong><br>
                    Votre photo peut illustrer la galerie « Notre communauté ». Décochez pour l’en retirer.
                </span>
            </label>

            <label class="flex items-start gap-3 p-3 rounded-xl cursor-pointer" style="background:var(--warm);">
                <input type="hidden" name="marketing_opt_in" value="0">
                <input type="checkbox" name="marketing_opt_in" value="1" class="mt-0.5"
                       @checked(old('marketing_opt_in', ! $member->hasOptedOutOfMarketing()))>
                <span class="text-xs leading-relaxed" style="color:var(--gray);">
                    <strong style="color:var(--dark);">Recevoir les communications de la communauté</strong><br>
                    Actualités, invitations et nouveautés. Les messages liés à votre adhésion
                    vous parviendront dans tous les cas.
                </span>
            </label>
        </div>

        <div class="pt-2">
            <button type="submit" class="btn-rose">Enregistrer</button>
        </div>
    </form>

    {{-- Mot de passe et adhésion --}}
    <div class="space-y-6">
        <form action="{{ route('member.password.update') }}" method="POST" class="card p-6 space-y-4">
            @csrf @method('PUT')
            <h2 class="font-bold" style="color:var(--dark);font-family:'Playfair Display',serif;">Mot de passe</h2>

            @error('current_password')
            <p class="text-xs p-2.5 rounded-lg" style="background:#FEF2F2;color:#dc2626;">{{ $message }}</p>
            @enderror

            <div>
                <label class="form-label">Mot de passe actuel</label>
                <input type="password" name="current_password" class="form-input" autocomplete="current-password" required>
            </div>
            <div>
                <label class="form-label">Nouveau mot de passe</label>
                <input type="password" name="password" class="form-input" autocomplete="new-password" minlength="8" required>
            </div>
            <div>
                <label class="form-label">Confirmation</label>
                <input type="password" name="password_confirmation" class="form-input" autocomplete="new-password" minlength="8" required>
            </div>

            <button type="submit" class="btn-rose w-full text-sm">Changer mon mot de passe</button>
        </form>

        <div class="card p-6">
            <h2 class="font-bold mb-3" style="color:var(--dark);font-family:'Playfair Display',serif;">Mon adhésion</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between gap-3">
                    <dt style="color:var(--gray);">Numéro</dt>
                    <dd class="font-semibold text-right" style="color:var(--dark);">{{ $member->member_number }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt style="color:var(--gray);">Niveau</dt>
                    <dd class="font-semibold" style="color:{{ $member->badge_color }};">{{ ucfirst($member->type) }}</dd>
                </div>
                @if($member->joined_at)
                <div class="flex justify-between gap-3">
                    <dt style="color:var(--gray);">Membre depuis</dt>
                    <dd class="font-semibold text-right" style="color:var(--dark);">{{ $member->joined_at->translatedFormat('F Y') }}</dd>
                </div>
                @endif
                @if($member->expires_at)
                <div class="flex justify-between gap-3">
                    <dt style="color:var(--gray);">Échéance</dt>
                    <dd class="font-semibold text-right" style="color:var(--dark);">{{ $member->expires_at->translatedFormat('d/m/Y') }}</dd>
                </div>
                @endif
            </dl>

            <a href="{{ route('member.renewal') }}" class="btn-rose w-full text-sm mt-4 inline-block text-center">
                Renouveler ou changer de niveau
            </a>
        </div>
    </div>
</div>

@endsection
