@extends('layouts.public')
@section('title', 'Acheter — '.$ebook->title)
@section('robots', 'noindex, nofollow')

@section('content')
<section class="min-h-[70vh] flex items-center justify-center py-20 px-5" style="background:var(--warm);">
    <div class="max-w-md w-full">
        <div class="card overflow-hidden">
            <div class="px-8 py-7" style="background:linear-gradient(160deg,#1A0A10 0%,#0D1418 100%);">
                <p class="text-xs font-bold uppercase tracking-[0.14em] mb-2" style="color:var(--rose);">Ebook</p>
                <h1 class="text-2xl font-bold text-white leading-tight" style="font-family:'Playfair Display',serif;">{{ $ebook->title }}</h1>
                <p class="mt-3 text-white text-lg font-bold">
                    {{ number_format($ebook->price, 0, ',', ' ') }} <span class="text-sm font-medium">{{ $ebook->currency }}</span>
                </p>
            </div>

            <div class="px-8 py-7">
                @if($errors->any())
                <div class="mb-4 p-3 rounded-xl text-sm" style="background:#FEF2F2;border:1px solid #FECACA;color:#dc2626;">
                    <ul class="space-y-0.5 list-none">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
                </div>
                @endif
                @if(session('error'))
                <div class="mb-4 p-3 rounded-xl text-sm" style="background:#FEF2F2;border:1px solid #FECACA;color:#dc2626;">{{ session('error') }}</div>
                @endif

                <p class="text-sm mb-5" style="color:var(--gray);">
                    Renseigne tes coordonnées : tu seras redirigée vers le paiement sécurisé (Wave, Orange Money, MTN, Moov ou carte). Ton ebook te sera envoyé par email juste après.
                </p>

                <form method="POST" action="{{ route('ebooks.buy.store', $ebook->slug) }}" class="space-y-4"
                      x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf
                    <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0;">

                    <div>
                        <label class="form-label">Nom complet <span style="color:var(--rose)">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-input" placeholder="Marie Koné" required>
                    </div>
                    <div>
                        <label class="form-label">Email <span style="color:var(--rose)">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-input" placeholder="marie@email.com" required>
                        <p class="text-xs mt-1" style="color:var(--gray);">C'est à cette adresse que ton ebook sera envoyé.</p>
                    </div>
                    <div>
                        <label class="form-label">Téléphone <span class="font-normal" style="color:var(--gray)">(optionnel)</span></label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" class="form-input" placeholder="+225 07 00 00 00 00">
                    </div>

                    <button type="submit" class="btn-rose w-full py-3.5" :disabled="submitting" :class="submitting ? 'opacity-70 cursor-not-allowed' : ''">
                        <template x-if="!submitting"><span>Payer {{ number_format($ebook->price, 0, ',', ' ') }} {{ $ebook->currency }} →</span></template>
                        <template x-if="submitting"><span class="inline-flex items-center gap-2"><svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg> Redirection…</span></template>
                    </button>
                    <a href="{{ route('ebooks.show', $ebook->slug) }}" class="block text-center text-xs mt-2" style="color:var(--gray);">← Retour à l'ebook</a>
                </form>
            </div>
        </div>
        <p class="text-center text-xs mt-4" style="color:var(--gray);">🔒 Paiement sécurisé via GeniusPay</p>
    </div>
</section>
@endsection
