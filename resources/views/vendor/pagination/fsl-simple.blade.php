@if ($paginator->hasPages())
<nav class="flex items-center gap-2" aria-label="Pagination">
    @if ($paginator->onFirstPage())
    <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm cursor-not-allowed opacity-40" style="background:white;border:1px solid var(--border);color:var(--gray);">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg> Précédent
    </span>
    @else
    <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm transition-all hover:-translate-y-px" style="background:white;border:1px solid var(--border);color:var(--charcoal);">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg> Précédent
    </a>
    @endif

    @if ($paginator->hasMorePages())
    <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium transition-all hover:-translate-y-px text-white" style="background:var(--rose);">
        Suivant <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </a>
    @else
    <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm cursor-not-allowed opacity-40" style="background:var(--rose);color:white;">
        Suivant <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </span>
    @endif
</nav>
@endif
