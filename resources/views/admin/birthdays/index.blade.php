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

{{-- Envoi du lien par email à celles dont la date manque --}}
@if($missing->isNotEmpty())
<div class="admin-card p-5 mb-6">
    <h3 class="font-semibold text-sm mb-1" style="color:var(--dark);">Envoyer le lien par email</h3>
    <p class="text-xs mb-4" style="color:var(--gray);">
        Pour celles qui ne sont pas dans le groupe WhatsApp, ou qui ont laissé passer le lien.
        Chacune reçoit un lien qui ouvre directement son propre formulaire.
        Une membre déjà invitée ne reçoit pas de nouvel email avant {{ \App\Models\Member::BIRTHDAY_INVITE_COOLDOWN_DAYS }} jours,
        et celles qui ont refusé nos communications ne le reçoivent pas.
    </p>

    <form x-ref="invite" action="{{ route('admin.birthdays.invite') }}" method="POST">@csrf</form>
    @if($invitable > 0)
    <button type="button"
            @click="confirm = { title: 'Envoyer {{ $invitable }} email(s) ?', body: 'Chaque membre dont la date manque recevra le lien pour l\'indiquer.' }; confirmAction = 'invite'"
            class="btn-rose text-sm px-5 py-2.5">
        Envoyer à {{ $invitable }} membre(s)
    </button>
    @else
    <p class="text-sm" style="color:var(--gray);">
        Personne à relancer pour l'instant : les membres en attente ont été invitées il y a moins de
        {{ \App\Models\Member::BIRTHDAY_INVITE_COOLDOWN_DAYS }} jours, ou ne reçoivent pas nos communications.
    </p>
    @endif
</div>
@endif

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
            <span class="text-xs text-right" style="color:var(--gray);">
                {{ $member->email }}
                @if($member->birthday_invited_at)
                <br>invitée le {{ $member->birthday_invited_at->format('d/m') }}
                @endif
            </span>
        </div>
        @empty
        <p class="text-sm py-6 text-center" style="color:var(--gray);">Toutes les membres ont répondu 🎉</p>
        @endforelse
    </div>
</div>

{{-- Mur de vœux : modération --}}
@if($wishes->isNotEmpty())
<div class="admin-card p-5 mt-6">
    <h3 class="font-semibold text-sm mb-1" style="color:var(--dark);">Derniers messages reçus</h3>
    <p class="text-xs mb-4" style="color:var(--gray);">
        Masquer retire le message du mur sans le détruire : une décision prise vite un jour de fête doit pouvoir se défaire.
    </p>
    <div class="space-y-2">
        @foreach($wishes as $wish)
        <div class="flex items-start justify-between gap-3 py-2.5 px-3 rounded-xl" style="background:{{ $wish->hidden_at ? '#FEF2F2' : '#F8F7F9' }};">
            <div class="min-w-0">
                <p class="text-sm leading-snug {{ $wish->hidden_at ? 'line-through' : '' }}" style="color:var(--dark);">{{ $wish->message }}</p>
                <p class="text-xs mt-0.5" style="color:var(--gray);">
                    {{ $wish->author_name }} → {{ $wish->member?->name ?? '—' }} · {{ $wish->created_at->format('d/m/Y H:i') }}
                </p>
            </div>
            <form x-ref="wish_{{ $wish->id }}" action="{{ route('admin.birthdays.wish-toggle', $wish) }}" method="POST">@csrf</form>
            <button type="button"
                    @click="confirm = { title: '{{ $wish->hidden_at ? 'Réafficher ce message ?' : 'Masquer ce message ?' }}', body: '{{ $wish->hidden_at ? 'Il redeviendra visible sur le mur.' : 'Il disparaîtra du mur, sans être supprimé.' }}' }; confirmAction = 'wish_{{ $wish->id }}'"
                    class="text-xs px-3 py-1.5 rounded-lg flex-shrink-0" style="background:#fff;border:1px solid var(--border);color:var(--gray);">
                {{ $wish->hidden_at ? 'Réafficher' : 'Masquer' }}
            </button>
        </div>
        @endforeach
    </div>
</div>
@endif
</div>
@endsection
