<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — FSL Back Office</title>
    @vite(['resources/css/app.css'])
</head>
<body class="antialiased" style="background:var(--dark);">

<div class="min-h-screen flex">

    {{-- ── Panneau gauche : branding ── --}}
    <div class="hidden lg:flex lg:w-[52%] relative overflow-hidden flex-col justify-between p-14" style="background:var(--dark);">

        {{-- Photo de fond --}}
        <div class="absolute inset-0">
            <img src="{{ asset('images/photo_01_1.png') }}" alt="" class="w-full h-full object-cover object-top" style="opacity:0.22;">
            <div class="absolute inset-0" style="background:linear-gradient(145deg,rgba(15,10,12,0.95) 0%,rgba(217,30,110,0.18) 100%);"></div>
        </div>

        {{-- Éléments déco --}}
        <div class="absolute top-0 right-0 w-80 h-80 rounded-full pointer-events-none" style="background:radial-gradient(circle,rgba(217,30,110,0.12),transparent 70%);"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 rounded-full pointer-events-none" style="background:radial-gradient(circle,rgba(201,168,76,0.08),transparent 70%);"></div>

        {{-- Logo --}}
        <div class="relative z-10">
            <a href="{{ url('/') }}" class="inline-flex rounded-2xl p-2 hover:opacity-90 transition-opacity" style="background:rgba(253,240,245,0.92);">
                <img src="{{ asset('logo_FSL.png') }}" alt="FSL" class="h-14 w-auto" style="filter:drop-shadow(0 1px 4px rgba(217,30,110,0.18));">
            </a>
        </div>

        {{-- Citation centrale --}}
        <div class="relative z-10 max-w-md">
            <div class="w-8 h-0.5 mb-6" style="background:var(--rose);"></div>
            <blockquote class="text-4xl font-bold leading-tight mb-8 text-white" style="font-family:'Playfair Display',serif;">
                « Chaque femme porte<br>en elle une puissance<br><em style="color:var(--rose);">sans limites.</em> »
            </blockquote>
            <div class="flex items-center gap-4">
                <div class="flex -space-x-2">
                    <img src="{{ asset('images/photo_01_2.png') }}" class="w-9 h-9 rounded-full object-cover object-top border-2 border-white/20" alt="">
                    <img src="{{ asset('images/photo_03_4.png') }}" class="w-9 h-9 rounded-full object-cover object-top border-2 border-white/20" alt="">
                    <img src="{{ asset('images/photo_03_1.png') }}" class="w-9 h-9 rounded-full object-cover object-top border-2 border-white/20" alt="">
                </div>
                <div>
                    <p class="text-sm font-semibold text-white">500+ femmes accompagnées</p>
                    <p class="text-xs" style="color:rgba(255,255,255,0.45);">Afrique & diaspora · 15+ pays</p>
                </div>
            </div>
        </div>

        {{-- Métriques bas --}}
        <div class="relative z-10 grid grid-cols-3 gap-6 pt-8" style="border-top:1px solid rgba(255,255,255,0.07);">
            @foreach([['500+','Membres actives'],['15+','Pays'],['50+','Événements']] as [$n,$l])
            <div>
                <p class="text-2xl font-bold text-white" style="font-family:'Playfair Display',serif;">{{ $n }}</p>
                <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.4);">{{ $l }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── Panneau droit : formulaire ── --}}
    <div class="flex-1 flex items-center justify-center px-6 py-12" style="background:#FAFAFA;">
        <div class="w-full max-w-[420px]">

            {{-- Logo mobile --}}
            <div class="lg:hidden flex justify-center mb-10">
                <a href="{{ url('/') }}" class="inline-flex rounded-2xl p-2" style="background:rgba(253,240,245,0.95);">
                    <img src="{{ asset('logo_FSL.png') }}" alt="FSL" class="h-14 w-auto">
                </a>
            </div>

            {{-- En-tête --}}
            <div class="mb-8">
                <span class="text-xs font-bold uppercase tracking-[0.14em]" style="color:var(--rose);">Back Office</span>
                <h1 class="text-3xl font-bold mt-2 mb-2 leading-tight" style="color:var(--dark);font-family:'Playfair Display',serif;">
                    Connexion administrateur
                </h1>
                <p class="text-sm" style="color:var(--gray);">Accède à l'espace de gestion FSL</p>
            </div>

            {{-- Status session --}}
            @if(session('status'))
            <div class="mb-5 px-4 py-3 rounded-xl text-sm" style="background:#D1FAE5;color:#065F46;border:1px solid #A7F3D0;">
                {{ session('status') }}
            </div>
            @endif

            {{-- Erreurs globales --}}
            @if($errors->any())
            <div class="mb-5 px-4 py-3 rounded-xl text-sm" style="background:#FEF2F2;border:1px solid #FECACA;color:#dc2626;">
                @foreach($errors->all() as $error)
                <p>• {{ $error }}</p>
                @endforeach
            </div>
            @endif

            {{-- Formulaire --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="form-label">Adresse email</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none" style="color:var(--gray-light);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}"
                               required autofocus autocomplete="username"
                               class="form-input pl-11"
                               placeholder="admin@femmessanslimites.com">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="form-label mb-0">Mot de passe</label>
                        @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-xs font-medium" style="color:var(--rose);">Oublié ?</a>
                        @endif
                    </div>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none" style="color:var(--gray-light);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </span>
                        <input id="password" type="password" name="password"
                               required autocomplete="current-password"
                               class="form-input pl-11"
                               placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input id="remember_me" type="checkbox" name="remember"
                           class="rounded w-4 h-4 border-gray-300 cursor-pointer"
                           style="accent-color:var(--rose);">
                    <label for="remember_me" class="text-sm cursor-pointer select-none" style="color:var(--gray);">Se souvenir de moi</label>
                </div>

                <button type="submit" class="btn-rose w-full py-4 text-base mt-1" style="gap:0.75rem;">
                    Se connecter
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </button>
            </form>

            {{-- Retour site --}}
            <div class="mt-8 pt-6 text-center" style="border-top:1px solid var(--border);">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-xs font-medium transition-colors" style="color:var(--gray);" onmouseover="this.style.color='var(--dark)'" onmouseout="this.style.color='var(--gray)'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Retour au site public
                </a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
