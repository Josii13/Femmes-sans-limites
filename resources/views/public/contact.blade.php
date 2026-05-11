@extends('layouts.public')
@section('title', 'Contact — Femmes Sans Limites')

@section('content')

<section class="pt-40 pb-24 px-6" style="background:linear-gradient(135deg,#FDF0F5,#FDFAF9);">
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-16 items-start">

        {{-- Left --}}
        <div class="fade-up">
            <div class="gold-divider mb-8 mx-0"></div>
            <h1 class="text-5xl font-bold mb-6 text-left" style="color:var(--dark);">Parlons-nous</h1>
            <p class="text-lg leading-relaxed mb-12" style="color:var(--gray);">
                Une question, une idée, un partenariat ? Nous sommes là pour vous écouter et vous accompagner.
            </p>

            <div class="space-y-6">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(217,30,110,0.08);">
                        <svg class="w-5 h-5" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-sm mb-1" style="color:var(--dark);">Email</p>
                        <p style="color:var(--gray);">contact@femmessanslimites.com</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(201,168,76,0.1);">
                        <svg class="w-5 h-5" style="color:var(--gold);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-sm mb-1" style="color:var(--dark);">Réseaux sociaux</p>
                        <p style="color:var(--gray);">@femmessanslimites</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form --}}
        <div class="bg-white rounded-3xl p-10 shadow-lg fade-up">
            @if(session('success'))
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm mb-6" style="background:#D1FAE5;color:#065F46;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
            @endif

            <form action="{{ route('contact.send') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Prénom *</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-input @error('name') border-red-400 @enderror" placeholder="Marie">
                        @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-input @error('email') border-red-400 @enderror" placeholder="marie@email.com">
                        @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="form-label">Sujet *</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" class="form-input @error('subject') border-red-400 @enderror" placeholder="Votre sujet">
                    @error('subject')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Message *</label>
                    <textarea name="message" rows="5" class="form-input resize-none @error('message') border-red-400 @enderror" placeholder="Votre message...">{{ old('message') }}</textarea>
                    @error('message')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="btn-rose w-full justify-center">
                    Envoyer le message
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
            </form>
        </div>
    </div>
</section>

@endsection
