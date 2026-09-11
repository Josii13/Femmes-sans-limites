@extends('member.auth.layout')
@section('title', 'Mot de passe oublié')
@section('heading', 'Définir mon mot de passe')
@section('subheading', 'Nous vous envoyons un lien pour en choisir un nouveau.')

@section('form')
<form method="POST" action="{{ route('member.password.email') }}" class="space-y-4">
    @csrf

    <div>
        <label class="form-label">Adresse email de votre adhésion</label>
        <input type="email" name="email" value="{{ old('email') }}" class="form-input"
               placeholder="vous@email.com" required autofocus>
    </div>

    <button type="submit" class="btn-rose w-full py-3">Recevoir le lien</button>
</form>

<p class="text-xs text-center mt-5" style="color:var(--gray);">
    <a href="{{ route('member.login') }}" class="hover:underline">← Retour à la connexion</a>
</p>
@endsection
