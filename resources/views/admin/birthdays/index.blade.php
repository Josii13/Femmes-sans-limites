@extends('layouts.admin')
@section('title','Anniversaires')
@section('page-title','Anniversaires')
@section('page-subtitle', $filled->count().' date(s) recueillie(s), '.$missing->count().' manquante(s)')

@section('content')
@php
    $total = $filled->count() + $missing->count();
    $mois = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
             'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
@endphp

<div x-data="{ copied: false, confirm: null, confirmAction: '' }">

{{-- Confirmation, même composant que les autres écrans d'administration --}}
<div x-show="confirm !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.5);">
    <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4">
        <p class="font-semibold text-gray-800 mb-2" x-text="confirm?.title ?? ''"></p>
        <p class="text-sm text-gray-500 mb-5" x-text="confirm?.body ?? ''"></p>
        <div class="flex gap-3">
            <button @click="$refs[confirmAction].submit(); confirm = null" class="flex-1 btn-rose text-sm py-2.5">Confirmer</button>
            <button @click="confirm = null" class="flex-1 text-sm py-2.5 rounded-full border border-gray-200 hover:bg-gray-50 font-medium">Annuler</button>
        </div>
    </div>
</div>

{{-- Lien à partager --}}
<div class="admin-card p-5 mb-6">
    <h3 class="font-semibold text-sm mb-1" style="color:var(--dark);">Lien à partager dans le groupe WhatsApp</h3>
    <p class="text-xs mb-4" style="color:var(--gray);">
        Chaque membre clique sur sa photo, saisit sa date, puis disparaît de la page.
        Le lien contient un jeton : ne le publiez pas sur le site, il donne la liste des membres.
    </p>

    <div class="flex flex-col sm:flex-row gap-2">
        <input type="text" readonly value="{{ $link }}" x-ref="lien"
               class="form-input flex-1 font-mono text-xs" @focus="$event.target.select()">
        <button type="button"
                @click="navigator.clipboard.writeText($refs.lien.value); copied = true; setTimeout(() => copied = false, 2000)"
                class="btn-rose text-sm px-5 py-2.5 whitespace-nowrap">
            <span x-show="!copied">Copier le lien</span>
            <span x-show="copied" x-cloak>Copié ✓</span>
        </button>
    </div>

    <form x-ref="rotate" action="{{ route('admin.birthdays.rotate') }}" method="POST">@csrf</form>
    <button type="button"
            @click="confirm = { title: 'Générer un nouveau lien ?', body: 'L\'ancien cessera immédiatement de fonctionner. À utiliser si le lien a circulé hors du groupe.' }; confirmAction = 'rotate'"
            class="text-xs underline mt-3" style="color:var(--gray);">Régénérer le lien</button>
</div>

{{-- Avancement --}}
@if($total > 0)
<div class="admin-card p-5 mb-6">
    <div class="flex items-center justify-between text-xs mb-2" style="color:var(--gray);">
        <span>{{ $filled->count() }} sur {{ $total }} ont répondu</span>
        <span>{{ (int) round($filled->count() / $total * 100) }} %</span>
    </div>
    <div class="h-2.5 rounded-full overflow-hidden" style="background:#F3F4F6;">
        <div class="h-full rounded-full" style="width:{{ round($filled->count() / $total * 100, 1) }}%;background:var(--rose);"></div>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Dates recueillies, par mois --}}
    <div class="admin-card p-5">
        <h3 class="font-semibold text-sm mb-4" style="color:var(--dark);">Dates recueillies ({{ $filled->count() }})</h3>
        @forelse($filled->groupBy('birth_month') as $month => $group)
        <p class="text-[10px] font-bold uppercase tracking-wider mt-4 first:mt-0 mb-2" style="color:var(--gray);">{{ $mois[$month] }}</p>
        <div class="space-y-1.5">
            @foreach($group as $member)
            <div class="flex items-center justify-between py-2 px-3 rounded-xl" style="background:#F8F7F9;">
                <a href="{{ route('admin.members.show', $member) }}" class="text-sm font-medium hover:underline" style="color:var(--dark);">{{ $member->name }}</a>
                <span class="text-xs" style="color:var(--gray);">
                    {{ $member->birth_day }} {{ mb_strtolower($mois[$member->birth_month]) }}@if($member->birth_year) · {{ $member->ageOn() }} ans @endif
                </span>
            </div>
            @endforeach
        </div>
        @empty
        <p class="text-sm py-6 text-center" style="color:var(--gray);">Aucune date pour l'instant. Partagez le lien ci-dessus.</p>
        @endforelse
    </div>

    {{-- Encore attendues --}}
    <div class="admin-card p-5">
        <h3 class="font-semibold text-sm mb-4" style="color:var(--dark);">En attente ({{ $missing->count() }})</h3>
        @forelse($missing as $member)
        <div class="flex items-center justify-between py-2 px-3 rounded-xl mb-1.5" style="background:#F8F7F9;">
            <a href="{{ route('admin.members.show', $member) }}" class="text-sm font-medium hover:underline" style="color:var(--dark);">{{ $member->name }}</a>
            <span class="text-xs" style="color:var(--gray);">{{ $member->email }}</span>
        </div>
        @empty
        <p class="text-sm py-6 text-center" style="color:var(--gray);">Toutes les membres ont répondu 🎉</p>
        @endforelse
    </div>
</div>
</div>
@endsection
