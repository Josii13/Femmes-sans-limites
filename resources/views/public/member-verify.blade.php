@extends('layouts.public')
@section('title', 'Vérification de carte — Femme Sans Limites')
@section('robots', 'noindex, nofollow')

@section('content')
@php
    $statusLabels = ['pending'=>'En attente','active'=>'Actif','rejected'=>'—','expired'=>'Expiré','suspended'=>'Suspendu'];
@endphp
<section class="min-h-[70vh] flex items-center justify-center py-20 px-5" style="background:linear-gradient(160deg,#1A0A10 0%,#0D1418 100%);">
    <div class="max-w-md w-full">
        <div class="card scale-up visible overflow-hidden">

            @if($valid)
            {{-- Carte valide --}}
            <div class="px-8 py-7 text-center" style="background:linear-gradient(135deg,var(--rose) 0%,var(--gold) 100%);">
                <div class="w-16 h-16 rounded-full bg-white/95 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-9 h-9" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                <p class="text-white font-bold text-lg">Membre vérifiée</p>
                <p class="text-white/80 text-xs mt-1">Femme Sans Limites</p>
            </div>
            <div class="px-8 py-7 text-center">
                <h1 class="text-2xl font-bold mb-1" style="color:var(--dark);font-family:'Playfair Display',serif;">{{ $member->name }}</h1>
                <p class="text-sm mb-4" style="color:var(--gray);">{{ $member->profession }}@if($member->city) · {{ $member->city }}@endif</p>
                <div class="flex items-center justify-center gap-2 mb-6">
                    <span class="badge-{{ $member->type }}">Membre {{ ucfirst($member->type) }}</span>
                </div>
                <dl class="text-sm space-y-2 text-left border-t pt-4" style="border-color:var(--border);">
                    <div class="flex justify-between"><dt style="color:var(--gray);">N° de membre</dt><dd class="font-mono font-medium" style="color:var(--dark);">{{ $member->member_number }}</dd></div>
                    <div class="flex justify-between"><dt style="color:var(--gray);">Adhère depuis</dt><dd class="font-medium" style="color:var(--dark);">{{ ($member->joined_at ?? $member->created_at)->translatedFormat('M Y') }}</dd></div>
                    @if($member->expires_at)
                    <div class="flex justify-between"><dt style="color:var(--gray);">Valable jusqu'au</dt><dd class="font-medium" style="color:var(--dark);">{{ $member->expires_at->translatedFormat('d M Y') }}</dd></div>
                    @endif
                </dl>
            </div>
            @else
            {{-- Carte invalide / inconnue / expirée --}}
            <div class="px-8 py-7 text-center" style="background:var(--warm);">
                <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3" style="background:#FEE2E2;">
                    <svg class="w-9 h-9" style="color:#DC2626;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="font-bold text-lg" style="color:var(--dark);">Carte non valide</p>
            </div>
            <div class="px-8 py-7 text-center">
                @if($member && $member->isExpired())
                <p class="text-sm" style="color:var(--gray);">L'adhésion de <strong>{{ $member->name }}</strong> a expiré le {{ $member->expires_at->translatedFormat('d M Y') }}. Merci de la renouveler auprès de l'équipe FSL.</p>
                @elseif($member)
                <p class="text-sm" style="color:var(--gray);">Cette carte n'est pas active actuellement.</p>
                @else
                <p class="text-sm" style="color:var(--gray);">Aucune carte ne correspond à ce code. Vérifie le QR ou contacte l'équipe Femme Sans Limites.</p>
                @endif
            </div>
            @endif

            <div class="px-8 py-4 text-center" style="background:var(--warm);border-top:1px solid var(--border);">
                <a href="{{ route('home') }}" class="text-xs font-medium" style="color:var(--rose);">femmesanslimites.com</a>
            </div>
        </div>
    </div>
</section>
@endsection
