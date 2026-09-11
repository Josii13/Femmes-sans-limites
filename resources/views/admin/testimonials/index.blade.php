@extends('layouts.admin')
@section('title','Témoignages')
@section('page-title','Témoignages')
@section('page-subtitle', $testimonials->total().' témoignage(s)')

@section('content')

<div class="flex items-center justify-between mb-6">
    <p class="text-sm" style="color:var(--gray);">
        Ces témoignages alimentent la section « La parole aux membres » de la page d’accueil.
        @if($testimonials->total() === 0)
        Tant qu’aucun n’est publié, la section reste masquée.
        @endif
    </p>
    <a href="{{ route('admin.testimonials.create') }}" class="btn-rose text-sm whitespace-nowrap">+ Ajouter</a>
</div>

@if($testimonials->isEmpty())
<div class="admin-card text-center py-16">
    <svg class="w-12 h-12 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
    <p class="font-semibold" style="color:var(--dark);">Aucun témoignage pour l’instant</p>
    <p class="text-sm mt-1 max-w-md mx-auto" style="color:var(--gray);">
        Recueillez la parole de vos membres : c’est ce qui convainc une visiteuse
        d’adhérer, bien plus qu’un argumentaire.
    </p>
    <a href="{{ route('admin.testimonials.create') }}" class="btn-rose text-sm mt-5 inline-block">Ajouter le premier</a>
</div>
@else

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4" x-data="{ confirm: null, delForm: null }">
    @foreach($testimonials as $testimonial)
    <div class="admin-card flex flex-col {{ $testimonial->is_published ? '' : 'opacity-60' }}">
        <div class="flex items-start justify-between gap-3 mb-3">
            <div class="flex items-center gap-3 min-w-0">
                @if($testimonial->photoUrl())
                <img src="{{ $testimonial->photoUrl() }}" alt="" class="w-10 h-10 rounded-full object-cover object-top flex-shrink-0">
                @else
                <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center font-bold text-white" style="background:var(--rose);">
                    {{ $testimonial->initial() }}
                </div>
                @endif
                <div class="min-w-0">
                    <p class="font-semibold truncate" style="color:var(--dark);">{{ $testimonial->name }}</p>
                    @if($testimonial->role)
                    <p class="text-xs truncate" style="color:var(--gray);">{{ $testimonial->role }}</p>
                    @endif
                </div>
            </div>
            @if($testimonial->is_published)
            <span class="text-[10px] px-2 py-0.5 rounded-full font-medium flex-shrink-0" style="background:#05966918;color:#059669;">Publié</span>
            @else
            <span class="text-[10px] px-2 py-0.5 rounded-full font-medium bg-gray-100 text-gray-400 flex-shrink-0">Masqué</span>
            @endif
        </div>

        <p class="text-sm leading-relaxed flex-1 line-clamp-5" style="color:var(--gray);">« {{ $testimonial->quote }} »</p>

        <div class="flex items-center justify-between gap-2 pt-4 mt-4" style="border-top:1px solid var(--border);">
            <span class="text-[11px]" style="color:var(--gray);">
                Ordre {{ $testimonial->sort_order }}@if($testimonial->member) · membre liée @endif
            </span>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.testimonials.edit', $testimonial) }}"
                   class="text-xs px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">Éditer</a>
                <form x-ref="del_{{ $testimonial->id }}" action="{{ route('admin.testimonials.destroy', $testimonial) }}" method="POST">@csrf @method('DELETE')</form>
                <button type="button"
                        @click="confirm = '{{ addslashes($testimonial->name) }}'; delForm = $refs.del_{{ $testimonial->id }}"
                        class="text-xs px-3 py-1.5 rounded-lg text-red-400 hover:bg-red-50">Supprimer</button>
            </div>
        </div>
    </div>
    @endforeach

    {{-- Confirmation de suppression --}}
    <div x-show="confirm !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background:rgba(0,0,0,0.55);" @keydown.escape.window="confirm = null">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.outside="confirm = null">
            <p class="font-bold mb-2" style="color:var(--dark);">Supprimer ce témoignage ?</p>
            <p class="text-sm mb-5" style="color:var(--gray);">
                Le témoignage de <strong x-text="confirm"></strong> sera définitivement retiré du site.
            </p>
            <div class="flex gap-3">
                <button type="button" @click="delForm.submit()" class="btn-rose text-sm flex-1">Supprimer</button>
                <button type="button" @click="confirm = null" class="btn-gold text-sm flex-1">Annuler</button>
            </div>
        </div>
    </div>
</div>

<div class="mt-6">{{ $testimonials->links() }}</div>
@endif

@endsection
