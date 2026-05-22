@extends('layouts.public')
@section('title', 'Conditions Générales d\'Utilisation — Femme Sans Limites')

@section('content')

<section class="pt-28 pb-16 bg-white relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none" style="background:radial-gradient(ellipse at 30% 50%,var(--gold-pale) 0%,transparent 55%);"></div>
    <div class="max-w-4xl mx-auto px-5 lg:px-8 relative">
        <span class="section-label fade-up">Cadre contractuel</span>
        <h1 class="text-5xl font-bold mt-3 mb-5 fade-up delay-100" style="color:var(--dark);font-family:'Playfair Display',serif;">Conditions Générales d'Utilisation</h1>
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
                        ['art1','Article 1 — Objet'],
                        ['art2','Article 2 — Accès au site'],
                        ['art3','Article 3 — Adhésion à la communauté FSL'],
                        ['art4','Article 4 — Inscription aux événements'],
                        ['art5','Article 5 — Ebooks et ressources'],
                        ['art6','Article 6 — Newsletter'],
                        ['art7','Article 7 — Comportement des utilisatrices'],
                        ['art8','Article 8 — Droit applicable et juridiction'],
                        ['art9','Article 9 — Contact'],
                    ] as [$id,$label])
                    <li class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:var(--rose);opacity:.5;"></span>
                        <a href="#{{ $id }}" class="text-sm hover:underline" style="color:var(--dark);">{{ $label }}</a>
                    </li>
                    @endforeach
                </ol>
            </div>

            <div id="art1">
                <h2 class="text-xl font-bold mb-4" style="color:var(--dark);font-family:'Playfair Display',serif;">Article 1 — Objet</h2>
                <div class="space-y-3 text-sm leading-relaxed" style="color:var(--gray);">
                    <p>Les présentes Conditions Générales d'Utilisation (ci-après « CGU ») régissent l'accès et l'utilisation du site internet de l'association <strong style="color:var(--dark);">Femme Sans Limites</strong> (FSL), accessible à l'adresse <strong style="color:var(--dark);">femmessanslimites.com</strong>.</p>
                    <p>L'utilisation du site implique l'acceptation pleine et entière des présentes CGU. FSL se réserve le droit de les modifier à tout moment ; les modifications prennent effet dès leur publication sur le site.</p>
                </div>
            </div>

            <div id="art2" style="border-top:1px solid var(--border);padding-top:2rem;">
                <h2 class="text-xl font-bold mb-4" style="color:var(--dark);font-family:'Playfair Display',serif;">Article 2 — Accès au site</h2>
                <div class="space-y-3 text-sm leading-relaxed" style="color:var(--gray);">
                    <p>Le site est accessible gratuitement à tout visiteur disposant d'un accès internet. FSL ne saurait être tenu responsable des perturbations liées au réseau internet ou aux équipements de l'utilisateur.</p>
                    <p>FSL se réserve le droit de suspendre l'accès au site pour des raisons de maintenance ou de mise à jour, sans préavis.</p>
                </div>
            </div>

            <div id="art3" style="border-top:1px solid var(--border);padding-top:2rem;">
                <h2 class="text-xl font-bold mb-4" style="color:var(--dark);font-family:'Playfair Display',serif;">Article 3 — Adhésion à la communauté FSL</h2>
                <div class="space-y-3 text-sm leading-relaxed" style="color:var(--gray);">
                    <p>L'adhésion à la communauté FSL est ouverte à toute femme souhaitant rejoindre le réseau. La candidature est soumise à validation par l'équipe FSL sous 48 heures ouvrées.</p>
                    <p>En soumettant une candidature, l'utilisatrice certifie que les informations fournies sont exactes et accepte d'être contactée par l'équipe FSL dans le cadre de sa candidature.</p>
                    <p>FSL se réserve le droit de refuser ou de révoquer une adhésion sans avoir à en justifier les motifs.</p>
                </div>
            </div>

            <div id="art4" style="border-top:1px solid var(--border);padding-top:2rem;">
                <h2 class="text-xl font-bold mb-4" style="color:var(--dark);font-family:'Playfair Display',serif;">Article 4 — Inscription aux événements</h2>
                <div class="space-y-3 text-sm leading-relaxed" style="color:var(--gray);">
                    <p>Les inscriptions aux événements FSL sont soumises à disponibilité des places. Toute inscription payante donne lieu à l'envoi d'un lien de paiement par email.</p>
                    <p>L'inscription est confirmée uniquement après réception du paiement complet. En cas de capacité atteinte, l'utilisatrice peut rejoindre la liste d'attente.</p>
                    <p>FSL se réserve le droit d'annuler ou de reporter un événement. Dans ce cas, les inscrites seront notifiées et, pour les événements payants, remboursées.</p>
                </div>
            </div>

            <div id="art5" style="border-top:1px solid var(--border);padding-top:2rem;">
                <h2 class="text-xl font-bold mb-4" style="color:var(--dark);font-family:'Playfair Display',serif;">Article 5 — Ebooks et ressources</h2>
                <div class="space-y-3 text-sm leading-relaxed" style="color:var(--gray);">
                    <p>Les ebooks présentés sur le site FSL sont disponibles via la plateforme partenaire <strong style="color:var(--dark);">Charriow</strong>. L'achat et le téléchargement s'effectuent directement sur Charriow, soumis aux conditions de cette plateforme.</p>
                    <p>Le contenu des ebooks est protégé par le droit d'auteur. Toute reproduction, diffusion ou revente sans autorisation est interdite.</p>
                </div>
            </div>

            <div id="art6" style="border-top:1px solid var(--border);padding-top:2rem;">
                <h2 class="text-xl font-bold mb-4" style="color:var(--dark);font-family:'Playfair Display',serif;">Article 6 — Newsletter</h2>
                <div class="space-y-3 text-sm leading-relaxed" style="color:var(--gray);">
                    <p>En s'abonnant à la newsletter FSL, l'utilisatrice consent à recevoir des communications par email concernant les événements et ressources publiés par FSL.</p>
                    <p>Elle peut se désabonner à tout moment via le lien présent dans chaque email de newsletter, ou en contactant l'équipe FSL.</p>
                </div>
            </div>

            <div id="art7" style="border-top:1px solid var(--border);padding-top:2rem;">
                <h2 class="text-xl font-bold mb-4" style="color:var(--dark);font-family:'Playfair Display',serif;">Article 7 — Comportement des utilisatrices</h2>
                <div class="space-y-3 text-sm leading-relaxed" style="color:var(--gray);">
                    <p>Les utilisatrices s'engagent à utiliser le site de manière conforme à la loi et aux bonnes mœurs, et à ne pas tenter de porter atteinte à la sécurité ou au bon fonctionnement du site.</p>
                    <p>Tout comportement abusif, harcelant ou discriminatoire au sein de la communauté FSL entraînera l'exclusion immédiate et définitive.</p>
                </div>
            </div>

            <div id="art8" style="border-top:1px solid var(--border);padding-top:2rem;">
                <h2 class="text-xl font-bold mb-4" style="color:var(--dark);font-family:'Playfair Display',serif;">Article 8 — Droit applicable et juridiction</h2>
                <p class="text-sm leading-relaxed" style="color:var(--gray);">Les présentes CGU sont soumises au droit applicable en Côte d'Ivoire. Tout litige relatif à leur interprétation ou exécution sera soumis aux tribunaux compétents d'Abidjan.</p>
            </div>

            <div id="art9" style="border-top:1px solid var(--border);padding-top:2rem;">
                <h2 class="text-xl font-bold mb-4" style="color:var(--dark);font-family:'Playfair Display',serif;">Article 9 — Contact</h2>
                <p class="text-sm leading-relaxed" style="color:var(--gray);">Pour toute question relative aux présentes CGU : <strong style="color:var(--dark);">contact@femmessanslimites.com</strong></p>
            </div>

        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('legal.mentions') }}" class="text-sm font-medium hover:opacity-80 transition-opacity" style="color:var(--rose);">
                ← Consulter les Mentions légales
            </a>
        </div>
    </div>
</section>

@endsection
