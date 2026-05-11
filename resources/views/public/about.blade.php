@extends('layouts.public')
@section('title', 'À propos — Femmes Sans Limites')

@section('content')

{{-- Hero --}}
<section class="pt-40 pb-24 px-6 text-center" style="background:linear-gradient(135deg,#FDF0F5,#FDFAF9);">
    <div class="max-w-4xl mx-auto">
        <div class="gold-divider mb-8"></div>
        <h1 class="section-title mb-6">Notre Histoire & Vision</h1>
        <p class="section-subtitle">Découvrez qui nous sommes, ce qui nous anime et pourquoi nous croyons profondément au potentiel de chaque femme.</p>
    </div>
</section>

{{-- Vision --}}
<section class="py-24 px-6">
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
        <div class="fade-up">
            <span class="text-xs font-bold uppercase tracking-widest" style="color:var(--rose);">Notre Raison d'Être</span>
            <h2 class="text-4xl font-bold mt-3 mb-6" style="color:var(--dark);">Une plateforme née d'une conviction</h2>
            <p class="text-lg leading-relaxed mb-6" style="color:var(--gray);">
                Femmes Sans Limites est née d'une conviction profonde : chaque femme porte en elle une puissance infinie, trop souvent bridée par des barrières mentales, sociales et culturelles imposées par la société.
            </p>
            <p class="text-lg leading-relaxed mb-6" style="color:var(--gray);">
                Notre mission est de créer un espace sûr, stimulant et transformateur où les femmes peuvent se connecter à leur potentiel authentique, développer leurs compétences et prendre leur place avec confiance.
            </p>
            <p class="text-lg leading-relaxed" style="color:var(--gray);">
                Concrètement, cela passe par des <strong style="color:var(--dark);">événements</strong>, des <strong style="color:var(--dark);">panels</strong>, des formations en ligne et en présentiel, ainsi qu'une communauté active où les femmes se soutiennent et grandissent ensemble.
            </p>
        </div>

        <div class="fade-up flex justify-center">
            <div class="relative w-80">
                <div class="absolute -inset-4 rounded-3xl opacity-20" style="background:linear-gradient(135deg,var(--rose),var(--gold));"></div>
                <img src="{{ asset('logo_FSL.png') }}" alt="FSL" class="relative w-full drop-shadow-xl">
            </div>
        </div>
    </div>
</section>

{{-- Valeurs --}}
<section class="py-24 px-6" style="background:var(--rose-pale);">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16 fade-up">
            <h2 class="section-title">Nos Valeurs</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach([
                ['💪', 'Empowerment', 'Nous croyons que chaque femme a le droit et le devoir de révéler pleinement sa puissance intérieure.', 'var(--rose)'],
                ['🤝', 'Communauté', 'Ensemble on va plus loin. Nous créons des liens authentiques et durables entre femmes qui s\'élèvent mutuellement.', 'var(--gold)'],
                ['🌱', 'Croissance', 'Le développement est un voyage continu. Nous accompagnons chaque étape avec bienveillance et exigence.', 'var(--rose)'],
            ] as [$icon, $title, $desc, $color])
            <div class="card-pillar text-center fade-up">
                <div class="w-16 h-16 rounded-full flex items-center justify-center text-3xl mx-auto mb-6" style="background:rgba(217,30,110,0.06);">{{ $icon }}</div>
                <h3 class="text-xl font-bold mb-3" style="color:var(--dark);">{{ $title }}</h3>
                <p class="text-sm leading-relaxed" style="color:var(--gray);">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-24 px-6 text-center" style="background:var(--dark);">
    <div class="max-w-2xl mx-auto fade-up">
        <h2 class="text-4xl font-bold text-white mb-4">Rejoins le mouvement</h2>
        <p class="text-lg mb-10" style="color:rgba(255,255,255,0.6);">Prête à faire partie d'une communauté qui croit en ton potentiel ?</p>
        <a href="{{ route('events.index') }}" class="btn-rose text-base px-10 py-4">Voir nos événements</a>
    </div>
</section>

@endsection
