@extends('layouts.admin')
@section('title','Ajouter un ebook')
@section('page-title','Ajouter un ebook')
@section('page-subtitle','Créer et publier un nouvel ebook FSL')

@section('content')
<div class="max-w-3xl">
    <div class="admin-card">
        <form action="{{ route('admin.ebooks.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            @if($errors->any())
            <div class="px-4 py-3 rounded-xl text-sm" style="background:#FEF2F2;border:1px solid #FCA5A5;color:#991B1B;">
                <ul class="space-y-1">
                    @foreach($errors->all() as $e)
                    <li class="flex items-center gap-2"><span style="color:var(--rose);">•</span> {{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Titre & catégorie --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="col-span-2 sm:col-span-1">
                    <label class="form-label">Titre de l'ebook *</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="form-input @error('title') border-red-400 @enderror" placeholder="Ex : Guide de leadership féminin">
                    @error('title')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Catégorie *</label>
                    <input type="text" name="category" value="{{ old('category') }}" class="form-input @error('category') border-red-400 @enderror"
                           placeholder="Leadership, Finance, Bien-être..." list="categories-list">
                    <datalist id="categories-list">
                        @foreach(['Leadership','Finance','Entrepreneuriat','Bien-être','Développement personnel','Famille','Carrière'] as $cat)
                        <option value="{{ $cat }}">
                        @endforeach
                    </datalist>
                    @error('category')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Description --}}
            <div>
                <label class="form-label">Description *</label>
                <textarea name="description" rows="5" class="form-input resize-none @error('description') border-red-400 @enderror"
                          placeholder="Présente le contenu de l'ebook, ce que la lectrice va apprendre, à qui il s'adresse...">{{ old('description') }}</textarea>
                @error('description')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Mot de l'autrice --}}
            <div>
                <label class="form-label">
                    Mot de l'autrice
                    <span class="font-normal text-gray-400">(optionnel)</span>
                </label>
                <textarea name="author_note" rows="3" class="form-input resize-none"
                          placeholder="Un message personnel de l'autrice sur cet ebook, sa genèse, son intention...">{{ old('author_note') }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Sera mis en avant dans une citation sur la page publique.</p>
            </div>

            {{-- Image de couverture --}}
            <div>
                <label class="form-label">Image de couverture</label>
                <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-rose-200 transition-colors cursor-pointer" onclick="document.getElementById('ebook-image').click()">
                    <input type="file" id="ebook-image" name="image" accept="image/*" class="sr-only" onchange="previewImg(this)">
                    <div id="img-preview" class="hidden mb-3">
                        <img id="preview-img" src="" alt="" class="h-48 mx-auto object-cover rounded-xl shadow-sm">
                    </div>
                    <div id="img-placeholder">
                        <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="text-sm text-gray-400">Cliquer pour choisir la couverture</p>
                        <p class="text-xs text-gray-300 mt-1">JPG, PNG — recommandé 400×560px — max 5 Mo</p>
                    </div>
                </div>
                @error('image')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- CTA --}}
            <div class="rounded-xl border border-gray-100 p-5 space-y-4">
                <div>
                    <p class="font-semibold text-sm mb-1" style="color:var(--dark);">Bouton d'action (CTA)</p>
                    <p class="text-xs text-gray-400">Ce bouton redirige vers la page produit Charriow.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Label du bouton *</label>
                        <select name="cta_label" class="form-input @error('cta_label') border-red-400 @enderror">
                            @foreach(['Télécharger maintenant','Lire maintenant','Obtenir l\'ebook','Commander sur Charriow','Accéder à l\'ebook'] as $label)
                            <option value="{{ $label }}" @selected(old('cta_label','Télécharger maintenant') === $label)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('cta_label')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Lien Charriow *</label>
                        <input type="url" name="cta_url" value="{{ old('cta_url') }}" class="form-input @error('cta_url') border-red-400 @enderror" placeholder="https://charriow.com/produit/...">
                        @error('cta_url')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Statut & ordre --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Statut *</label>
                    <select name="status" class="form-input">
                        <option value="draft"     @selected(old('status','draft')==='draft')>Brouillon</option>
                        <option value="published" @selected(old('status')==='published')>Publié</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Ordre d'affichage</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="form-input" min="0" placeholder="0 = premier">
                    <p class="text-xs text-gray-400 mt-1">Les chiffres les plus bas s'affichent en premier.</p>
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="btn-rose">Enregistrer l'ebook</button>
                <a href="{{ route('admin.ebooks.index') }}" class="btn-gold">Annuler</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function previewImg(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('img-preview').classList.remove('hidden');
            document.getElementById('img-placeholder').classList.add('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
