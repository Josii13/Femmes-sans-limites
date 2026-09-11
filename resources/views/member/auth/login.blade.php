@extends('member.auth.layout')
@section('title', 'Connexion')
@section('heading', 'Mon espace membre')
@section('subheading', 'Retrouvez votre carte, vos événements et la communauté.')

@section('form')
<form method="POST" action="{{ route('member.login.store') }}" class="space-y-4">
    @csrf

    <div>
        <label class="form-label">Adresse email</label>
        <input type="email" name="email" value="{{ old('email') }}" class="form-input"
               autocomplete="username" placeholder="vous@email.com" required autofocus>
    </div>

    <div>
        <label class="form-label">Mot de passe</label>
        <input type="password" name="password" class="form-input" autocomplete="current-password" required>
    </div>

    <div class="flex items-center justify-between gap-3">
        <label class="flex items-center gap-2 text-sm cursor-pointer" style="color:var(--gray);">
            <input type="checkbox" name="remember" value="1">
            Rester connectée
        </label>
        <a href="{{ route('member.password.request') }}" class="text-sm hover:underline" style="color:var(--rose);">
            Mot de passe oublié ?
        </a>
    </div>

    <button type="submit" class="btn-rose w-full py-3">Se connecter</button>
</form>

<p class="text-xs text-center mt-5 leading-relaxed" style="color:var(--gray);">
    Première connexion ? Utilisez « Mot de passe oublié » pour définir le vôtre.<br>
    Pas encore membre ? <a href="{{ route('membership.join') }}" style="color:var(--rose);" class="hover:underline">Rejoignez la communauté</a>.
</p>
@endsection
