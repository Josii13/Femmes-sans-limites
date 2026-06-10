@extends('layouts.admin')
@section('title', $member->name)
@section('page-title', $member->name)
@section('page-subtitle', $member->member_number)

@section('header-actions')
<a href="{{ route('admin.members.edit', $member) }}" class="btn-gold text-sm px-5 py-2">Modifier</a>
@if($member->card_path)
<a href="{{ route('admin.members.download-card', $member) }}" class="btn-rose text-sm px-5 py-2">⬇ Télécharger la carte</a>
@endif
@endsection

@section('content')

@if(session('success'))
<div class="mb-5 px-4 py-3 rounded-xl text-sm flex items-center gap-2" style="background:#F0FDF4;border:1px solid #86EFAC;color:#166534;">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-5 px-4 py-3 rounded-xl text-sm" style="background:#FEF2F2;border:1px solid #FCA5A5;color:#991B1B;">
    {{ session('error') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ confirm: null, confirmRef: '' }">

    {{-- Confirm modal --}}
    <div x-show="confirm !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.5);">
        <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4">
            <p class="font-semibold text-gray-800 mb-2" x-text="confirm?.title ?? ''"></p>
            <p class="text-sm text-gray-500 mb-5" x-text="confirm?.body ?? ''"></p>
            <div class="flex gap-3">
                <button @click="$refs[confirmRef]?.submit(); confirm = null"
                        class="flex-1 text-sm py-2.5 rounded-full font-medium text-white" style="background:#DC2626;">Confirmer</button>
                <button @click="confirm = null"
                        class="flex-1 text-sm py-2.5 rounded-full border border-gray-200 hover:bg-gray-50 font-medium">Annuler</button>
            </div>
        </div>
    </div>

    {{-- Col gauche — Profil --}}
    <div class="lg:col-span-1 space-y-5">
        <div class="admin-card text-center">
            {{-- Photo --}}
            <div class="w-28 h-28 rounded-full overflow-hidden mx-auto mb-4 flex items-center justify-center font-bold text-white text-4xl ring-4"
                 style="background:{{ $member->badge_color }};ring-color:{{ $member->badge_color }}22;">
                @if($member->photo)
                <img src="{{ asset('storage/'.$member->photo) }}" alt="{{ $member->name }}" class="w-full h-full object-cover">
                @else
                {{ mb_substr($member->name, 0, 1) }}
                @endif
            </div>

            <h2 class="text-xl font-bold mb-1" style="color:var(--dark);">{{ $member->name }}</h2>
            <p class="text-sm mb-3" style="color:var(--gray);">{{ $member->profession }}</p>

            @php
                $statusLabels = ['pending'=>'En attente','active'=>'Actif','rejected'=>'Refusé','expired'=>'Expiré','suspended'=>'Suspendu'];
            @endphp
            <div class="flex items-center justify-center gap-2 flex-wrap">
                <span class="badge-{{ $member->type }} text-sm px-4 py-1.5">{{ ucfirst($member->type) }}</span>
                <span class="badge-{{ $member->status }}">{{ $statusLabels[$member->status] ?? $member->status }}</span>
            </div>

            {{-- Infos contact --}}
            <div class="mt-6 pt-6 border-t border-gray-50 space-y-3 text-left">
                <div class="flex items-center gap-3 text-sm">
                    <svg class="w-4 h-4 flex-shrink-0" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span class="text-gray-600 break-all">{{ $member->email }}</span>
                </div>
                @if($member->phone)
                <div class="flex items-center gap-3 text-sm">
                    <svg class="w-4 h-4 flex-shrink-0" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <span class="text-gray-600">{{ $member->phone }}</span>
                </div>
                @endif
                <div class="flex items-center gap-3 text-sm">
                    <svg class="w-4 h-4 flex-shrink-0" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                    <span class="text-gray-600">{{ $member->city }}, {{ $member->country }}</span>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <svg class="w-4 h-4 flex-shrink-0" style="color:var(--gold);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="text-gray-600">Membre depuis {{ ($member->joined_at ?? $member->created_at)->translatedFormat('M Y') }}</span>
                </div>
                @if($member->expires_at)
                <div class="flex items-center gap-3 text-sm">
                    <svg class="w-4 h-4 flex-shrink-0" style="color:{{ $member->isExpired() ? '#DC2626' : ($member->expires_at->diffInDays(now(),false) > -30 ? '#B45309' : 'var(--gold)') }};" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-gray-600">
                        @if($member->isExpired())
                            <span style="color:#DC2626;font-weight:600;">Expirée</span> le {{ $member->expires_at->translatedFormat('d M Y') }}
                        @else
                            Valable jusqu'au {{ $member->expires_at->translatedFormat('d M Y') }}
                        @endif
                    </span>
                </div>
                @endif
            </div>

            {{-- Actions --}}
            <div class="mt-6 space-y-2">
                {{-- Activer (candidature ou réactivation) --}}
                @if(in_array($member->status, ['pending','expired','rejected','suspended']))
                <form x-ref="act_form" action="{{ route('admin.members.activate', $member) }}" method="POST">@csrf</form>
                <button type="button"
                    @click="confirm = { title: 'Activer ce membre ?', body: '{{ $member->name }} recevra sa carte membre par email.' }; confirmRef = 'act_form'"
                    class="w-full text-sm py-2.5 rounded-xl font-medium text-white transition-all" style="background:#059669;">
                    ✓ Activer le compte
                </button>
                @endif

                {{-- Renouveler (membre actif) --}}
                @if($member->status === 'active')
                <form x-ref="renew_form" action="{{ route('admin.members.activate', $member) }}" method="POST">@csrf</form>
                <button type="button"
                    @click="confirm = { title: 'Renouveler l\'adhésion ?', body: 'La validité de {{ addslashes($member->name) }} sera prolongée d\'un an et sa carte renvoyée.' }; confirmRef = 'renew_form'"
                    class="w-full text-sm py-2.5 rounded-xl font-medium text-white transition-all" style="background:var(--gold);">
                    ↻ Renouveler l'adhésion (+1 an)
                </button>
                @endif

                {{-- Refuser (candidature en attente) --}}
                @if($member->status === 'pending')
                <form x-ref="rej_form" action="{{ route('admin.members.reject', $member) }}" method="POST">@csrf</form>
                <button type="button"
                    @click="confirm = { title: 'Refuser cette candidature ?', body: '{{ addslashes($member->name) }} recevra un email de refus bienveillant.' }; confirmRef = 'rej_form'"
                    class="w-full text-sm py-2.5 rounded-xl font-medium transition-all" style="background:#FEE2E2;color:#991B1B;">
                    Refuser la candidature
                </button>
                @endif

                {{-- Envoyer la carte --}}
                <form action="{{ route('admin.members.send-card', $member) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="w-full text-sm py-2.5 rounded-xl font-medium transition-all" style="background:rgba(217,30,110,0.08);color:var(--rose);"
                        onmouseover="this.style.background='rgba(217,30,110,0.15)'"
                        onmouseout="this.style.background='rgba(217,30,110,0.08)'">
                        ✉ Envoyer la carte par email
                    </button>
                </form>

                {{-- Supprimer --}}
                <form x-ref="del_form" action="{{ route('admin.members.destroy', $member) }}" method="POST">
                    @csrf @method('DELETE')
                </form>
                <button type="button"
                    @click="confirm = { title: 'Supprimer ce membre ?', body: 'Cette action est irréversible. Toutes les données de {{ addslashes($member->name) }} seront supprimées.' }; confirmRef = 'del_form'"
                    class="w-full text-sm py-2 rounded-xl font-medium text-red-400 hover:bg-red-50 transition-colors">
                    Supprimer le membre
                </button>
            </div>
        </div>

        {{-- Privilèges --}}
        <div class="admin-card">
            <h3 class="font-semibold text-sm mb-4" style="color:var(--dark);">Privilèges — {{ ucfirst($member->type) }}</h3>
            <ul class="space-y-2">
                @foreach($member->privileges_list as $priv)
                <li class="flex items-start gap-2 text-sm text-gray-600">
                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" style="color:{{ $member->badge_color }};" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ $priv }}
                </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Col droite --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Carte membre --}}
        <div class="admin-card space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-sm" style="color:var(--dark);">Carte de membre</h3>
                    <p class="text-xs mt-0.5" style="color:var(--gray);">Design {{ ucfirst($member->type) }} · 900 × 560 px</p>
                </div>
                <div class="flex items-center gap-2">
                    @if($member->card_path)
                    <a href="{{ route('admin.members.download-card', $member) }}"
                       class="text-xs px-3 py-1.5 rounded-lg font-medium text-white"
                       style="background:var(--gold);">
                        ⬇ Télécharger
                    </a>
                    @endif
                    <form x-ref="regen_form" action="{{ route('admin.members.regenerate-card', $member) }}" method="POST">@csrf</form>
                    <button type="button"
                        @click="confirm = { title: 'Régénérer la carte ?', body: 'La carte actuelle sera remplacée par une nouvelle version.' }; confirmRef = 'regen_form'"
                        class="text-xs px-3 py-1.5 rounded-lg font-medium border transition-colors"
                        style="border-color:var(--border);color:var(--gray);"
                        onmouseover="this.style.background='#F8F7F9'"
                        onmouseout="this.style.background='transparent'">
                        ↺ Régénérer
                    </button>
                </div>
            </div>

            @if($member->card_path && Storage::disk('public')->exists($member->card_path))
            {{-- Aperçu carte --}}
            <div class="relative overflow-hidden rounded-xl shadow-lg"
                 style="background:linear-gradient(135deg,#1a0a1e,#2d0e2b);">
                <img src="{{ asset('storage/'.$member->card_path) }}?v={{ filemtime(Storage::disk('public')->path($member->card_path)) }}"
                     alt="Carte membre {{ $member->name }}"
                     class="w-full object-contain">
            </div>
            <p class="text-xs text-center" style="color:var(--gray);">
                Générée le {{ \Carbon\Carbon::createFromTimestamp(filemtime(Storage::disk('public')->path($member->card_path)))->format('d/m/Y à H\hi') }}
                · {{ number_format(Storage::disk('public')->size($member->card_path) / 1024, 1) }} Ko
            </p>
            @else
            {{-- Pas de carte --}}
            <div class="flex flex-col items-center justify-center py-12 rounded-xl border-2 border-dashed" style="border-color:var(--border);">
                <svg class="w-10 h-10 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                <p class="text-sm font-medium text-gray-500 mb-1">Aucune carte générée</p>
                <p class="text-xs text-gray-400 mb-4">Cliquez sur "Régénérer" pour créer la carte.</p>
                <form action="{{ route('admin.members.regenerate-card', $member) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-rose text-sm px-5 py-2">Générer la carte</button>
                </form>
            </div>
            @endif
        </div>

        {{-- Motivation --}}
        @if($member->motivation)
        <div class="admin-card">
            <h3 class="font-semibold text-sm mb-3" style="color:var(--dark);">Motivation</h3>
            <p class="text-sm leading-relaxed whitespace-pre-line" style="color:var(--gray);">{{ $member->motivation }}</p>
        </div>
        @endif

        {{-- Historique événements --}}
        @if($member->registrations->isNotEmpty())
        <div class="admin-card">
            <h3 class="font-semibold text-sm mb-4" style="color:var(--dark);">Historique des événements ({{ $member->registrations->count() }})</h3>
            <div class="space-y-2">
                @foreach($member->registrations->sortByDesc('created_at') as $reg)
                @php
                    $statusColors = ['pending'=>'#6B7280','payment_sent'=>'#C9A84C','paid'=>'#059669','attended'=>'#7C3AED','cancelled'=>'#DC2626'];
                    $statusLabels = ['pending'=>'En attente','payment_sent'=>'Lien envoyé','paid'=>'Payé','attended'=>'Présent','cancelled'=>'Annulé'];
                    $sc = $statusColors[$reg->status] ?? '#999';
                    $sl = $statusLabels[$reg->status] ?? $reg->status;
                @endphp
                <div class="flex items-center justify-between py-2.5 px-3 rounded-xl" style="background:#F8F7F9;">
                    <div>
                        <p class="text-sm font-medium" style="color:var(--dark);">{{ $reg->event?->title ?? '—' }}</p>
                        <p class="text-xs text-gray-400">{{ $reg->event?->event_date?->format('d M Y') ?? '' }} · inscrit le {{ $reg->created_at->format('d/m/Y') }}</p>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium ml-3 flex-shrink-0"
                          style="background:{{ $sc }}18;color:{{ $sc }};">{{ $sl }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="admin-card">
            <h3 class="font-semibold text-sm mb-3" style="color:var(--dark);">Événements</h3>
            <p class="text-sm text-gray-400">Aucune inscription à un événement pour ce membre.</p>
        </div>
        @endif
    </div>

</div>
@endsection
