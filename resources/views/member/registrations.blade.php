@extends('layouts.member')
@section('title', 'Mes événements')

@section('content')

<h1 class="text-2xl font-bold mb-6" style="color:var(--dark);font-family:'Playfair Display',serif;">Mes événements</h1>

@if($registrations->isEmpty())
<div class="card p-10 text-center">
    <p class="font-semibold" style="color:var(--dark);">Aucune inscription pour l’instant</p>
    <p class="text-sm mt-1 mb-5" style="color:var(--gray);">Vos inscriptions aux événements FSL apparaîtront ici.</p>
    <a href="{{ route('events.index') }}" class="btn-rose text-sm">Voir les événements</a>
</div>
@else
<div class="space-y-3">
    @foreach($registrations as $registration)
    @php $event = $registration->event; @endphp
    <div class="card p-5 flex flex-wrap items-center justify-between gap-4">
        <div class="min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-wider" style="color:var(--gray);">
                {{ $event?->event_date->translatedFormat('d F Y à H:i') ?? 'Événement supprimé' }}
            </p>
            <p class="font-bold mt-0.5" style="color:var(--dark);">{{ $event?->title ?? '—' }}</p>
            @if($event?->location)
            <p class="text-xs mt-0.5" style="color:var(--gray);">{{ $event->location }}{{ $event->city ? ', '.$event->city : '' }}</p>
            @endif
        </div>

        <div class="flex items-center gap-3 flex-shrink-0">
            @php
                [$label, $bg, $fg] = match ($registration->status) {
                    'paid' => ['Confirmée', '#05966918', '#059669'],
                    'attended' => ['Présente', '#7C3AED18', '#7C3AED'],
                    'payment_sent' => ['Paiement en attente', '#B4530918', '#B45309'],
                    'cancelled' => ['Annulée', '#F3F4F6', '#9CA3AF'],
                    default => ['En attente', '#F3F4F6', '#6B7280'],
                };
            @endphp
            <span class="text-xs px-2.5 py-1 rounded-full font-medium" style="background:{{ $bg }};color:{{ $fg }};">{{ $label }}</span>

            @if($event)
            <a href="{{ route('events.show', $event->slug) }}" class="text-xs px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">Voir</a>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection
