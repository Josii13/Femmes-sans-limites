@extends('layouts.admin')
@section('title','Images du site')
@section('page-title','Images du site')
@section('page-subtitle', $images->flatten()->count().' image(s)')

@section('content')

<div class="mb-6 p-4 rounded-xl text-sm" style="background:#FFF8F1;border:1px solid #FDDCAD;color:#92400E;">
    <strong>Comment ça marche :</strong> cliquez sur <em>Modifier</em> pour remplacer une image sur le site en direct. L'image par défaut est toujours conservée et restaurable à tout moment.
</div>

@foreach($pages as $pageKey => $pageLabel)
@if($images->has($pageKey))
<div class="mb-10">

    {{-- En-tête de page --}}
    <div class="flex items-center gap-3 mb-5">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--rose-pale);">
            <svg class="w-4 h-4" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <h2 class="text-lg font-bold" style="color:var(--dark);font-family:'Playfair Display',serif;">{{ $pageLabel }}</h2>
        <span class="text-xs px-2 py-0.5 rounded-full" style="background:var(--rose-pale);color:var(--rose);">
            {{ $images[$pageKey]->count() }} image(s)
        </span>
    </div>

    {{-- Grille d'images --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
         x-data="{ modal: null }">

        {{-- Modal upload --}}
        <div x-show="modal !== null"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background:rgba(0,0,0,0.55);backdrop-filter:blur(3px);"
             @keydown.escape.window="modal = null">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md" @click.outside="modal = null">
                <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid #EEEBF0;">
                    <h3 class="font-semibold" style="color:var(--dark);" x-text="modal ? modal.label : ''"></h3>
                    <button @click="modal = null" class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="p-6" x-show="modal !== null">
                    {{-- Aperçu actuel --}}
                    <div class="mb-5">
                        <p class="text-xs font-semibold uppercase tracking-widest mb-2" style="color:var(--gray);">Image actuelle</p>
                        <div class="rounded-xl overflow-hidden" style="height:180px;background:#F4F3F6;">
                            <img :src="modal ? modal.url : ''"
                                 :alt="modal ? modal.label : ''"
                                 class="w-full h-full object-cover">
                        </div>
                    </div>
                    {{-- Formulaire upload --}}
                    <form :action="modal ? modal.action : '#'"
                          method="POST"
                          enctype="multipart/form-data"
                          x-ref="uploadForm">
                        @csrf
                        @method('PUT')
                        <label class="block text-xs font-semibold uppercase tracking-widest mb-2" style="color:var(--gray);">
                            Nouvelle image <span style="color:var(--rose);">*</span>
                        </label>
                        <input type="file"
                               name="image"
                               accept="image/jpeg,image/png,image/webp"
                               required
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:cursor-pointer"
                               style="file:background:var(--rose-pale);file:color:var(--rose);">
                        <p class="text-xs mt-1.5" style="color:var(--gray);">JPEG, PNG ou WebP — max 5 Mo. Préférez un ratio similaire à l'image actuelle.</p>
                        <div class="flex gap-3 mt-5">
                            <button type="submit" class="btn-rose flex-1 text-sm py-2.5">
                                Enregistrer
                            </button>
                            <button type="button" @click="modal = null" class="btn-outline flex-1 text-sm py-2.5">
                                Annuler
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @foreach($images[$pageKey] as $img)
        <div class="admin-card p-0 overflow-hidden group">

            {{-- Aperçu image --}}
            <div class="relative overflow-hidden" style="height:160px;background:#F4F3F6;">
                <img src="{{ $img->url }}"
                     alt="{{ $img->label }}"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                @if($img->isCustomized())
                <div class="absolute top-2 right-2">
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full text-white" style="background:var(--rose);">Modifiée</span>
                </div>
                @endif
            </div>

            {{-- Infos + actions --}}
            <div class="p-4">
                <p class="text-sm font-semibold leading-tight mb-3" style="color:var(--dark);">{{ $img->label }}</p>
                <div class="flex gap-2">
                    <button
                        type="button"
                        class="btn-rose text-xs py-1.5 px-3 flex-1"
                        @click="modal = {
                            label: '{{ addslashes($img->label) }}',
                            url: '{{ $img->url }}',
                            action: '{{ route('admin.site-images.update', $img) }}'
                        }">
                        Modifier
                    </button>
                    @if($img->isCustomized())
                    <form method="POST" action="{{ route('admin.site-images.reset', $img) }}" class="flex-shrink-0"
                          onsubmit="return confirm('Restaurer l\'image par défaut ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-outline text-xs py-1.5 px-3 whitespace-nowrap">
                            Défaut
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach

    </div>
</div>
@endif
@endforeach

@endsection
