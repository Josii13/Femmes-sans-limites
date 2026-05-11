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

{{-- Types d'adhésion --}}
<section class="py-12" style="background:#F8F7F9;">
    <div class="max-w-4xl mx-auto px-6">
        <p class="text-center text-sm font-semibold uppercase tracking-widest text-gray-400 mb-8">Choisir ton type d'adhésion</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            {{-- Standard --}}
            <div class="bg-white rounded-2xl p-6 border-2 border-gray-100 text-center transition-all duration-300 hover:border-gray-300">
                <div class="w-12 h-12 rounded-full mx-auto mb-4 flex items-center justify-center" style="background:#8B96A022;">
                    <span style="color:#8B96A0;font-size:20px;">◆</span>
                </div>
                <h3 class="font-bold text-lg mb-2" style="color:var(--dark);">Standard</h3>
                <ul class="text-sm text-gray-500 space-y-1.5 text-left">
                    <li>✓ Accès aux événements publics</li>
                    <li>✓ Carte membre numérique</li>
                    <li>✓ Newsletter mensuelle</li>
                    <li>✓ Communauté en ligne</li>
                </ul>
            </div>
            {{-- Gold --}}
            <div class="bg-white rounded-2xl p-6 text-center transition-all duration-300" style="border:2px solid var(--gold);">
                <div class="w-12 h-12 rounded-full mx-auto mb-4 flex items-center justify-center" style="background:#C9A84C22;">
                    <span style="color:var(--gold);font-size:20px;">★</span>
                </div>
                <span class="text-xs font-semibold uppercase tracking-widest px-3 py-1 rounded-full mb-3 inline-block" style="background:#C9A84C22;color:var(--gold);">Populaire</span>
                <h3 class="font-bold text-lg mb-2" style="color:var(--dark);">Gold</h3>
                <ul class="text-sm text-gray-500 space-y-1.5 text-left">
                    <li>✓ Tout Standard +</li>
                    <li>✓ Accès prioritaire aux events</li>
                    <li>✓ Sessions de mentorat</li>
                    <li>✓ Réseau Gold exclusif</li>
                </ul>
            </div>
            {{-- Premium --}}
            <div class="bg-white rounded-2xl p-6 text-center transition-all duration-300" style="border:2px solid var(--rose);">
                <div class="w-12 h-12 rounded-full mx-auto mb-4 flex items-center justify-center" style="background:#D91E6E22;">
                    <span style="color:var(--rose);font-size:20px;">♛</span>
                </div>
                <span class="text-xs font-semibold uppercase tracking-widest px-3 py-1 rounded-full mb-3 inline-block" style="background:#D91E6E22;color:var(--rose);">VIP</span>
                <h3 class="font-bold text-lg mb-2" style="color:var(--dark);">Premium</h3>
                <ul class="text-sm text-gray-500 space-y-1.5 text-left">
                    <li>✓ Tout Gold +</li>
                    <li>✓ Accès VIP aux événements</li>
                    <li>✓ Coaching individuel</li>
                    <li>✓ Visibilité partenaire FSL</li>
                </ul>
            </div>
        </div>
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

            {{-- Type selector --}}
            <div>
                <label class="form-label">Type d'adhésion <span style="color:var(--rose);">*</span></label>
                <div class="grid grid-cols-3 gap-3 mt-2">
                    @foreach(['standard' => ['◆','#8B96A0'], 'gold' => ['★','var(--gold)'], 'premium' => ['♛','var(--rose)']] as $val => [$icon, $color])
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="{{ $val }}" class="sr-only peer" {{ old('type', 'standard') === $val ? 'checked' : '' }}>
                        <div class="text-center p-3 rounded-xl border-2 transition-all peer-checked:border-current peer-checked:font-bold" style="border-color:#e5e7eb;--tw-text-opacity:1;" data-color="{{ $color }}">
                            <span style="color:{{ $color }};">{{ $icon }}</span>
                            <p class="text-sm mt-1 capitalize font-medium" style="color:var(--dark);">{{ ucfirst($val) }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

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
                    <div id="photo-preview" class="w-16 h-16 rounded-full flex items-center justify-center flex-shrink-0 text-2xl font-bold text-white" style="background:var(--rose);">
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

// Highlight selected type card
document.querySelectorAll('input[name="type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('input[name="type"]').forEach(r => {
            r.closest('label').querySelector('div').style.borderColor = '#e5e7eb';
        });
        const color = this.closest('label').querySelector('[data-color]').dataset.color;
        this.closest('label').querySelector('div').style.borderColor = color === 'var(--rose)' ? '#D91E6E'
            : color === 'var(--gold)' ? '#C9A84C' : '#8B96A0';
    });
    if (radio.checked) radio.dispatchEvent(new Event('change'));
});
</script>
@endpush
