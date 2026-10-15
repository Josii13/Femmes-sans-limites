@extends('layouts.admin')
@section('title','Test des emails')
@section('page-title','Test des emails')
@section('page-subtitle', collect(\App\Support\MailSamples::catalog())->count().' types d\'emails envoyés par le site')

@section('content')
@php
    $sent = session('sent', []);
    $failed = session('failed', []);
    $labels = \App\Support\MailSamples::catalog();
@endphp

<div x-data="{ email: '{{ session('tested_email', '') }}', all: false }">

    {{-- Résultat du dernier envoi --}}
    @if($sent || $failed)
    <div class="admin-card p-5 mb-6">
        <h3 class="font-semibold text-sm mb-3" style="color:var(--dark);">
            Résultat vers {{ session('tested_email') }}
        </h3>

        @if($sent)
        <div class="px-4 py-3 rounded-xl text-sm mb-3" style="background:#D1FAE5;color:#065F46;">
            <strong>{{ count($sent) }} email(s) envoyé(s)</strong> —
            {{ collect($sent)->map(fn ($k) => $labels[$k]['label'] ?? $k)->implode(', ') }}
        </div>
        @endif

        @foreach($failed as $key => $message)
        <div class="px-4 py-3 rounded-xl text-sm mb-2" style="background:#FEF2F2;border:1px solid #FCA5A5;color:#991B1B;">
            <strong>{{ $labels[$key]['label'] ?? $key }}</strong><br>
            <span class="font-mono text-xs">{{ $message }}</span>
        </div>
        @endforeach

        @if($sent && ! $failed)
        <p class="text-xs" style="color:var(--gray);">
            Acceptés par le serveur SMTP. S'ils n'arrivent pas, le problème est la délivrabilité
            (SPF/DKIM, indésirables) et non la configuration.
        </p>
        @endif
    </div>
    @endif

    @if($errors->any())
    <div class="admin-card p-4 mb-6" style="background:#FEF2F2;border:1px solid #FCA5A5;color:#991B1B;">
        <ul class="space-y-0.5 list-none text-sm">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('admin.emails.send') }}" method="POST">
        @csrf

        {{-- Adresse de test --}}
        <div class="admin-card p-5 mb-6">
            <h3 class="font-semibold text-sm mb-1" style="color:var(--dark);">Adresse de test</h3>
            <p class="text-xs mb-4" style="color:var(--gray);">
                Tous les emails cochés partiront à cette adresse, avec des données d'exemple.
                Rien n'est enregistré en base : aucune fausse inscrite, aucun faux paiement.
            </p>
            <input type="email" name="email" x-model="email" value="{{ old('email', session('tested_email')) }}"
                   class="form-input" placeholder="vous@exemple.com" required>
        </div>

        {{-- Catalogue --}}
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs" style="color:var(--gray);">Cochez les emails à envoyer.</p>
            <label class="text-xs font-medium cursor-pointer" style="color:var(--rose);">
                <input type="checkbox" x-model="all"
                       @change="document.querySelectorAll('input[name=\'keys[]\']').forEach(c => c.checked = all)"
                       class="mr-1.5 align-middle">
                Tout sélectionner
            </label>
        </div>

        @foreach($catalog as $group => $items)
        <div class="admin-card p-5 mb-4">
            <p class="text-[10px] font-bold uppercase tracking-wider mb-3" style="color:var(--gray);">{{ $group }}</p>
            <div class="space-y-2">
                @foreach($items as $key => $item)
                <div class="flex items-start justify-between gap-3 py-2.5 px-3 rounded-xl" style="background:#F8F7F9;">
                    <label class="flex items-start gap-3 cursor-pointer min-w-0">
                        <input type="checkbox" name="keys[]" value="{{ $key }}" class="mt-0.5 flex-shrink-0"
                               @checked(in_array($key, old('keys', []), true))>
                        <span class="min-w-0">
                            <span class="text-sm font-medium block" style="color:var(--dark);">{{ $item['label'] }}</span>
                            <span class="text-xs" style="color:var(--gray);">{{ $item['description'] }}</span>
                        </span>
                    </label>
                    {{-- L'aperçu rend l'email dans le navigateur, sans rien envoyer :
                         c'est ce qu'on veut en relisant une tournure de phrase. --}}
                    <a href="{{ route('admin.emails.preview', $key) }}" target="_blank" rel="noopener"
                       class="text-xs px-3 py-1.5 rounded-lg flex-shrink-0"
                       style="background:#fff;border:1px solid var(--border);color:var(--gray);">Aperçu</a>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <button type="submit" class="btn-rose w-full justify-center py-3.5 mt-2">
            Envoyer les emails cochés
        </button>
    </form>
</div>
@endsection
