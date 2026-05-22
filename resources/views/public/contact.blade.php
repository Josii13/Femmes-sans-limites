@extends('layouts.public')
@section('title', 'Contact — Femme Sans Limites')
@section('description', 'Contactez l\'équipe Femme Sans Limites pour toute question, partenariat ou suggestion.')
@section('robots', 'noindex, follow')

@section('content')

{{-- Hero --}}
<section class="pt-28 pb-20 bg-white relative overflow-hidden">
    <div class="absolute bottom-0 left-0 w-72 h-72 rounded-full pointer-events-none" style="background:radial-gradient(circle,var(--gold-pale),transparent 70%);transform:translate(-30%,30%);"></div>
    <div class="max-w-7xl mx-auto px-5 lg:px-8">
        <div class="max-w-2xl fade-up">
            <span class="section-label">Contact</span>
            <h1 class="text-5xl lg:text-6xl font-bold mt-4 mb-6" style="color:var(--dark);font-family:'Playfair Display',serif;">
                Parlons-nous
            </h1>
            <p class="text-xl leading-relaxed mb-10" style="color:var(--gray);">
                Une question, une idée de partenariat ou simplement envie d'échanger ? Notre équipe est là pour vous écouter.
            </p>
            <button @click="$store.modal.contact = true" class="btn-rose text-base px-10 py-4">
                Envoyer un message
            </button>
        </div>
    </div>
</section>

{{-- Contact infos --}}
<section class="py-20 lg:py-28" style="background:var(--warm);">
    <div class="max-w-7xl mx-auto px-5 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <div class="card-hover p-8 fade-up">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-6" style="background:var(--rose-pale);">
                    <svg class="w-6 h-6" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-lg font-bold mb-2" style="color:var(--dark);font-family:'Playfair Display',serif;">Email</h3>
                <p class="text-sm mb-1" style="color:var(--gray);">Pour toute demande générale</p>
                <a href="mailto:contact@femmessanslimites.com" class="text-sm font-medium" style="color:var(--rose);">contact@femmessanslimites.com</a>
            </div>

            <div class="card-hover p-8 fade-up">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-6" style="background:var(--gold-pale);">
                    <svg class="w-6 h-6" style="color:var(--gold);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2v-2H6v2a2 2 0 002 2zM12 3a7 7 0 017 7c0 2.38-1.19 4.47-3 5.74V17H8v-1.26C6.19 14.47 5 12.38 5 10a7 7 0 017-7z"/></svg>
                </div>
                <h3 class="text-lg font-bold mb-2" style="color:var(--dark);font-family:'Playfair Display',serif;">Partenariats</h3>
                <p class="text-sm mb-1" style="color:var(--gray);">Entreprises, organisations, médias</p>
                <a href="mailto:partenariats@femmessanslimites.com" class="text-sm font-medium" style="color:var(--gold-dark);">partenariats@femmessanslimites.com</a>
            </div>

            <div class="card-hover p-8 fade-up md:col-span-2 lg:col-span-1">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-6" style="background:var(--rose-pale);">
                    <svg class="w-6 h-6" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <h3 class="text-lg font-bold mb-2" style="color:var(--dark);font-family:'Playfair Display',serif;">Réseaux sociaux</h3>
                <p class="text-sm mb-4" style="color:var(--gray);">Suivez-nous et échangez avec la communauté</p>
                <div class="flex gap-3">
                    <a href="#" class="w-9 h-9 rounded-lg flex items-center justify-center transition-colors" style="background:var(--rose-pale);" onmouseover="this.style.background='var(--rose)';this.querySelector('svg').style.color='white'" onmouseout="this.style.background='var(--rose-pale)';this.querySelector('svg').style.color='var(--rose)'">
                        <svg class="w-4 h-4" style="color:var(--rose);" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="#" class="w-9 h-9 rounded-lg flex items-center justify-center transition-colors" style="background:var(--rose-pale);" onmouseover="this.style.background='var(--rose)';this.querySelector('svg').style.color='white'" onmouseout="this.style.background='var(--rose-pale)';this.querySelector('svg').style.color='var(--rose)'">
                        <svg class="w-4 h-4" style="color:var(--rose);" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                    <a href="#" class="w-9 h-9 rounded-lg flex items-center justify-center transition-colors" style="background:var(--rose-pale);" onmouseover="this.style.background='var(--rose)';this.querySelector('svg').style.color='white'" onmouseout="this.style.background='var(--rose-pale)';this.querySelector('svg').style.color='var(--rose)'">
                        <svg class="w-4 h-4" style="color:var(--rose);" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.27 8.27 0 004.84 1.55V6.79a4.85 4.85 0 01-1.07-.1z"/></svg>
                    </a>
                    <a href="#" class="w-9 h-9 rounded-lg flex items-center justify-center transition-colors" style="background:var(--rose-pale);" onmouseover="this.style.background='#25D366';this.querySelector('svg').style.color='white'" onmouseout="this.style.background='var(--rose-pale)';this.querySelector('svg').style.color='var(--rose)'">
                        <svg class="w-4 h-4" style="color:var(--rose);" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="py-20 lg:py-28 bg-white">
    <div class="max-w-3xl mx-auto px-5 lg:px-8">
        <div class="text-center mb-14 fade-up">
            <span class="section-label">Questions fréquentes</span>
            <h2 class="text-4xl font-bold mt-3" style="color:var(--dark);font-family:'Playfair Display',serif;">On vous répond</h2>
        </div>

        <div class="space-y-4 fade-up" x-data="{ open: null }">
            @foreach([
                ['Comment rejoindre FSL ?', 'Clique sur le bouton "Rejoindre la communauté" depuis n\'importe quelle page et remplis le formulaire en 2 minutes. Notre équipe valide ta candidature sous 48h.'],
                ['L\'adhésion est-elle payante ?', 'L\'adhésion Standard est accessible à toutes. Les niveaux Gold et Premium sont définis avec l\'équipe selon ton profil et ton engagement au sein du mouvement.'],
                ['Comment s\'inscrire à un événement ?', 'Rends-toi sur la page Événements, choisis l\'événement qui t\'intéresse et remplis le formulaire d\'inscription. Pour les événements payants, un lien de paiement te sera envoyé par email.'],
                ['FSL est-elle présente dans mon pays ?', 'FSL est présente en Afrique et dans la diaspora. Nos événements se tiennent en présentiel dans plusieurs capitales africaines et en ligne pour les membres de la diaspora.'],
                ['Je suis dans la diaspora, puis-je adhérer ?', 'Absolument ! FSL accueille les femmes africaines du monde entier. De nombreuses activités sont disponibles en ligne et nous organisons des rencontres dans plusieurs villes européennes et nord-américaines.'],
            ] as [$q, $a])
            <div class="rounded-2xl overflow-hidden" style="border:1px solid var(--border);">
                <button class="w-full text-left px-6 py-5 flex items-center justify-between gap-4" @click="open = open === {{ $loop->index }} ? null : {{ $loop->index }}" style="background:white;">
                    <span class="text-base font-semibold" style="color:var(--dark);">{{ $q }}</span>
                    <svg class="w-5 h-5 flex-shrink-0 transition-transform duration-200" :class="open === {{ $loop->index }} ? 'rotate-45' : ''" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
                <div x-show="open === {{ $loop->index }}" x-collapse class="px-6 pb-5" style="background:var(--warm);">
                    <p class="text-sm leading-relaxed pt-3" style="color:var(--gray);">{{ $a }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-20" style="background:var(--dark);">
    <div class="max-w-xl mx-auto px-5 text-center fade-up">
        <h2 class="text-3xl font-bold mb-4 text-white" style="font-family:'Playfair Display',serif;">Vous n'avez pas trouvé votre réponse ?</h2>
        <p class="text-base mb-8" style="color:rgba(255,255,255,0.6);">Écrivez-nous directement, nous répondons sous 48h.</p>
        <button @click="$store.modal.contact = true" class="btn-rose px-10 py-4 text-base">
            Nous contacter
        </button>
    </div>
</section>

@endsection
