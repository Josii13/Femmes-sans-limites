@extends('layouts.public')
@section('title', 'Mentions légales — Femmes Sans Limites')

@section('content')

<section class="pt-28 pb-16 bg-white relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none" style="background:radial-gradient(ellipse at 70% 30%,var(--rose-pale) 0%,transparent 55%);"></div>
    <div class="max-w-4xl mx-auto px-5 lg:px-8 relative">
        <span class="section-label fade-up">Informations légales</span>
        <h1 class="text-5xl font-bold mt-3 mb-5 fade-up delay-100" style="color:var(--dark);font-family:'Playfair Display',serif;">Mentions légales</h1>
        <p class="text-sm fade-up delay-200" style="color:var(--gray);">Dernière mise à jour : {{ date('d/m/Y') }}</p>
    </div>
</section>

<section class="py-16" style="background:var(--warm);">
    <div class="max-w-4xl mx-auto px-5 lg:px-8">
        <div class="bg-white rounded-3xl p-8 lg:p-12 shadow-sm space-y-10" style="border:1px solid var(--border);">

            {{-- Table des matières --}}
            <div class="rounded-2xl p-5" style="background:var(--rose-pale);border:1px solid #F9A8CF44;">
                <p class="text-xs font-bold uppercase tracking-widest mb-3" style="color:var(--rose);">Sommaire</p>
                <ol class="space-y-1.5">
                    @foreach([
                        ['ml1','1. Éditeur du site'],
                        ['ml2','2. Hébergement'],
                        ['ml3','3. Propriété intellectuelle'],
                        ['ml4','4. Données personnelles'],
                        ['ml5','5. Cookies'],
                        ['ml6','6. Limitation de responsabilité'],
                        ['ml7','7. Contact'],
                    ] as [$id,$label])
                    <li class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:var(--rose);opacity:.5;"></span>
                        <a href="#{{ $id }}" class="text-sm hover:underline" style="color:var(--dark);">{{ $label }}</a>
                    </li>
                    @endforeach
                </ol>
            </div>

            <div id="ml1">
                <h2 class="text-xl font-bold mb-4" style="color:var(--dark);font-family:'Playfair Display',serif;">1. Éditeur du site</h2>
                <div class="space-y-2 text-sm leading-relaxed" style="color:var(--gray);">
                    <p><strong style="color:var(--dark);">Dénomination :</strong> Femmes Sans Limites (FSL)</p>
                    <p><strong style="color:var(--dark);">Forme juridique :</strong> Association</p>
                    <p><strong style="color:var(--dark);">Adresse :</strong> Abidjan, Côte d'Ivoire</p>
                    <p><strong style="color:var(--dark);">Email :</strong> contact@femmessanslimites.com</p>
                    <p><strong style="color:var(--dark);">Directrice de la publication :</strong> Équipe FSL</p>
                </div>
            </div>

            <div id="ml2" style="border-top:1px solid var(--border);padding-top:2rem;">
                <h2 class="text-xl font-bold mb-4" style="color:var(--dark);font-family:'Playfair Display',serif;">2. Hébergement</h2>
                <div class="space-y-2 text-sm leading-relaxed" style="color:var(--gray);">
                    <p>Le site est hébergé par un prestataire d'hébergement web professionnel. Les données sont stockées sur des serveurs sécurisés.</p>
                </div>
            </div>

            <div id="ml3" style="border-top:1px solid var(--border);padding-top:2rem;">
                <h2 class="text-xl font-bold mb-4" style="color:var(--dark);font-family:'Playfair Display',serif;">3. Propriété intellectuelle</h2>
                <div class="space-y-3 text-sm leading-relaxed" style="color:var(--gray);">
                    <p>L'ensemble du contenu de ce site (textes, images, logos, ebooks, vidéos) est la propriété exclusive de Femmes Sans Limites ou de ses partenaires, et est protégé par les lois relatives à la propriété intellectuelle.</p>
                    <p>Toute reproduction, distribution, modification ou utilisation de ces contenus sans autorisation écrite préalable est strictement interdite.</p>
                </div>
            </div>

            <div id="ml4" style="border-top:1px solid var(--border);padding-top:2rem;">
                <h2 class="text-xl font-bold mb-4" style="color:var(--dark);font-family:'Playfair Display',serif;">4. Données personnelles</h2>
                <div class="space-y-3 text-sm leading-relaxed" style="color:var(--gray);">
                    <p>Femmes Sans Limites collecte et traite des données personnelles (nom, email, profession, ville, pays) dans le cadre des adhésions et inscriptions aux événements.</p>
                    <p>Ces données sont collectées avec le consentement des utilisateurs et ne sont jamais cédées à des tiers sans autorisation explicite.</p>
                    <p>Conformément à la réglementation en vigueur, vous disposez d'un droit d'accès, de rectification et de suppression de vos données en contactant : <strong style="color:var(--dark);">contact@femmessanslimites.com</strong></p>
                </div>
            </div>

            <div id="ml5" style="border-top:1px solid var(--border);padding-top:2rem;">
                <h2 class="text-xl font-bold mb-4" style="color:var(--dark);font-family:'Playfair Display',serif;">5. Cookies</h2>
                <div class="space-y-3 text-sm leading-relaxed" style="color:var(--gray);">
                    <p>Ce site utilise des cookies techniques nécessaires à son bon fonctionnement (session, sécurité CSRF). Aucun cookie publicitaire ou de tracking tiers n'est utilisé.</p>
                </div>
            </div>

            <div id="ml6" style="border-top:1px solid var(--border);padding-top:2rem;">
                <h2 class="text-xl font-bold mb-4" style="color:var(--dark);font-family:'Playfair Display',serif;">6. Limitation de responsabilité</h2>
                <div class="space-y-3 text-sm leading-relaxed" style="color:var(--gray);">
                    <p>Femmes Sans Limites s'efforce de maintenir les informations de ce site à jour et exactes. Cependant, elle ne saurait être tenue responsable des erreurs ou omissions, ni des dommages résultant de l'utilisation des informations contenues sur ce site.</p>
                    <p>Les liens vers des sites tiers (notamment Charriow pour les ebooks) sont fournis à titre informatif. FSL n'est pas responsable du contenu de ces sites externes.</p>
                </div>
            </div>

            <div id="ml7" style="border-top:1px solid var(--border);padding-top:2rem;">
                <h2 class="text-xl font-bold mb-4" style="color:var(--dark);font-family:'Playfair Display',serif;">7. Contact</h2>
                <p class="text-sm leading-relaxed" style="color:var(--gray);">Pour toute question relative aux présentes mentions légales, vous pouvez nous contacter à l'adresse : <strong style="color:var(--dark);">contact@femmessanslimites.com</strong></p>
            </div>

        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('legal.cgu') }}" class="text-sm font-medium hover:opacity-80 transition-opacity" style="color:var(--rose);">
                Consulter nos Conditions Générales d'Utilisation →
            </a>
        </div>
    </div>
</section>

@endsection
