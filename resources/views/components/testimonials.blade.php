{{--
    Section « La parole aux membres ».

    Alimentée par le back-office. La section disparaît entièrement s'il n'y a
    aucun témoignage publié : mieux vaut ne rien dire que de remplir l'espace
    avec des propos inventés, ce qu'elle faisait auparavant avec trois
    témoignages écrits en dur dans la vue.
--}}
@php
    $testimonials = \App\Models\Testimonial::published()->with('member')->limit(6)->get();
@endphp

@if($testimonials->isNotEmpty())
<section class="py-20 lg:py-28" style="background:var(--dark);">
    <div class="max-w-7xl mx-auto px-5 lg:px-8">
        <div class="text-center mb-14 fade-up">
            <span class="text-xs font-bold uppercase tracking-widest" style="color:var(--gold);">Ce qu’elles disent</span>
            <h2 class="text-3xl lg:text-4xl font-bold mt-3 text-white" style="font-family:'Playfair Display',serif;">La parole aux membres</h2>
        </div>

        {{-- La grille s'adapte au nombre réel de témoignages plutôt que d'imposer trois colonnes. --}}
        <div class="grid grid-cols-1 {{ $testimonials->count() > 1 ? 'md:grid-cols-2' : '' }} {{ $testimonials->count() > 2 ? 'lg:grid-cols-3' : '' }} gap-6 {{ $testimonials->count() < 3 ? 'max-w-4xl mx-auto' : '' }}" data-stagger="120">
            @foreach($testimonials as $testimonial)
            <div class="rounded-2xl p-7 flex flex-col fade-up" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);">
                <svg class="w-8 h-8 mb-5 flex-shrink-0" style="color:var(--rose);" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>

                <p class="text-sm leading-relaxed flex-1 mb-6" style="color:rgba(255,255,255,0.7);">{{ $testimonial->quote }}</p>

                <div class="flex items-center gap-3 pt-5" style="border-top:1px solid rgba(255,255,255,0.1);">
                    @if($testimonial->photoUrl())
                    <img src="{{ $testimonial->photoUrl() }}" alt="{{ $testimonial->name }}" loading="lazy"
                         class="w-11 h-11 rounded-full object-cover object-top flex-shrink-0">
                    @else
                    {{-- Sans photo, l'initiale vaut mieux qu'une image cassée. --}}
                    <div class="w-11 h-11 rounded-full flex-shrink-0 flex items-center justify-center font-bold text-white"
                         style="background:var(--rose);font-family:'Playfair Display',serif;">
                        {{ $testimonial->initial() }}
                    </div>
                    @endif
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-white truncate">{{ $testimonial->name }}</p>
                        @if($testimonial->role)
                        <p class="text-xs truncate" style="color:rgba(255,255,255,0.4);">{{ $testimonial->role }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
