{{-- Écran d'authentification de l'espace membre : volontairement sobre et isolé. --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Espace membre') — Femme Sans Limites</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background:var(--warm);">

<div class="min-h-screen flex items-center justify-center px-5 py-12">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <a href="{{ route('home') }}">
                <img src="{{ asset('favicon.png') }}" alt="Femme Sans Limites" class="w-14 h-14 rounded-2xl mx-auto mb-4 shadow-sm">
            </a>
            <h1 class="text-2xl font-bold" style="color:var(--dark);font-family:'Playfair Display',serif;">@yield('heading')</h1>
            <p class="text-sm mt-2" style="color:var(--gray);">@yield('subheading')</p>
        </div>

        <div class="card p-7">
            @if(session('status'))
            <div class="mb-5 p-3 rounded-xl text-sm" style="background:#D1FAE5;color:#065F46;">{{ session('status') }}</div>
            @endif

            @if($errors->any())
            <div class="mb-5 p-3 rounded-xl text-sm" style="background:#FEF2F2;border:1px solid #FECACA;color:#dc2626;">
                <ul class="space-y-0.5 list-none">@foreach($errors->all() as $error)<li>• {{ $error }}</li>@endforeach</ul>
            </div>
            @endif

            @yield('form')
        </div>

        <p class="text-center text-xs mt-6" style="color:var(--gray);">
            <a href="{{ route('home') }}" class="hover:underline">← Retour au site</a>
        </p>
    </div>
</div>

</body>
</html>
