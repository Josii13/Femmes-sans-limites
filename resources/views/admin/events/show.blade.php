@extends('layouts.admin')
@section('title', $event->title)
@section('page-title', $event->title)
@section('page-subtitle', $event->event_date->format('d M Y à H:i'))
@section('header-actions')
<a href="{{ route('events.show', $event->slug) }}" target="_blank" class="text-sm px-5 py-2 rounded-full border border-gray-200 hover:bg-gray-50 font-medium">🌐 Voir sur le site</a>
<a href="{{ route('admin.scanner.index', $event) }}" class="btn-rose text-sm px-5 py-2">📷 Scanner QR</a>
<a href="{{ route('admin.registrations.index', $event) }}" class="btn-gold text-sm px-5 py-2">Voir inscrits</a>
@if($event->is_sold_out)
<a href="{{ route('admin.events.waiting-list-admin', $event) }}" class="text-sm px-5 py-2 rounded-full border border-gray-200 hover:bg-gray-50 font-medium">⏳ Liste d'attente</a>
@endif
<a href="{{ route('admin.events.edit', $event) }}" class="text-sm px-5 py-2 rounded-full border border-gray-200 hover:bg-gray-50 font-medium">Modifier</a>
@endsection

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ confirm: null, confirmAction: '' }">

    {{-- Confirm modal --}}
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

    <div class="lg:col-span-2 space-y-5">
        <div class="admin-card">
            @if($event->image)
            <img src="{{ asset('storage/'.$event->image) }}" alt="{{ $event->title }}" class="w-full h-56 object-cover rounded-xl mb-6">
            @endif
            <h3 class="font-semibold text-sm mb-3" style="color:var(--dark);">Description</h3>
            <p class="text-sm leading-relaxed text-gray-600">{!! nl2br(e($event->description)) !!}</p>
        </div>

        @if($event->is_paid && $event->payment_link)
        <div class="admin-card" style="border:1px solid rgba(201,168,76,0.3); background:rgba(201,168,76,0.03);">
            <h3 class="font-semibold text-sm mb-2" style="color:var(--gold);">Lien de paiement configuré</h3>
            <a href="{{ $event->payment_link }}" target="_blank" class="text-sm break-all" style="color:var(--rose);">{{ $event->payment_link }}</a>
        </div>
        @endif

        {{-- Capacity progress --}}
        @if($event->capacity)
        @php
            $pct     = min(100, intval($event->registrations_count / $event->capacity * 100));
            $barColor = $pct >= 90 ? '#DC2626' : ($pct >= 70 ? '#C9A84C' : '#059669');
        @endphp
        <div class="admin-card">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-sm" style="color:var(--dark);">Remplissage</h3>
                <span class="text-sm font-bold" style="color:{{ $barColor }};">{{ $pct }}%</span>
            </div>
            <div class="h-3 rounded-full bg-gray-100 overflow-hidden mb-2">
                <div class="h-full rounded-full transition-all" style="width:{{ $pct }}%;background:{{ $barColor }};"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-400">
                <span>{{ $event->registrations_count }} inscrits</span>
                <span>{{ $event->capacity }} places</span>
            </div>
        </div>
        @endif
    </div>

    <div class="space-y-5">
        {{-- Stats --}}
        <div class="admin-card">
            <h3 class="font-semibold text-sm mb-4" style="color:var(--dark);">Statistiques</h3>
            <div class="space-y-3">
                @foreach([
                    ['Total inscrits',      $event->registrations_count,             'var(--dark)'],
                    ['Paiements confirmés', $event->paid_count,                      'var(--rose)'],
                    ['Présents',            $event->attended_count,                  '#7C3AED'],
                    ['Places restantes',    $event->spots_left ?? '∞',              'var(--gold)'],
                    ['Liste d\'attente',    $event->waiting_list_count,               '#6B7280'],
                ] as [$label, $val, $color])
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">{{ $label }}</span>
                    <span class="font-bold text-lg" style="color:{{ $color }};">{{ $val }}</span>
                </div>
                @endforeach
            </div>

            @if($event->is_paid && $event->paid_count > 0)
            <div class="mt-4 pt-4 border-t border-gray-50">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Revenus</span>
                    <span class="font-bold" style="color:var(--gold);">{{ number_format($event->paid_count * (float)$event->price, 0, ',', ' ') }} {{ $event->currency }}</span>
                </div>
            </div>
            @endif
        </div>

        {{-- Info --}}
        <div class="admin-card">
            <h3 class="font-semibold text-sm mb-4" style="color:var(--dark);">Informations</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Lieu</dt><dd class="font-medium text-right">{{ $event->location }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Ville</dt><dd class="font-medium">{{ $event->city ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Capacité</dt><dd class="font-medium">{{ $event->capacity ?? 'Illimité' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Prix</dt><dd class="font-medium" style="color:var(--rose);">{{ $event->is_paid ? number_format($event->price,0,',',' ').' '.$event->currency : 'Gratuit' }}</dd></div>
                @if($event->registration_closes_at)
                <div class="flex justify-between"><dt class="text-gray-500">Clôture inscriptions</dt><dd class="font-medium text-right">{{ $event->registration_closes_at->format('d M Y H:i') }}</dd></div>
                @endif
                <div class="flex justify-between"><dt class="text-gray-500">Statut</dt>
                <dd><span class="text-xs px-2 py-1 rounded-full font-medium" style="background:rgba(217,30,110,0.08);color:var(--rose);">{{ ucfirst($event->status) }}</span></dd>
                </div>
            </dl>
        </div>

        {{-- Actions --}}
        <div class="admin-card space-y-2">
            <h3 class="font-semibold text-sm mb-3" style="color:var(--dark);">Actions</h3>
            <form x-ref="dup_form" action="{{ route('admin.events.duplicate', $event) }}" method="POST">@csrf</form>
            <button type="button"
                @click="confirm = { title: 'Dupliquer cet événement ?', body: 'Une copie brouillon sera créée que vous pourrez modifier.' }; confirmAction = 'dup_form'"
                class="w-full text-sm py-2.5 rounded-xl font-medium transition-all text-center" style="background:rgba(201,168,76,0.08);color:var(--gold);"
                onmouseover="this.style.background='rgba(201,168,76,0.16)'" onmouseout="this.style.background='rgba(201,168,76,0.08)'">
                ⎘ Dupliquer cet événement
            </button>

            <form x-ref="del_form" action="{{ route('admin.events.destroy', $event) }}" method="POST">@csrf @method('DELETE')</form>
            <button type="button"
                @click="confirm = { title: 'Supprimer cet événement ?', body: 'L\'événement sera archivé. Suppression impossible s\'il a des inscriptions payées ou des présences.' }; confirmAction = 'del_form'"
                class="w-full text-sm py-2 rounded-xl font-medium text-red-400 hover:bg-red-50 border border-red-100 transition-colors">
                Supprimer l'événement
            </button>
        </div>
    </div>
</div>
@endsection
