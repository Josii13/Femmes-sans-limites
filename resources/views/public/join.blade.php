@extends('layouts.public')
@section('title', 'Rejoindre la communauté — Femmes Sans Limites')

@section('content')

{{-- Hero --}}
<section class="pt-32 pb-16 text-center" style="background:linear-gradient(160deg,#1A0A10 0%,#0D1418 100%);">
    <div class="max-w-2xl mx-auto px-6">
        <span class="inline-block text-xs font-semibold tracking-widest uppercase mb-4" style="color:var(--rose);">Adhésion</span>
        <h1 class="section-title text-white mb-4">Rejoins la communauté</h1>
        <p class="section-subtitle">Remplis ce formulaire pour soumettre ta candidature. Notre équipe l'examinera et te contactera sous 48h.</p>
    </div>
</section>

{{-- Form --}}
<section class="py-16 bg-white">
    <div class="max-w-2xl mx-auto px-6">

        <h2 class="text-2xl font-bold mb-8 text-center" style="color:var(--dark);">Ta candidature</h2>

        @if($errors->any())
        <div class="mb-6 p-4 rounded-xl text-sm" style="background:#D91E6E12;border:1px solid #D91E6E33;color:var(--rose);">
            <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('membership.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="form-label">Nom complet <span style="color:var(--rose);">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-input" placeholder="Marie Koné" required>
                </div>
                <div>
                    <label class="form-label">Email <span style="color:var(--rose);">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-input" placeholder="marie@exemple.com" required>
                </div>
                <div>
                    <label class="form-label">Profession <span style="color:var(--rose);">*</span></label>
                    <input type="text" name="profession" value="{{ old('profession') }}" class="form-input" placeholder="Entrepreneur, Médecin..." required>
                </div>
                <div>
                    <label class="form-label">Pays <span style="color:var(--rose);">*</span></label>
                    <input type="text" name="country" value="{{ old('country') }}" class="form-input" placeholder="Côte d'Ivoire" required>
                </div>
                <div class="md:col-span-2">
                    <label class="form-label">Ville <span style="color:var(--rose);">*</span></label>
                    <input type="text" name="city" value="{{ old('city') }}" class="form-input" placeholder="Abidjan" required>
                </div>
            </div>

            {{-- Photo --}}
            <div>
                <label class="form-label">Photo de profil <span class="text-gray-400 font-normal">(optionnel, max 3 Mo)</span></label>
                <div class="flex items-center gap-4 mt-2">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center flex-shrink-0 text-2xl font-bold text-white" style="background:var(--rose);">
                        <span id="photo-initial">?</span>
                        <img id="photo-img" src="" alt="" class="hidden w-full h-full object-cover rounded-full">
                    </div>
                    <label class="cursor-pointer flex-1">
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center hover:border-rose-300 transition-colors">
                            <p class="text-sm text-gray-500">Clique pour choisir une photo</p>
                            <p class="text-xs text-gray-400 mt-1">JPG, PNG ou WEBP</p>
                        </div>
                        <input type="file" name="photo" id="photo-input" accept="image/*" class="sr-only">
                    </label>
                </div>
            </div>

            <button type="submit" class="btn-rose w-full py-4 text-base font-bold">
                Soumettre ma candidature →
            </button>

            <p class="text-xs text-center text-gray-400">
                En soumettant ce formulaire, tu acceptes d'être contactée par l'équipe FSL.
                Ton adhésion commencera au niveau <strong>Standard</strong> — l'équipe peut ensuite l'ajuster selon ton profil.
            </p>
        </form>
    </div>
</section>

@endsection

@push('scripts')
<script>
document.getElementById('photo-input').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(ev) {
        document.getElementById('photo-initial').classList.add('hidden');
        const img = document.getElementById('photo-img');
        img.src = ev.target.result;
        img.classList.remove('hidden');
    };
    reader.readAsDataURL(file);
});
</script>
@endpush
