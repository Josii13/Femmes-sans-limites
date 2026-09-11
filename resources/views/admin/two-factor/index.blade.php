@extends('layouts.admin')
@section('title','Double authentification')
@section('page-title','Double authentification')
@section('page-subtitle','Protéger votre accès au back-office')

@section('content')
<div class="max-w-2xl space-y-6">

    {{-- Codes de secours : affichés UNE SEULE FOIS, juste après génération. --}}
    @if(session('two_factor.recovery_codes'))
    <div class="admin-card" style="border:2px solid var(--rose);">
        <p class="font-bold mb-2" style="color:var(--dark);">Vos codes de secours</p>
        <p class="text-sm mb-4" style="color:var(--gray);">
            Notez-les maintenant et conservez-les hors de votre téléphone. Ils ne seront plus
            jamais affichés. Chacun ne fonctionne qu’une fois, et ils sont votre seul recours
            si vous perdez votre téléphone.
        </p>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 font-mono text-sm">
            @foreach(session('two_factor.recovery_codes') as $code)
            <span class="px-3 py-2 rounded-lg text-center" style="background:var(--warm);color:var(--dark);">{{ $code }}</span>
            @endforeach
        </div>
    </div>
    @endif

    @if($user->hasTwoFactorEnabled())

    <div class="admin-card">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#05966918;">
                <svg class="w-5 h-5" style="color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
            </div>
            <div>
                <p class="font-bold" style="color:var(--dark);">Double authentification activée</p>
                <p class="text-sm mt-1" style="color:var(--gray);">
                    Depuis le {{ $user->two_factor_confirmed_at->translatedFormat('d F Y') }}.
                    Un code vous sera demandé à chaque connexion.
                </p>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <p class="font-semibold mb-2" style="color:var(--dark);">Codes de secours</p>
        <p class="text-sm mb-4" style="color:var(--gray);">
            Il vous en reste {{ count((array) $user->two_factor_recovery_codes) }}.
            Régénérez-les si vous les avez perdus ou presque tous utilisés — les anciens cesseront de fonctionner.
        </p>
        <form action="{{ route('admin.two-factor.recovery') }}" method="POST">
            @csrf
            <button type="submit" class="btn-gold text-sm">Générer de nouveaux codes</button>
        </form>
    </div>

    <div class="admin-card">
        <p class="font-semibold mb-2" style="color:var(--dark);">Désactiver</p>
        <p class="text-sm mb-4" style="color:var(--gray);">
            Votre compte ne sera plus protégé que par son mot de passe.
        </p>
        <form action="{{ route('admin.two-factor.destroy') }}" method="POST" class="flex flex-wrap items-end gap-3">
            @csrf @method('DELETE')
            <div class="flex-1 min-w-[200px]">
                <label class="form-label">Confirmez avec votre mot de passe</label>
                <input type="password" name="password" class="form-input @error('password') border-red-400 @enderror" required>
                @error('password')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="text-sm px-5 py-2.5 rounded-xl text-red-500 hover:bg-red-50" style="border:1px solid #FECACA;">
                Désactiver
            </button>
        </form>
    </div>

    @elseif($pendingSecret)

    <div class="admin-card">
        <p class="font-bold mb-1" style="color:var(--dark);">1. Scannez ce code</p>
        <p class="text-sm mb-4" style="color:var(--gray);">
            Avec Google Authenticator, Authy, 1Password ou toute autre application de codes temporaires.
        </p>

        <div class="flex flex-wrap items-start gap-6">
            <div class="p-3 rounded-xl bg-white" style="border:1px solid var(--border);">{!! $qrCode !!}</div>

            <div class="min-w-[220px]">
                <p class="text-xs mb-1" style="color:var(--gray);">Impossible de scanner ? Saisissez cette clé :</p>
                <code class="block px-3 py-2 rounded-lg text-sm break-all" style="background:var(--warm);color:var(--dark);">{{ $pendingSecret }}</code>
            </div>
        </div>

        <form action="{{ route('admin.two-factor.confirm') }}" method="POST" class="mt-6 pt-5" style="border-top:1px solid var(--border);">
            @csrf
            <p class="font-bold mb-1" style="color:var(--dark);">2. Saisissez le code affiché</p>
            {{-- On n'enregistre le second facteur qu'après vérification : sans cela,
                 une erreur de scan enfermerait la personne hors du back-office. --}}
            <p class="text-sm mb-3" style="color:var(--gray);">
                La double authentification ne sera activée qu’une fois ce code validé.
            </p>
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6"
                           class="form-input text-center tracking-[0.3em] font-mono @error('code') border-red-400 @enderror"
                           style="max-width:160px;" placeholder="000000" required autofocus>
                </div>
                <button type="submit" class="btn-rose">Activer</button>
            </div>
            @error('code')<p class="text-xs text-red-500 mt-2">{{ $message }}</p>@enderror
        </form>
    </div>

    @else

    <div class="admin-card">
        <p class="font-bold mb-2" style="color:var(--dark);">Votre compte n’est protégé que par un mot de passe</p>
        <p class="text-sm mb-5 leading-relaxed" style="color:var(--gray);">
            Le back-office contient les données personnelles des membres et les références de
            paiement. La double authentification ajoute un code temporaire, généré par votre
            téléphone, en plus du mot de passe.
        </p>
        <form action="{{ route('admin.two-factor.start') }}" method="POST">
            @csrf
            <button type="submit" class="btn-rose">Activer la double authentification</button>
        </form>
    </div>

    @endif
</div>
@endsection
