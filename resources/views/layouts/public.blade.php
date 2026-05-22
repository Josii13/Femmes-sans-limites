<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ══ Titre & description ══ --}}
    <title>@yield('title', 'Femmes Sans Limites')</title>
    <meta name="description" content="@yield('description', 'Femmes Sans Limites — Plateforme de transformation et d\'empowerment féminin. Brise tes limites, révèle ta puissance.')">
    <meta name="robots" content="@yield('robots', 'index, follow')">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="theme-color" content="#D91E6E">

    {{-- ══ Favicon ══ --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">

    {{-- ══ Open Graph ══ --}}
    <meta property="og:site_name" content="Femmes Sans Limites">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('title', 'Femmes Sans Limites')">
    <meta property="og:description" content="@yield('description', 'Femmes Sans Limites — Plateforme de transformation et d\'empowerment féminin. Brise tes limites, révèle ta puissance.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('og_image', asset('logo_FSL.png'))">
    <meta property="og:image:alt" content="@yield('title', 'Femmes Sans Limites')">

    {{-- ══ Twitter Card ══ --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Femmes Sans Limites')">
    <meta name="twitter:description" content="@yield('description', 'Femmes Sans Limites — Plateforme de transformation et d\'empowerment féminin. Brise tes limites, révèle ta puissance.')">
    <meta name="twitter:image" content="@yield('og_image', asset('logo_FSL.png'))">

    {{-- ══ JSON-LD Organisation ══ --}}
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "Organization",
      "name": "Femmes Sans Limites",
      "url": "{{ config('app.url') }}",
      "logo": "{{ asset('logo_FSL.png') }}",
      "description": "Plateforme de transformation et d'empowerment féminin pour les femmes d'Afrique et de la diaspora.",
      "sameAs": []
    }
    </script>

    @stack('seo')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('modal', {
                join: {{ session('modal') === 'join' ? 'true' : 'false' }},
                contact: {{ session('modal') === 'contact' ? 'true' : 'false' }},
            })
        })
    </script>
    <style>
    #fsl-bar{position:fixed;top:0;left:0;height:3px;z-index:9999;width:0%;opacity:0;pointer-events:none;background:linear-gradient(90deg,#D91E6E,#F472B6,#D91E6E);background-size:200% 100%;transition:opacity .15s;}
    #fsl-bar.active{opacity:1;animation:fsl-p 14s cubic-bezier(.05,.01,0,1) forwards,fsl-s 1.8s linear infinite;}
    @keyframes fsl-p{from{width:0%}to{width:90%}}
    @keyframes fsl-s{from{background-position:0% 0}to{background-position:-200% 0}}
    </style>
</head>
<body x-data class="antialiased">
<div id="fsl-bar" aria-hidden="true"></div>

{{-- ══════════════════ HEADER ══════════════════ --}}
<div x-data="{ open: false }" class="fixed top-0 left-0 right-0 z-40">

    {{-- Barre principale — hauteur fixe 64px --}}
    <header class="h-16 bg-white flex items-center" style="border-bottom:1px solid var(--border);box-shadow:0 1px 3px rgba(0,0,0,0.06);">
        <div class="max-w-7xl mx-auto px-5 lg:px-8 w-full flex items-center justify-between">

            <a href="{{ route('home') }}" class="flex items-center flex-shrink-0 rounded-xl py-1 px-2 -ml-2 transition-opacity hover:opacity-80" style="background:#FDF0F5;">
                <img src="{{ asset('logo_FSL.png') }}" alt="FSL" class="h-10 w-auto">
            </a>

            <nav class="hidden lg:flex items-center gap-8">
                <a href="{{ route('home') }}"          class="nav-link {{ request()->routeIs('home')      ? 'active' : '' }}">Accueil</a>
                <a href="{{ route('about') }}"         class="nav-link {{ request()->routeIs('about')     ? 'active' : '' }}">À propos</a>
                <a href="{{ route('events.index') }}"  class="nav-link {{ request()->routeIs('events.*')  ? 'active' : '' }}">Événements</a>
                <a href="{{ route('ebooks.index') }}"  class="nav-link {{ request()->routeIs('ebooks.*')  ? 'active' : '' }}">Ebooks</a>
                <a href="{{ route('contact') }}"       class="nav-link {{ request()->routeIs('contact')   ? 'active' : '' }}">Contact</a>
            </nav>

            <div class="flex items-center gap-3">
                <button @click="$store.modal.join = true" class="btn-rose text-sm px-5 py-2 hidden sm:inline-flex">
                    Rejoindre
                </button>
                <button @click="open = !open" class="lg:hidden flex flex-col gap-[5px] p-2 rounded-lg hover:bg-gray-50 transition-colors" aria-label="Menu">
                    <span class="block w-5 h-0.5 transition-all duration-300 origin-center" style="background:var(--dark);" :class="open ? 'rotate-45 translate-y-[7px]' : ''"></span>
                    <span class="block w-5 h-0.5 transition-all duration-200" style="background:var(--dark);" :class="open ? 'opacity-0 scale-x-0' : ''"></span>
                    <span class="block w-5 h-0.5 transition-all duration-300 origin-center" style="background:var(--dark);" :class="open ? '-rotate-45 -translate-y-[7px]' : ''"></span>
                </button>
            </div>
        </div>
    </header>

    {{-- Menu mobile — hors du flux du header, positionné juste en dessous --}}
    <div x-show="open"
         x-transition:enter="transition duration-200 ease-out"
         x-transition:enter-start="-translate-y-2 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition duration-150 ease-in"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="lg:hidden bg-white" style="border-bottom:1px solid var(--border);box-shadow:0 4px 12px rgba(0,0,0,0.08);display:none;">
        <div class="px-5 py-4 space-y-1">
            <a href="{{ route('home') }}"         @click="open=false" class="flex px-4 py-3 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors" style="color:var(--charcoal);">Accueil</a>
            <a href="{{ route('about') }}"        @click="open=false" class="flex px-4 py-3 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors" style="color:var(--charcoal);">À propos</a>
            <a href="{{ route('events.index') }}" @click="open=false" class="flex px-4 py-3 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors" style="color:var(--charcoal);">Événements</a>
            <a href="{{ route('ebooks.index') }}" @click="open=false" class="flex px-4 py-3 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors" style="color:var(--charcoal);">Ebooks</a>
            <a href="{{ route('contact') }}"      @click="open=false" class="flex px-4 py-3 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors" style="color:var(--charcoal);">Contact</a>
            <div class="pt-3 pb-1">
                <button @click="open=false; $store.modal.join = true" class="btn-rose w-full py-3 text-sm">
                    Rejoindre la communauté
                </button>
            </div>
        </div>
    </div>

</div>

{{-- ══════════════════ CONTENT ══════════════════ --}}
<main class="pt-16">
    @if(session('success') && !in_array(session('modal'), ['join','contact']))
    <div class="max-w-3xl mx-auto px-5 pt-5">
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm" style="background:#D1FAE5;color:#065F46;">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    </div>
    @endif
    @yield('content')
</main>

{{-- ══════════════════ FOOTER ══════════════════ --}}
<footer style="background:var(--dark);">
    <div class="max-w-7xl mx-auto px-5 lg:px-8 pt-16 pb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 mb-14">

            <div class="sm:col-span-2 lg:col-span-1">
                <a href="{{ route('home') }}" class="inline-flex rounded-2xl p-2 mb-4 hover:opacity-80 transition-opacity" style="background:#FDF0F5;">
                    <img src="{{ asset('logo_FSL.png') }}" alt="FSL" class="h-14 w-auto">
                </a>
                <p class="text-sm leading-relaxed mb-6" style="color:rgba(255,255,255,0.45);">Plateforme de transformation féminine dédiée à révéler la puissance intérieure de chaque femme.</p>
                <div class="flex items-center gap-3">
                    {{-- Facebook --}}
                    <a href="#" target="_blank" rel="noopener" class="w-9 h-9 rounded-full flex items-center justify-center transition-all duration-200" style="background:rgba(255,255,255,0.08);" onmouseover="this.style.background='var(--rose)'" onmouseout="this.style.background='rgba(255,255,255,0.08)'" aria-label="Facebook">
                        <svg class="w-4 h-4" fill="white" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                    </a>
                    {{-- Instagram --}}
                    <a href="#" target="_blank" rel="noopener" class="w-9 h-9 rounded-full flex items-center justify-center transition-all duration-200" style="background:rgba(255,255,255,0.08);" onmouseover="this.style.background='var(--rose)'" onmouseout="this.style.background='rgba(255,255,255,0.08)'" aria-label="Instagram">
                        <svg class="w-4 h-4" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="white" stroke="none"/></svg>
                    </a>
                    {{-- TikTok --}}
                    <a href="#" target="_blank" rel="noopener" class="w-9 h-9 rounded-full flex items-center justify-center transition-all duration-200" style="background:rgba(255,255,255,0.08);" onmouseover="this.style.background='var(--rose)'" onmouseout="this.style.background='rgba(255,255,255,0.08)'" aria-label="TikTok">
                        <svg class="w-4 h-4" fill="white" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.27 6.27 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.78 1.52V6.77a4.85 4.85 0 01-1.01-.08z"/></svg>
                    </a>
                    {{-- WhatsApp --}}
                    <a href="https://wa.me/2250000000000" target="_blank" rel="noopener" class="w-9 h-9 rounded-full flex items-center justify-center transition-all duration-200" style="background:rgba(255,255,255,0.08);" onmouseover="this.style.background='#25D366'" onmouseout="this.style.background='rgba(255,255,255,0.08)'" aria-label="WhatsApp">
                        <svg class="w-4 h-4" fill="white" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </a>
                </div>
            </div>

            <div>
                <p class="text-xs font-bold uppercase tracking-widest mb-5" style="color:rgba(255,255,255,0.3);">Navigation</p>
                <ul class="space-y-3">
                    @foreach([['Accueil','home'],['À propos','about'],['Événements','events.index'],['Contact','contact']] as [$l,$r])
                    <li><a href="{{ route($r) }}" class="text-sm transition-colors" style="color:rgba(255,255,255,0.55);" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.55)'">{{ $l }}</a></li>
                    @endforeach
                    <li><button @click="$store.modal.join = true" class="text-sm transition-colors text-left" style="color:rgba(255,255,255,0.55);" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.55)'">Rejoindre →</button></li>
                </ul>
            </div>

            <div>
                <p class="text-xs font-bold uppercase tracking-widest mb-5" style="color:rgba(255,255,255,0.3);">Contact</p>
                <ul class="space-y-3">
                    <li><a href="mailto:contact@femmessanslimites.com" class="text-sm transition-colors" style="color:rgba(255,255,255,0.55);" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.55)'">contact@femmessanslimites.com</a></li>
                    <li><a href="https://wa.me/2250000000000" target="_blank" class="text-sm transition-colors" style="color:rgba(255,255,255,0.55);" onmouseover="this.style.color='#25D366'" onmouseout="this.style.color='rgba(255,255,255,0.55)'">WhatsApp</a></li>
                    <li><button @click="$store.modal.contact = true" class="text-sm transition-colors text-left" style="color:rgba(255,255,255,0.55);" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.55)'">Nous écrire</button></li>
                </ul>
            </div>

            <div x-data="{ subscribed: {{ session('newsletter_success') ? 'true' : 'false' }} }">
                <p class="text-xs font-bold uppercase tracking-widest mb-5" style="color:rgba(255,255,255,0.3);">Newsletter</p>
                <p class="text-sm mb-4" style="color:rgba(255,255,255,0.45);">Reçois nos actualités et invitations en avant-première.</p>
                <div x-show="subscribed" class="flex items-center gap-2 text-sm py-2.5 px-3 rounded-xl" style="background:rgba(255,255,255,0.08);color:rgba(255,255,255,0.75);">
                    <svg class="w-4 h-4 flex-shrink-0" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Inscription confirmée !
                </div>
                <form x-show="!subscribed" method="POST" action="{{ route('newsletter.subscribe') }}" class="flex flex-col gap-2">
                    @csrf
                    <input type="email" name="email" placeholder="ton@email.com" required
                           class="w-full rounded-xl px-4 py-2.5 text-sm outline-none"
                           style="background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.1);color:white;"
                           onfocus="this.style.borderColor='var(--rose)'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'">
                    <button type="submit" class="btn-rose py-2.5 text-sm w-full">S'abonner</button>
                </form>
            </div>
        </div>

        <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-3" style="border-top:1px solid rgba(255,255,255,0.07);">
            <p class="text-xs" style="color:rgba(255,255,255,0.25);">© {{ date('Y') }} Femmes Sans Limites. Tous droits réservés.</p>
            <div class="flex items-center gap-5">
                <a href="{{ route('legal.cgu') }}" class="text-xs transition-colors" style="color:rgba(255,255,255,0.25);" onmouseover="this.style.color='rgba(255,255,255,0.6)'" onmouseout="this.style.color='rgba(255,255,255,0.25)'">CGU</a>
                <a href="{{ route('legal.mentions') }}" class="text-xs transition-colors" style="color:rgba(255,255,255,0.25);" onmouseover="this.style.color='rgba(255,255,255,0.6)'" onmouseout="this.style.color='rgba(255,255,255,0.25)'">Mentions légales</a>
            </div>
        </div>
    </div>
</footer>

{{-- ══════════════════ MODAL REJOINDRE ══════════════════ --}}
<div x-show="$store.modal.join"
     x-transition:enter="transition duration-200 ease-out"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition duration-150 ease-in"
     x-transition:leave-end="opacity-0"
     @click.self="$store.modal.join = false"
     @keydown.escape.window="$store.modal.join = false"
     class="modal-overlay" style="display:none;">
    <div class="modal-box"
         x-transition:enter="transition duration-200 ease-out"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
        <div class="flex items-center justify-between px-6 py-4 sticky top-0 bg-white z-10" style="border-bottom:1px solid var(--border);">
            <div>
                <span class="section-label">Candidature</span>
                <h2 class="text-lg font-bold" style="color:var(--dark);font-family:'Playfair Display',serif;">Rejoindre la communauté</h2>
            </div>
            <button @click="$store.modal.join = false" class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors" style="color:var(--gray);">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="px-6 py-5">
            @if($errors->any() && session('modal') === 'join')
            <div class="mb-4 p-3 rounded-xl text-sm" style="background:#FEF2F2;border:1px solid #FECACA;color:#dc2626;">
                <ul class="space-y-0.5 list-none">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
            </div>
            @endif
            <form method="POST" action="{{ route('membership.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Nom complet <span style="color:var(--rose)">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-input" placeholder="Marie Koné" required>
                    </div>
                    <div>
                        <label class="form-label">Email <span style="color:var(--rose)">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-input" placeholder="marie@email.com" required>
                    </div>
                    <div>
                        <label class="form-label">Téléphone <span class="font-normal" style="color:var(--gray)">(optionnel)</span></label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" class="form-input" placeholder="+225 07 00 00 00 00">
                    </div>
                    <div>
                        <label class="form-label">Profession <span style="color:var(--rose)">*</span></label>
                        <select name="profession" class="form-input" required>
                            <option value="" disabled {{ old('profession') ? '' : 'selected' }}>Sélectionne ta profession</option>
                            @foreach([
                                'Entrepreneur / Cheffe d\'entreprise',
                                'Dirigeante / PDG',
                                'Manager / Cadre supérieure',
                                'Ingénieure',
                                'Médecin / Chirurgienne',
                                'Pharmacienne',
                                'Infirmière / Sage-femme',
                                'Avocate / Juriste',
                                'Magistrate / Notaire',
                                'Comptable / Auditrice',
                                'Banquière / Finance',
                                'Consultante / Conseil',
                                'Architecte',
                                'Enseignante / Formatrice',
                                'Chercheuse / Scientifique',
                                'Journaliste / Communicante',
                                'Marketing / Relations publiques',
                                'Développeuse / Tech',
                                'Designer / Créatrice',
                                'Styliste / Mode & Beauté',
                                'Logisticienne / Supply chain',
                                'RH / Recrutement',
                                'Coach / Mentore',
                                'ONG / Humanitaire',
                                'Fonctionnaire / Administration',
                                'Artiste / Culturelle',
                                'Étudiante',
                                'Autre',
                            ] as $p)
                            <option value="{{ $p }}" {{ old('profession') === $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Pays <span style="color:var(--rose)">*</span></label>
                        <input type="text" name="country" value="{{ old('country') }}" class="form-input" placeholder="Côte d'Ivoire" required>
                    </div>
                    <div>
                        <label class="form-label">Ville <span style="color:var(--rose)">*</span></label>
                        <input type="text" name="city" value="{{ old('city') }}" class="form-input" placeholder="Abidjan" required>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="form-label">Photo <span class="font-normal" style="color:var(--gray)">(optionnel, max 3 Mo)</span></label>
                        <input type="file" name="photo" accept="image/*" class="form-input py-2 cursor-pointer text-sm">
                    </div>
                </div>
                <div>
                    <label class="form-label">Pourquoi souhaites-tu intégrer le mouvement FSL ? <span style="color:var(--rose)">*</span></label>
                    <textarea name="motivation" rows="4" class="form-input resize-none" placeholder="Partage ta motivation, tes objectifs, ce que tu espères apporter et recevoir au sein de la communauté…" required minlength="20" maxlength="1000">{{ old('motivation') }}</textarea>
                    <p class="text-xs mt-1" style="color:var(--gray);">Minimum 20 caractères — cette réponse aide notre équipe à mieux te connaître.</p>
                </div>
                <p class="text-xs leading-relaxed" style="color:var(--gray);">Ton adhésion démarre au niveau <strong>Standard</strong>. Notre équipe te contactera sous 48h ouvrées pour valider ta candidature.</p>
                <button type="submit" class="btn-rose w-full py-3.5">
                    Soumettre ma candidature
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════ MODAL CONTACT ══════════════════ --}}
<div x-show="$store.modal.contact"
     x-transition:enter="transition duration-200 ease-out"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition duration-150 ease-in"
     x-transition:leave-end="opacity-0"
     @click.self="$store.modal.contact = false"
     @keydown.escape.window="$store.modal.contact = false"
     class="modal-overlay" style="display:none;">
    <div class="modal-box"
         x-transition:enter="transition duration-200 ease-out"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
        <div class="flex items-center justify-between px-6 py-4 sticky top-0 bg-white z-10" style="border-bottom:1px solid var(--border);">
            <div>
                <span class="section-label">Message</span>
                <h2 class="text-lg font-bold" style="color:var(--dark);font-family:'Playfair Display',serif;">Nous écrire</h2>
            </div>
            <button @click="$store.modal.contact = false" class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors" style="color:var(--gray);">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="px-6 py-5">
            @if(session('success') && session('modal') === 'contact')
            <div class="flex items-center gap-2 mb-4 p-3 rounded-xl text-sm" style="background:#D1FAE5;color:#065F46;"><svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> {{ session('success') }}</div>
            @endif
            @if($errors->any() && session('modal') === 'contact')
            <div class="mb-4 p-3 rounded-xl text-sm" style="background:#FEF2F2;border:1px solid #FECACA;color:#dc2626;"><ul class="space-y-0.5">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul></div>
            @endif
            <form method="POST" action="{{ route('contact.send') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><label class="form-label">Prénom <span style="color:var(--rose)">*</span></label><input type="text" name="name" value="{{ old('name') }}" class="form-input" placeholder="Marie" required></div>
                    <div><label class="form-label">Email <span style="color:var(--rose)">*</span></label><input type="email" name="email" value="{{ old('email') }}" class="form-input" placeholder="marie@email.com" required></div>
                </div>
                <div><label class="form-label">Sujet <span style="color:var(--rose)">*</span></label><input type="text" name="subject" value="{{ old('subject') }}" class="form-input" placeholder="Votre sujet" required></div>
                <div><label class="form-label">Message <span style="color:var(--rose)">*</span></label><textarea name="message" rows="4" class="form-input resize-none" placeholder="Votre message..." required>{{ old('message') }}</textarea></div>
                <button type="submit" class="btn-rose w-full py-3.5">
                    Envoyer le message
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>

@stack('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ── Scroll reveal (fade-up, fade-left, fade-right, scale-up, img-reveal) ── */
    const revealObs = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (!e.isIntersecting) return;
            const delay = parseInt(e.target.dataset.delay || 0);
            setTimeout(() => e.target.classList.add('visible'), delay);
            revealObs.unobserve(e.target);
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -44px 0px' });
    document.querySelectorAll('.fade-up,.fade-left,.fade-right,.scale-up,.img-reveal').forEach(el => revealObs.observe(el));

    /* ── Auto-stagger : ajoute data-delay aux enfants d'un conteneur [data-stagger] ── */
    document.querySelectorAll('[data-stagger]').forEach(parent => {
        const step = parseInt(parent.dataset.stagger) || 130;
        [...parent.children].forEach((child, i) => {
            const animated = child.classList.contains('fade-up') || child.classList.contains('scale-up') || child.classList.contains('fade-left') || child.classList.contains('fade-right');
            if (animated) child.dataset.delay = i * step;
        });
    });

    /* ── Counter animation ── */
    const counterObs = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (!e.isIntersecting) return;
            const el = e.target;
            const target = parseFloat(el.dataset.target);
            const suffix = el.dataset.suffix || '';
            const duration = 1800;
            const t0 = Date.now();
            const tick = () => {
                const p = Math.min((Date.now() - t0) / duration, 1);
                const eased = 1 - Math.pow(1 - p, 3);
                el.textContent = (Number.isInteger(target) ? Math.round(eased * target) : (eased * target).toFixed(1)) + suffix;
                if (p < 1) requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
            counterObs.unobserve(el);
        });
    }, { threshold: 0.6 });
    document.querySelectorAll('[data-target]').forEach(el => counterObs.observe(el));

    /* ── Parallax hero ── */
    const parallaxEls = document.querySelectorAll('[data-parallax]');
    if (parallaxEls.length) {
        const onScroll = () => parallaxEls.forEach(el => {
            el.style.transform = `translateY(${window.scrollY * parseFloat(el.dataset.parallax || 0.12)}px)`;
        });
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    /* ── Magnetic buttons ── */
    document.querySelectorAll('.btn-rose,.btn-outline,.btn-dark').forEach(btn => {
        btn.addEventListener('mousemove', e => {
            const r = btn.getBoundingClientRect();
            const x = (e.clientX - r.left - r.width / 2) * 0.15;
            const y = (e.clientY - r.top  - r.height / 2) * 0.2;
            btn.style.transform = `translateY(-1px) translate(${x}px,${y}px)`;
        });
        btn.addEventListener('mouseleave', () => btn.style.transform = '');
    });

    /* ── Nav shrink on scroll ── */
    const header = document.querySelector('header');
    if (header) {
        window.addEventListener('scroll', () => {
            header.style.height = window.scrollY > 40 ? '64px' : '';
        }, { passive: true });
    }

    /* ── Page transition bar + form submit spinner ── */
    const bar = document.getElementById('fsl-bar');
    const spinSVG = '<svg class="w-4 h-4 animate-spin inline-block mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 12a8 8 0 018-8"/></svg>';

    document.addEventListener('click', e => {
        const a = e.target.closest('a[href]');
        if (!a) return;
        const href = a.getAttribute('href');
        if (!href || /^(#|mailto:|tel:|javascript:)/.test(href) || a.target === '_blank' || a.hasAttribute('download')) return;
        bar.classList.add('active');
    });

    document.addEventListener('submit', e => {
        const btn = e.target.querySelector('button[type=submit], input[type=submit]');
        if (!btn || btn.disabled) return;
        btn.disabled = true;
        btn.innerHTML = spinSVG + ' Un instant…';
        bar.classList.add('active');
    }, true);

    window.addEventListener('pageshow', e => {
        if (e.persisted) bar.classList.remove('active');
    });

});
</script>
</body>
</html>
