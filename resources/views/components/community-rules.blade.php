{{--
    Les sept règles de la charte.

    Un seul composant pour la page publique, le formulaire d'adhésion et l'espace
    membre : une règle ne doit pas pouvoir diverger d'un endroit à l'autre.

    variant : full (page dédiée) | compact (aperçu dans un modal ou l'espace membre)
--}}
@props(['variant' => 'full'])

@php $compact = $variant === 'compact'; @endphp

<ol {{ $attributes->merge(['class' => $compact ? 'space-y-4' : 'space-y-8']) }}>
    @foreach(\App\Support\CommunityRules::all() as $index => $rule)
    <li class="flex gap-4">
        <span class="flex-shrink-0 rounded-full flex items-center justify-center font-bold text-white
                     {{ $compact ? 'w-7 h-7 text-xs' : 'w-9 h-9 text-sm' }}"
              style="background:var(--rose);font-family:'Playfair Display',serif;">
            {{ $index + 1 }}
        </span>

        <div class="min-w-0">
            <h2 class="font-bold {{ $compact ? 'text-sm' : 'text-lg' }}"
                style="color:var(--dark);font-family:'Playfair Display',serif;">
                {{ $rule['title'] }}
            </h2>
            <p class="font-medium {{ $compact ? 'text-xs mt-0.5' : 'text-sm mt-1' }}" style="color:var(--rose);">
                {{ $rule['summary'] }}
            </p>

            @if(! $compact)
            <div class="mt-3 space-y-2">
                @foreach($rule['body'] as $paragraph)
                <p class="text-base leading-relaxed" style="color:var(--gray);">{{ $paragraph }}</p>
                @endforeach
            </div>
            @endif
        </div>
    </li>
    @endforeach
</ol>
