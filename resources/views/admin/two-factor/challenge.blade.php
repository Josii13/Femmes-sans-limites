{{-- Écran de défi : volontairement hors du gabarit du back-office, auquel la
     personne n'a pas encore accès. --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Vérification — Femme Sans Limites</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background:var(--warm);">

<div class="min-h-screen flex items-center justify-center px-5 py-12">
    <div class="w-full max-w-sm">

        <div class="text-center mb-8">
            <img src="{{ asset('favicon.png') }}" alt="" class="w-14 h-14 rounded-2xl mx-auto mb-4 shadow-sm">
            <h1 class="text-2xl font-bold" style="color:var(--dark);font-family:'Playfair Display',serif;">Vérification</h1>
            <p class="text-sm mt-2" style="color:var(--gray);">
                Saisissez le code affiché par votre application d’authentification.
            </p>
        </div>

        <div class="card p-7">
            @if($errors->any())
            <div class="mb-5 p-3 rounded-xl text-sm" style="background:#FEF2F2;border:1px solid #FECACA;color:#dc2626;">
                <ul class="space-y-0.5 list-none">@foreach($errors->all() as $error)<li>• {{ $error }}</li>@endforeach</ul>
            </div>
            @endif

            <form method="POST" action="{{ route('admin.two-factor.challenge.verify') }}" class="space-y-4">
                @csrf
                <input type="text" name="code" inputmode="text" autocomplete="one-time-code"
                       class="form-input text-center tracking-[0.3em] font-mono text-lg"
                       placeholder="000000" required autofocus>

                <button type="submit" class="btn-rose w-full py-3">Vérifier</button>
            </form>

            {{-- Le code de secours est le seul recours si le téléphone est perdu. --}}
            <p class="text-xs text-center mt-5 leading-relaxed" style="color:var(--gray);">
                Téléphone perdu ou inaccessible ? Saisissez l’un de vos codes de secours
                dans le même champ.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.two-factor.abandon') }}" class="text-center mt-6">
            @csrf
            <button type="submit" class="text-xs hover:underline" style="color:var(--gray);">
                Se déconnecter
            </button>
        </form>
    </div>
</div>

</body>
</html>
