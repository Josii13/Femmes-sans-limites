@extends('layouts.admin')
@section('title','Nouveau membre')
@section('page-title','Nouveau membre')
@section('page-subtitle','Créer un nouveau membre et générer sa carte')

@section('content')
<div class="max-w-2xl">
    <div class="admin-card">
        <form action="{{ route('admin.members.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Nom complet *</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-input @error('name') border-red-400 @enderror" placeholder="Marie Dupont">
                    @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-input @error('email') border-red-400 @enderror" placeholder="marie@email.com">
                    @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="form-label">Téléphone <span class="font-normal text-gray-400">(optionnel)</span></label>
                <input type="tel" name="phone" value="{{ old('phone') }}" class="form-input" placeholder="+225 07 00 00 00 00">
            </div>

            <div>
                <label class="form-label">Profession *</label>
                <input type="text" name="profession" value="{{ old('profession') }}" class="form-input @error('profession') border-red-400 @enderror" placeholder="Entrepreneur, Médecin, Ingénieure...">
                @error('profession')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Pays *</label>
                    <input type="text" name="country" value="{{ old('country') }}" class="form-input @error('country') border-red-400 @enderror" placeholder="Côte d'Ivoire">
                    @error('country')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Ville *</label>
                    <input type="text" name="city" value="{{ old('city') }}" class="form-input @error('city') border-red-400 @enderror" placeholder="Abidjan">
                    @error('city')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="form-label">Type de membre *</label>
                <div class="grid grid-cols-3 gap-3 mt-2">
                    @foreach(['standard' => ['Argent/Gris','#6B7280'], 'gold' => ['Or','#C9A84C'], 'premium' => ['Rose','#D91E6E']] as $type => [$label,$color])
                    <label class="relative cursor-pointer">
                        <input type="radio" name="type" value="{{ $type }}" {{ old('type','standard') === $type ? 'checked' : '' }} class="sr-only peer">
                        <div class="border-2 rounded-xl p-4 text-center transition-all peer-checked:border-opacity-100 peer-checked:shadow-lg" style="border-color:{{ $color }}40;" x-bind:class="">
                            <div class="w-8 h-8 rounded-full mx-auto mb-2" style="background:{{ $color }};"></div>
                            <p class="font-semibold text-sm" style="color:{{ $color }};">{{ ucfirst($type) }}</p>
                            <p class="text-xs text-gray-400 mt-1">Badge {{ $label }}</p>
                        </div>
                        <div class="absolute top-2 right-2 w-4 h-4 rounded-full hidden peer-checked:flex items-center justify-center" style="background:{{ $color }};">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('type')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Photo du membre</label>
                <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-rose-200 transition-colors">
                    <input type="file" name="photo" id="photo" accept="image/*" class="sr-only" onchange="previewPhoto(this)">
                    <label for="photo" class="cursor-pointer">
                        <div id="photo-preview" class="hidden mb-3">
                            <img id="preview-img" src="" alt="" class="w-24 h-24 rounded-full object-cover mx-auto">
                        </div>
                        <svg id="photo-icon" class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="text-sm text-gray-400">Cliquez pour choisir une photo</p>
                        <p class="text-xs text-gray-300 mt-1">JPG, PNG — max 3 Mo</p>
                    </label>
                </div>
                @error('photo')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="btn-rose">
                    Créer le membre + générer la carte
                </button>
                <a href="{{ route('admin.members.index') }}" class="btn-gold">Annuler</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function previewPhoto(input) {
    const preview = document.getElementById('photo-preview');
    const img = document.getElementById('preview-img');
    const icon = document.getElementById('photo-icon');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { img.src = e.target.result; preview.classList.remove('hidden'); icon.classList.add('hidden'); };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
