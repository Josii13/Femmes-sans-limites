@extends('layouts.admin')
@section('title', $event->title)
@section('page-title', $event->title)
@section('page-subtitle', $event->event_date->format('d M Y à H:i'))
@section('header-actions')
<a href="{{ route('admin.scanner.index', $event) }}" class="btn-rose text-sm px-5 py-2">📷 Scanner QR</a>
<a href="{{ route('admin.registrations.index', $event) }}" class="btn-gold text-sm px-5 py-2">Voir inscrits</a>
<a href="{{ route('admin.events.edit', $event) }}" class="text-sm px-5 py-2 rounded-full border border-gray-200 hover:bg-gray-50 font-medium">Modifier</a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
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
    </div>

    <div class="space-y-5">
        {{-- Stats --}}
        <div class="admin-card">
            <h3 class="font-semibold text-sm mb-4" style="color:var(--dark);">Statistiques</h3>
            <div class="space-y-3">
                @foreach([
                    ['Total inscrits', $event->registrations_count, 'var(--dark)'],
                    ['Paiements confirmés', $event->paid_count, 'var(--rose)'],
                    ['Places restantes', $event->spots_left ?? '∞', 'var(--gold)'],
                ] as [$label, $val, $color])
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">{{ $label }}</span>
                    <span class="font-bold text-lg" style="color:{{ $color }};">{{ $val }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Info --}}
        <div class="admin-card">
            <h3 class="font-semibold text-sm mb-4" style="color:var(--dark);">Informations</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Lieu</dt><dd class="font-medium text-right">{{ $event->location }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Ville</dt><dd class="font-medium">{{ $event->city ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Capacité</dt><dd class="font-medium">{{ $event->capacity ?? 'Illimité' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Prix</dt><dd class="font-medium" style="color:var(--rose);">{{ $event->is_paid ? number_format($event->price,0,',',' ').' '.$event->currency : 'Gratuit' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Statut</dt>
                <dd><span class="text-xs px-2 py-1 rounded-full font-medium" style="background:rgba(217,30,110,0.08);color:var(--rose);">{{ ucfirst($event->status) }}</span></dd>
                </div>
            </dl>
        </div>

        <form action="{{ route('admin.events.destroy', $event) }}" method="POST" onsubmit="return confirm('Supprimer cet événement ?')">
            @csrf @method('DELETE')
            <button type="submit" class="w-full text-sm py-2 rounded-xl font-medium text-red-400 hover:bg-red-50 border border-red-100 transition-colors">Supprimer l'événement</button>
        </form>
    </div>
</div>
@endsection
