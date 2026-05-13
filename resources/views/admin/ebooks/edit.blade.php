@extends('layouts.admin')
@section('title','Modifier '.$ebook->title)
@section('page-title','Modifier l\'ebook')
@section('page-subtitle', $ebook->title)

@section('content')
<div class="max-w-3xl">
    <div class="admin-card">
        <form action="{{ route('admin.ebooks.update', $ebook) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf @method('PUT')

            @if($errors->any())
            <div class="px-4 py-3 rounded-xl text-sm" style="background:#FEF2F2;border:1px solid #FCA5A5;color:#991B1B;">
                <ul class="space-y-1">
                    @foreach($errors->all() as $e)
                    <li class="flex items-center gap-2"><span style="color:var(--rose);">•</span> {{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="grid grid-cols-2 gap-5">
                <div class="col-span-2 sm:col-span-1">
                    <label class="form-label">Titre *</label>
                    <input type="text" name="title" value="{{ old('title', $ebook->title) }}" class="form-input @error('title') border-red-400 @enderror">
                    @error('title')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Catégorie *</label>
                    <input type="text" name="category" value="{{ old('category', $ebook->category) }}" class="form-input @error('category') border-red-400 @enderror"
                           list="categories-list">
                    <datalist id="categories-list">
                        @foreach(['Leadership','Finance','Entrepreneuriat','Bien-être','Développement personnel','Famille','Carrière'] as $cat)
                        <option value="{{ $cat }}">
                        @endforeach
                    </datalist>
                    @error('category')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="form-label">Description *</label>
                <textarea name="description" rows="5" class="form-input resize-none @error('description') border-red-400 @enderror">{{ old('description', $ebook->description) }}</textarea>
                @error('description')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Mot de l'autrice <span class="font-normal text-gray-400">(optionnel)</span></label>
                <textarea name="author_note" rows="3" class="form-input resize-none">{{ old('author_note', $ebook->author_note) }}</textarea>
            </div>

            <div>
                <label class="form-label">Image de couverture</label>
                @if($ebook->image)
                <div class="flex items-center gap-4 mb-3">
                    <img src="{{ asset('storage/'.$ebook->image) }}" alt="" class="h-24 rounded-xl object-cover shadow-sm">
                    <span class="text-xs text-gray-400">Couverture actuelle — choisir un nouveau fichier pour remplacer</span>
                </div>
                @endif
                <input type="file" name="image" accept="image/*" class="form-input" onchange="previewImg(this)">
                <div id="img-preview" class="hidden mt-3">
                    <img id="preview-img" src="" alt="" class="h-32 rounded-xl object-cover shadow-sm">
                </div>
                @error('image')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="rounded-xl border border-gray-100 p-5 space-y-4">
                <p class="font-semibold text-sm" style="color:var(--dark);">Bouton d'action (CTA)</p>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Label du bouton *</label>
                        <select name="cta_label" class="form-input">
                            @foreach(['Télécharger maintenant','Lire maintenant','Obtenir l\'ebook','Commander sur Charriow','Accéder à l\'ebook'] as $label)
                            <option value="{{ $label }}" @selected(old('cta_label', $ebook->cta_label) === $label)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Lien Charriow *</label>
                        <input type="url" name="cta_url" value="{{ old('cta_url', $ebook->cta_url) }}" class="form-input @error('cta_url') border-red-400 @enderror">
                        @error('cta_url')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Statut *</label>
                    <select name="status" class="form-input">
                        <option value="draft"     @selected(old('status', $ebook->status)==='draft')>Brouillon</option>
                        <option value="published" @selected(old('status', $ebook->status)==='published')>Publié</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Ordre d'affichage</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $ebook->sort_order) }}" class="form-input" min="0">
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="btn-rose">Enregistrer les modifications</button>
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
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
