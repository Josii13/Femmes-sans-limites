@extends('layouts.admin')
@section('title','Ebooks')
@section('page-title','Ebooks')
@section('page-subtitle', $ebooks->total().' ebook(s)')

@section('header-actions')
<a href="{{ route('admin.ebooks.create') }}" class="btn-rose text-sm px-5 py-2">+ Ajouter un ebook</a>
@endsection

@section('content')

{{-- Filters --}}
<form method="GET" class="flex flex-wrap gap-3 mb-6">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Titre, catégorie..." class="form-input flex-1 min-w-[180px] sm:w-64 sm:flex-none">
    <select name="status" class="form-input w-40">
        <option value="">Tous les statuts</option>
        <option value="published" @selected(request('status')=='published')>Publié</option>
        <option value="draft"     @selected(request('status')=='draft')>Brouillon</option>
    </select>
    <button type="submit" class="btn-rose text-sm px-5 py-2">Filtrer</button>
    @if(request()->anyFilled(['search','status']))
    <a href="{{ route('admin.ebooks.index') }}" class="btn-gold text-sm px-5 py-2">Réinitialiser</a>
    @endif
</form>

<div class="admin-card overflow-hidden p-0" x-data="{ confirm: null, delForm: null }">

    {{-- Confirm modal --}}
    <div x-show="confirm !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.5);">
        <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4">
            <p class="font-semibold text-gray-800 mb-2">Supprimer cet ebook ?</p>
            <p class="text-sm text-gray-500 mb-5" x-text="confirm"></p>
            <div class="flex gap-3">
                <button @click="delForm.submit(); confirm = null" class="flex-1 text-sm py-2.5 rounded-full font-medium text-white" style="background:#DC2626;">Supprimer</button>
                <button @click="confirm = null" class="flex-1 text-sm py-2.5 rounded-full border border-gray-200 hover:bg-gray-50 font-medium">Annuler</button>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
    <table class="w-full text-sm min-w-[720px]">
        <thead>
            <tr style="background:#F8F7F9;">
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Ebook</th>
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Catégorie</th>
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">CTA</th>
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Ordre</th>
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Statut</th>
                <th class="text-right px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($ebooks as $ebook)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-4">
                        @if($ebook->image)
                        <img src="{{ asset('storage/'.$ebook->image) }}" alt="{{ $ebook->title }}" class="w-14 h-20 object-cover rounded-lg flex-shrink-0 shadow-sm">
                        @else
                        <div class="w-14 h-20 rounded-lg flex-shrink-0 flex items-center justify-center" style="background:linear-gradient(135deg,var(--rose-pale),var(--gold-pale));">
                            <svg class="w-6 h-6" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        @endif
                        <div>
                            <p class="font-semibold" style="color:var(--dark);">{{ $ebook->title }}</p>
                            <p class="text-xs text-gray-400 mt-0.5 line-clamp-2 max-w-xs">{{ Str::limit($ebook->description, 80) }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium" style="background:var(--rose-pale);color:var(--rose);">{{ $ebook->category }}</span>
                </td>
                <td class="px-6 py-4">
                    <p class="text-sm font-medium" style="color:var(--dark);">{{ $ebook->cta_label }}</p>
                    <a href="{{ $ebook->cta_url }}" target="_blank" class="text-xs break-all" style="color:var(--gray);" title="{{ $ebook->cta_url }}">{{ Str::limit($ebook->cta_url, 40) }}</a>
                </td>
                <td class="px-6 py-4 text-center text-gray-500 text-sm">{{ $ebook->sort_order }}</td>
                <td class="px-6 py-4">
                    @if($ebook->status === 'published')
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium" style="background:#05966918;color:#059669;">Publié</span>
                    @else
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium bg-gray-100 text-gray-400">Brouillon</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                        @if($ebook->status === 'published')
                        <a href="{{ route('ebooks.show', $ebook->slug) }}" target="_blank" title="Voir sur le site public" class="text-xs px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">Voir</a>
                        @endif
                        <a href="{{ route('admin.ebooks.edit', $ebook) }}" class="text-xs px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">Éditer</a>
                        <form x-ref="del_{{ $ebook->id }}" action="{{ route('admin.ebooks.destroy', $ebook) }}" method="POST">@csrf @method('DELETE')</form>
                        <button type="button"
                            @click="confirm = '{{ addslashes($ebook->title) }}'; delForm = $refs.del_{{ $ebook->id }}"
                            class="text-xs px-3 py-1.5 rounded-lg text-red-400 hover:bg-red-50">Supprimer</button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-16">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:var(--rose-pale);">
                        <svg class="w-8 h-8" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <p class="text-gray-400">Aucun ebook. <a href="{{ route('admin.ebooks.create') }}" style="color:var(--rose);">En ajouter un</a></p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
    @if($ebooks->hasPages())
    <div class="px-6 py-4 border-t border-gray-50">{{ $ebooks->links() }}</div>
    @endif
</div>
@endsection
