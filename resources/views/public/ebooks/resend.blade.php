@extends('layouts.public')
@section('title', 'Renvoyer mon lien de téléchargement — FSL')
@section('robots', 'noindex, nofollow')

@section('content')
<section class="min-h-[60vh] flex items-center justify-center py-24 px-5" style="background:var(--warm);">
    <div class="max-w-md w-full">
        <div class="card p-8">
            @if(session('resent'))
            <div class="text-center">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:var(--rose-pale);">
                    <svg class="w-7 h-7" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <h1 class="text-xl font-bold mb-2" style="color:var(--dark);font-family:'Playfair Display',serif;">C'est envoyé</h1>
                <p class="text-sm leading-relaxed" style="color:var(--gray);">Si des achats sont associés à cette adresse, un email contenant le(s) lien(s) de téléchargement vient d'être envoyé. Pense à vérifier tes spams.</p>
                <a href="{{ route('ebooks.index') }}" class="btn-rose mt-6">Retour à la bibliothèque</a>
            </div>
            @else
            <h1 class="text-2xl font-bold mb-2" style="color:var(--dark);font-family:'Playfair Display',serif;">Renvoyer mon lien</h1>
            <p class="text-sm mb-6" style="color:var(--gray);">Tu as déjà acheté un ebook ? Indique l'email utilisé lors de l'achat, on te renvoie le lien de téléchargement.</p>

            @if($errors->any())
            <div class="mb-4 p-3 rounded-xl text-sm" style="background:#FEF2F2;border:1px solid #FECACA;color:#dc2626;">
                <ul class="space-y-0.5 list-none">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            <form method="POST" action="{{ route('ebooks.resend.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">Adresse email <span style="color:var(--rose)">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-input" placeholder="marie@email.com" required>
                </div>
                <button type="submit" class="btn-rose w-full py-3.5">Renvoyer mon lien</button>
                <a href="{{ route('ebooks.index') }}" class="block text-center text-xs" style="color:var(--gray);">← Retour à la bibliothèque</a>
            </form>
            @endif
        </div>
    </div>
</section>
@endsection
