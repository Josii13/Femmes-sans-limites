@extends('member.auth.layout')
@section('title', 'Nouveau mot de passe')
@section('heading', 'Choisir mon mot de passe')
@section('subheading', 'Au moins 8 caractères.')

@section('form')
<form method="POST" action="{{ route('member.password.store') }}" class="space-y-4">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div>
        <label class="form-label">Adresse email</label>
        <input type="email" name="email" value="{{ old('email', $email) }}" class="form-input"
               autocomplete="username" required readonly>
    </div>

    <div>
        <label class="form-label">Nouveau mot de passe</label>
        <input type="password" name="password" class="form-input" autocomplete="new-password" minlength="8" required autofocus>
    </div>

    <div>
        <label class="form-label">Confirmation</label>
        <input type="password" name="password_confirmation" class="form-input" autocomplete="new-password" minlength="8" required>
    </div>

    <button type="submit" class="btn-rose w-full py-3">Enregistrer et se connecter</button>
</form>
@endsection
