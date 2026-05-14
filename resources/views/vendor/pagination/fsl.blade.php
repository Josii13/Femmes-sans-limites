@if ($paginator->hasPages())
<nav class="flex items-center justify-between gap-4 mt-1" aria-label="Pagination">

    {{-- Info résultats --}}
    <p class="text-xs hidden sm:block" style="color:var(--gray);">
        {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} sur {{ $paginator->total() }} résultat(s)
    </p>

    {{-- Boutons --}}
    <div class="flex items-center gap-1 mx-auto sm:mx-0">

        {{-- Précédent --}}
        @if ($paginator->onFirstPage())
        <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-sm cursor-not-allowed opacity-35" style="background:white;border:1px solid var(--border);color:var(--gray);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </span>
        @else
        <a href="{{ $paginator->previousPageUrl() }}"
           class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-sm transition-all hover:-translate-y-px"
           style="background:white;border:1px solid var(--border);color:var(--gray);"
           aria-label="Page précédente">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        @endif

        {{-- Pages --}}
        @foreach ($elements as $element)
            @if (is_string($element))
            <span class="inline-flex items-center justify-center w-9 h-9 text-sm" style="color:var(--gray);">…</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-sm font-bold text-white"
                          style="background:var(--rose);" aria-current="page">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}"
                       class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-sm font-medium transition-all hover:-translate-y-px"
                       style="background:white;border:1px solid var(--border);color:var(--charcoal);">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Suivant --}}
        @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}"
           class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-sm transition-all hover:-translate-y-px"
           style="background:white;border:1px solid var(--border);color:var(--gray);"
           aria-label="Page suivante">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
        @else
        <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-sm cursor-not-allowed opacity-35" style="background:white;border:1px solid var(--border);color:var(--gray);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </span>
        @endif

    </div>
</nav>
@endif
