@extends('layouts.public')
@section('title', 'Ton anniversaire — Femme Sans Limites')
{{-- Le mur porte les noms et les photos des membres : jamais indexé. --}}
@section('robots', 'noindex, nofollow')

@section('content')
@php
    $total = $members->count() + $done;
    $mois = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
             'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
@endphp

<section class="py-24 px-5" style="background:var(--warm);min-height:70vh;">
    <div class="max-w-4xl mx-auto"
         x-data="{ open: @js((bool) $preselected), member: @js($preselected?->id), name: @js($preselected?->name ?? '') }">

        <div class="text-center mb-10">
            <h1 class="text-3xl sm:text-4xl font-bold mb-3" style="color:var(--dark);font-family:'Playfair Display',serif;">
                On veut fêter ton anniversaire 🎂
            </h1>
            <p class="text-base leading-relaxed max-w-xl mx-auto" style="color:var(--gray);">
                Trouve-toi dans la liste, clique sur ta photo et indique ta date de naissance.
                L'année est facultative. Une fois ta date enregistrée, tu disparais de cette page.
            </p>
        </div>

        @if(session('saved'))
        <div class="flex items-center gap-3 px-5 py-4 rounded-2xl text-sm mb-6 max-w-xl mx-auto" style="background:#D1FAE5;color:#065F46;">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>C'est noté, merci {{ session('saved') }} ! On garde la surprise.</span>
        </div>
        @endif

        @if(session('error'))
        <div class="px-5 py-4 rounded-2xl text-sm mb-6 max-w-xl mx-auto" style="background:#FEE2E2;color:#991B1B;">{{ session('error') }}</div>
        @endif

        @if($errors->any())
        <div class="px-5 py-4 rounded-2xl text-sm mb-6 max-w-xl mx-auto" style="background:#FEF2F2;border:1px solid #FCA5A5;color:#991B1B;">
            <ul class="space-y-0.5 list-none">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        @if($total > 0)
        {{-- L'avancement collectif : dans un groupe WhatsApp, voir le compteur
             monter fait plus pour la participation que n'importe quelle relance. --}}
        <div class="max-w-xl mx-auto mb-10">
            <div class="flex items-center justify-between text-xs mb-2" style="color:var(--gray);">
                <span>{{ $done }} sur {{ $total }} ont répondu</span>
                <span>{{ (int) round($done / $total * 100) }} %</span>
            </div>
            <div class="h-2 rounded-full overflow-hidden" style="background:var(--border);">
                <div class="h-full rounded-full" style="width:{{ round($done / $total * 100, 1) }}%;background:var(--rose);"></div>
            </div>
        </div>
        @endif

        @if($members->isEmpty())
        <div class="card p-10 text-center max-w-xl mx-auto">
            <p class="text-5xl mb-4">🎉</p>
            <h2 class="text-xl font-bold mb-2" style="color:var(--dark);font-family:'Playfair Display',serif;">Tout le monde a répondu</h2>
            <p class="text-sm" style="color:var(--gray);">Plus une seule date ne manque. Merci à toutes !</p>
            <a href="{{ route('home') }}" class="btn-rose mt-6">Retour au site</a>
        </div>
        @else

        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-4">
            @foreach($members as $member)
            @php
                // Le refus de la galerie publique vaut ici aussi : on affiche
                // les initiales, pas la photo.
                $showPhoto = $member->photo && $member->show_in_gallery;
                $initials = collect(explode(' ', trim($member->name)))
                    ->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
            @endphp
            <button type="button"
                    @click="member = {{ $member->id }}; name = @js($member->name); open = true"
                    class="group text-center">
                <div class="aspect-square rounded-2xl overflow-hidden mb-2 transition-transform group-hover:scale-105"
                     style="border:2px solid var(--border);background:var(--rose-pale);">
                    @if($showPhoto)
                    <img src="{{ asset('storage/'.$member->photo) }}" alt="" class="w-full h-full object-cover">
                    @else
                    <div class="w-full h-full flex items-center justify-center text-xl font-bold" style="color:var(--rose);">{{ $initials }}</div>
                    @endif
                </div>
                <p class="text-xs font-medium leading-tight" style="color:var(--dark);">{{ $member->name }}</p>
            </button>
            @endforeach
        </div>

        {{-- Saisie de la date --}}
        <div x-show="open" x-cloak class="modal-overlay" @click.self="open = false" x-transition.opacity>
            <div class="modal-box" @keydown.escape.window="open = false">
                <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid var(--border);">
                    <h2 class="text-lg font-bold" style="color:var(--dark);font-family:'Playfair Display',serif;" x-text="name"></h2>
                    <button @click="open = false" class="text-gray-400 hover:text-gray-700 text-2xl leading-none">&times;</button>
                </div>

                <form method="POST" action="{{ route('birthdays.collect.store', $token) }}" class="px-6 py-5 space-y-4">
                    @csrf
                    <input type="hidden" name="member_id" :value="member">

                    <p class="text-sm" style="color:var(--gray);">Quelle est ta date de naissance ?</p>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">Jour <span style="color:var(--rose);">*</span></label>
                            <select name="day" class="form-input" required>
                                <option value="">—</option>
                                @for($d = 1; $d <= 31; $d++)
                                <option value="{{ $d }}" @selected(old('day') == $d)>{{ $d }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Mois <span style="color:var(--rose);">*</span></label>
                            <select name="month" class="form-input" required>
                                <option value="">—</option>
                                @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" @selected(old('month') == $m)>{{ $mois[$m] }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Année <span class="font-normal" style="color:var(--gray);">(facultatif)</span></label>
                        <select name="year" class="form-input">
                            <option value="">Je préfère ne pas le dire</option>
                            @for($y = now()->year - 10; $y >= 1920; $y--)
                            <option value="{{ $y }}" @selected(old('year') == $y)>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <button type="submit" class="btn-rose w-full justify-center py-3.5">Enregistrer ma date</button>
                    <p class="text-xs text-center" style="color:var(--gray);">
                        Ta date ne sert qu'à te souhaiter ton anniversaire. Elle n'est affichée nulle part publiquement.
                    </p>

                    <x-honeypot />
                </form>
            </div>
        </div>
        @endif
    </div>
</section>
@endsection
