@extends('layouts.admin')
@section('title', $member->name)
@section('page-title', $member->name)
@section('page-subtitle', $member->member_number)

@section('header-actions')
<a href="{{ route('admin.members.edit', $member) }}" class="btn-gold text-sm px-5 py-2">Modifier</a>
<a href="{{ route('admin.members.download-card', $member) }}" class="btn-rose text-sm px-5 py-2">⬇ Télécharger la carte</a>
@endsection

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ confirm: null }">

    {{-- Confirm modal --}}
    <div x-show="confirm !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.5);">
        <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4">
            <p class="font-semibold text-gray-800 mb-2" x-text="confirm?.title ?? ''"></p>
            <p class="text-sm text-gray-500 mb-5" x-text="confirm?.body ?? ''"></p>
            <div class="flex gap-3">
                <button @click="$refs.del_form.submit(); confirm = null" class="flex-1 text-sm py-2.5 rounded-full font-medium text-white" style="background:#DC2626;">Supprimer</button>
                <button @click="confirm = null" class="flex-1 text-sm py-2.5 rounded-full border border-gray-200 hover:bg-gray-50 font-medium">Annuler</button>
            </div>
        </div>
    </div>

    {{-- Profile --}}
    <div class="lg:col-span-1 space-y-5">
        <div class="admin-card text-center">
            <div class="w-28 h-28 rounded-full overflow-hidden mx-auto mb-4 flex items-center justify-center font-bold text-white text-4xl" style="background:{{ $member->badge_color }};">
                @if($member->photo)
                <img src="{{ asset('storage/'.$member->photo) }}" alt="{{ $member->name }}" class="w-full h-full object-cover">
                @else
                {{ substr($member->name, 0, 1) }}
                @endif
            </div>
            <h2 class="text-xl font-bold mb-1" style="color:var(--dark);">{{ $member->name }}</h2>
            <p class="text-sm mb-3" style="color:var(--gray);">{{ $member->profession }}</p>
            <span class="badge-{{ $member->type }} text-sm px-4 py-1.5">{{ ucfirst($member->type) }}</span>

            @if($member->status === 'active')
            <span class="inline-flex ml-2 text-xs px-2.5 py-1 rounded-full font-medium" style="background:#05966918;color:#059669;">Actif</span>
            @else
            <span class="inline-flex ml-2 text-xs px-2.5 py-1 rounded-full font-medium" style="background:#D91E6E18;color:#D91E6E;">En attente</span>
            @endif

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
                    <span class="text-gray-600">Membre depuis {{ $member->created_at->format('M Y') }}</span>
                </div>
            </div>

            <div class="mt-6 space-y-2">
                <form action="{{ route('admin.members.send-card', $member) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full text-sm py-2.5 rounded-xl font-medium transition-all" style="background:rgba(217,30,110,0.08);color:var(--rose);" onmouseover="this.style.background='rgba(217,30,110,0.15)'" onmouseout="this.style.background='rgba(217,30,110,0.08)'">
                        ✉ Envoyer la carte par email
                    </button>
                </form>
                <form x-ref="del_form" action="{{ route('admin.members.destroy', $member) }}" method="POST">
                    @csrf @method('DELETE')
                </form>
                <button type="button" @click="confirm = { title: 'Supprimer ce membre ?', body: 'Cette action est irréversible. Toutes les données de {{ $member->name }} seront supprimées.' }"
                    class="w-full text-sm py-2 rounded-xl font-medium text-red-400 hover:bg-red-50 transition-colors">
                    Supprimer le membre
                </button>
            </div>
        </div>
    </div>

    {{-- Right column --}}
    <div class="lg:col-span-2 space-y-5">
        {{-- Card preview --}}
        @if($member->card_path)
        <div class="admin-card">
            <h3 class="font-semibold text-sm mb-4" style="color:var(--dark);">Carte de membre</h3>
            <img src="{{ asset('storage/'.$member->card_path) }}" alt="Carte membre" class="w-full rounded-xl shadow-md">
        </div>
        @endif

        {{-- Privileges --}}
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

        {{-- Event history --}}
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
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium ml-3 flex-shrink-0" style="background:{{ $sc }}18;color:{{ $sc }};">{{ $sl }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
