@extends('layouts.public')
@section('title', 'Candidature envoyée — Femme Sans Limites')

@section('content')
<section class="min-h-screen flex items-center justify-center px-6" style="background:linear-gradient(160deg,#1A0A10 0%,#0D1418 100%);">
    <div class="max-w-lg w-full text-center">

        <!-- <div class="w-20 h-20 rounded-full mx-auto mb-6 flex items-center justify-center text-3xl" style="background:#D91E6E22;border:2px solid #D91E6E44;">
            ✨
        </div> -->

        <h1 class="text-3xl font-bold text-white mb-4">Candidature reçue !</h1>

        <p class="mb-4" style="color:rgba(255,255,255,0.6);">
            Merci d'avoir postulé pour rejoindre <strong style="color:var(--rose);">Femme Sans Limites</strong>.
            Notre équipe examinera ta candidature et te contactera par email sous <strong style="color:#fff;">48h ouvrées</strong>.
        </p>

        <p class="text-sm mb-10" style="color:rgba(255,255,255,0.4);">
            Un email de confirmation vient d'être envoyé à ton adresse.
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('home') }}" class="btn-rose px-8 py-3">Retour à l'accueil</a>
            <a href="{{ route('events.index') }}" class="btn-gold px-8 py-3">Voir nos événements</a>
        </div>

        <div class="mt-12 p-6 rounded-2xl text-left" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);">
            <p class="text-xs font-semibold uppercase tracking-widest mb-4" style="color:var(--gold);">En attendant</p>
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <span style="color:var(--rose);">→</span>
                    <span class="text-sm" style="color:rgba(255,255,255,0.6);">Rejoins nos réseaux sociaux pour rester informée</span>
                </div>
                <div class="flex items-center gap-3">
                    <span style="color:var(--rose);">→</span>
                    <span class="text-sm" style="color:rgba(255,255,255,0.6);">Consulte nos événements à venir</span>
                </div>
                <div class="flex items-center gap-3">
                    <span style="color:var(--rose);">→</span>
                    <a href="{{ route('contact') }}" class="text-sm underline" style="color:rgba(255,255,255,0.6);">Contacte-nous si tu as des questions</a>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection
