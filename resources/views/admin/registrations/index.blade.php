@extends('layouts.admin')
@section('title','Inscriptions — '.$event->title)
@section('page-title','Inscriptions')
@section('page-subtitle', $event->title.' — '.$registrations->count().' inscrit(s)')
@section('header-actions')
<a href="{{ route('admin.scanner.index', $event) }}" class="btn-rose text-sm px-5 py-2">📷 Scanner QR</a>
<a href="{{ route('admin.events.show', $event) }}" class="text-sm px-5 py-2 rounded-full border border-gray-200 hover:bg-gray-50 font-medium">← Événement</a>
@endsection

@section('content')

{{-- Status summary --}}
<div class="grid grid-cols-5 gap-3 mb-6">
    @foreach([
        ['En attente', $registrations->where('status','pending')->count(), '#6B7280'],
        ['Lien envoyé', $registrations->where('status','payment_sent')->count(), '#C9A84C'],
        ['Payé', $registrations->where('status','paid')->count(), '#059669'],
        ['Présent', $registrations->where('status','attended')->count(), '#7C3AED'],
        ['Annulé', $registrations->where('status','cancelled')->count(), '#DC2626'],
    ] as [$label, $count, $color])
    <div class="admin-card py-3 px-4 text-center">
        <p class="text-2xl font-bold" style="color:{{ $color }};">{{ $count }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $label }}</p>
    </div>
    @endforeach
</div>

<div class="admin-card overflow-hidden p-0">
    <table class="w-full text-sm">
        <thead>
            <tr style="background:#F8F7F9;">
                <th class="text-left px-5 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Participant</th>
                <th class="text-left px-5 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Contact</th>
                <th class="text-left px-5 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Statut</th>
                <th class="text-left px-5 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Inscrit le</th>
                <th class="text-right px-5 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($registrations as $reg)
            @php
                $statusConfig = [
                    'pending'      => ['En attente', '#6B7280'],
                    'payment_sent' => ['Lien envoyé', '#C9A84C'],
                    'paid'         => ['Payé ✓', '#059669'],
                    'attended'     => ['Présent ✓', '#7C3AED'],
                    'cancelled'    => ['Annulé', '#DC2626'],
                ];
                [$statusLabel, $statusColor] = $statusConfig[$reg->status] ?? ['—','#999'];
            @endphp
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-4">
                    <p class="font-medium" style="color:var(--dark);">{{ $reg->full_name }}</p>
                </td>
                <td class="px-5 py-4">
                    <p class="text-gray-600">{{ $reg->email }}</p>
                    @if($reg->phone)<p class="text-xs text-gray-400">{{ $reg->phone }}</p>@endif
                </td>
                <td class="px-5 py-4">
                    <span class="text-xs px-2 py-1 rounded-full font-medium" style="background:{{ $statusColor }}18;color:{{ $statusColor }};">{{ $statusLabel }}</span>
                </td>
                <td class="px-5 py-4 text-gray-400 text-xs">{{ $reg->created_at->format('d/m/Y H:i') }}</td>
                <td class="px-5 py-4">
                    <div class="flex items-center justify-end gap-2">
                        @if($reg->status === 'pending' && $event->is_paid && $event->payment_link)
                        <form action="{{ route('admin.registrations.send-payment', $reg) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs px-3 py-1.5 rounded-lg font-medium text-white transition-colors" style="background:var(--gold);" onclick="return confirm('Envoyer le lien de paiement à {{ $reg->email }} ?')">
                                ✉ Lien paiement
                            </button>
                        </form>
                        @endif

                        @if(in_array($reg->status, ['pending','payment_sent']))
                        <form action="{{ route('admin.registrations.confirm-payment', $reg) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs px-3 py-1.5 rounded-lg font-medium text-white transition-colors" style="background:#059669;" onclick="return confirm('Confirmer le paiement et envoyer le QR code ?')">
                                ✓ Confirmer paiement
                            </button>
                        </form>
                        @endif

                        @if($reg->qr_code_path)
                        <a href="{{ asset('storage/'.$reg->qr_code_path) }}" download class="text-xs px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">⬇ QR</a>
                        @endif

                        @if(!in_array($reg->status, ['cancelled','attended']))
                        <form action="{{ route('admin.registrations.cancel', $reg) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs px-3 py-1.5 rounded-lg text-red-400 hover:bg-red-50 transition-colors" onclick="return confirm('Annuler cette inscription ?')">Annuler</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center py-12 text-gray-400">Aucune inscription pour cet événement</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
