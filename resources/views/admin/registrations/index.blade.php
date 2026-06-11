@extends('layouts.admin')
@section('title','Inscriptions — '.$event->title)
@section('page-title','Inscriptions')
@section('page-subtitle', $event->title.' — '.$registrations->total().' inscrit(s)')
@section('header-actions')
<a href="{{ route('admin.registrations.export-csv', $event) }}" class="text-sm px-5 py-2 rounded-full border border-gray-200 hover:bg-gray-50 font-medium">⬇ Exporter CSV</a>
<a href="{{ route('admin.scanner.index', $event) }}" class="btn-rose text-sm px-5 py-2">📷 Scanner QR</a>
<a href="{{ route('admin.events.show', $event) }}" class="text-sm px-5 py-2 rounded-full border border-gray-200 hover:bg-gray-50 font-medium">← Événement</a>
@endsection

@section('content')

{{-- Info paiement en ligne --}}
@if($event->is_paid)
<div class="rounded-xl px-4 py-3 mb-5 text-sm flex items-start gap-2" style="background:#EFF6FF;color:#1D4ED8;">
    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
    <span>Le paiement se fait <strong>en ligne (GeniusPay)</strong> : les inscrites paient directement et reçoivent leur reçu + QR automatiquement. Les boutons « manuel » ci-dessous ne servent qu'en secours.</span>
</div>
@endif

{{-- Revenue banner (paid events only) --}}
@if($event->is_paid && $totalRevenue > 0)
<div class="rounded-2xl p-4 mb-5 flex items-center justify-between" style="background:linear-gradient(135deg,rgba(201,168,76,0.12) 0%,rgba(201,168,76,0.04) 100%);border:1px solid rgba(201,168,76,0.3);">
    <div>
        <p class="text-xs font-semibold uppercase tracking-wide mb-0.5" style="color:var(--gold);">Revenus confirmés</p>
        <p class="text-2xl font-bold" style="color:var(--dark);">{{ number_format($totalRevenue, 0, ',', ' ') }} {{ $event->currency }}</p>
    </div>
    <div class="text-right">
        <p class="text-xs" style="color:var(--gray);">{{ $totals['paid'] }} paiement(s) × {{ number_format($event->price, 0, ',', ' ') }} {{ $event->currency }}</p>
        @if($totals['attended'] > 0)
        <p class="text-xs mt-0.5" style="color:var(--gray);">{{ $totals['attended'] }} présent(s) confirmé(s)</p>
        @endif
    </div>
</div>
@endif

{{-- Status filter cards --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
    @foreach([
        ['pending',      'En attente',  $totals['pending'],      '#6B7280'],
        ['payment_sent', 'Lien envoyé', $totals['payment_sent'], '#C9A84C'],
        ['paid',         'Payé',        $totals['paid'],         '#059669'],
        ['attended',     'Présent',     $totals['attended'],     '#7C3AED'],
        ['cancelled',    'Annulé',      $totals['cancelled'],    '#DC2626'],
    ] as [$statusKey, $label, $count, $color])
    @php $isActive = request('status') === $statusKey; @endphp
    <a href="{{ route('admin.registrations.index', [$event, 'status' => $statusKey]) }}"
       class="admin-card py-3 px-4 text-center transition-all cursor-pointer hover:shadow-md {{ $isActive ? 'ring-2' : '' }}"
       style="{{ $isActive ? 'ring-color:'.$color.';border-color:'.$color.'55;' : '' }}">
        <p class="text-2xl font-bold" style="color:{{ $color }};">{{ $count }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $label }}</p>
        @if($isActive)<p class="text-xs font-medium mt-1" style="color:{{ $color }};">✓ Filtré</p>@endif
    </a>
    @endforeach
</div>

@if(request('status'))
<div class="flex items-center gap-2 mb-4">
    <span class="text-sm text-gray-500">Filtre actif :</span>
    <span class="text-sm font-medium px-3 py-1 rounded-full" style="background:var(--rose-pale);color:var(--rose);">{{ request('status') }}</span>
    <a href="{{ route('admin.registrations.index', $event) }}" class="text-xs text-gray-400 hover:text-gray-600 underline">Tout afficher</a>
</div>
@endif

<div class="admin-card overflow-hidden p-0" x-data="{ confirm: null, confirmAction: '' }">

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

    <div class="overflow-x-auto">
    <table class="w-full text-sm min-w-[600px]">
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
                    @if($reg->paid_at)<p class="text-xs text-gray-400 mt-0.5">{{ $reg->paid_at->format('d/m/Y') }}</p>@endif
                    @if($reg->latestPayment)
                    <p class="text-[10px] text-gray-400 mt-0.5 font-mono" title="Référence du paiement en ligne">{{ $reg->latestPayment->provider_reference ?? $reg->latestPayment->reference }}</p>
                    @endif
                </td>
                <td class="px-5 py-4 text-gray-400 text-xs">{{ $reg->created_at->format('d/m/Y H:i') }}</td>
                <td class="px-5 py-4">
                    <div class="flex items-center justify-end gap-2">

                        {{-- Payment link: show for pending AND payment_sent --}}
                        @if(in_array($reg->status, ['pending','payment_sent']) && $event->is_paid && $event->payment_link)
                        <form x-ref="payment_{{ $reg->id }}" action="{{ route('admin.registrations.send-payment', $reg) }}" method="POST">@csrf</form>
                        <button type="button"
                            @click="confirm = { title: 'Envoyer le lien de paiement ?', body: 'Un email sera envoyé à {{ $reg->email }}.' }; confirmAction = 'payment_{{ $reg->id }}'"
                            class="text-xs px-3 py-1.5 rounded-lg font-medium text-white transition-colors" style="background:var(--gold);">
                            {{ $reg->status === 'payment_sent' ? '↻ Renvoyer' : '✉ Lien' }}
                        </button>
                        @endif

                        {{-- Confirm payment --}}
                        @if(in_array($reg->status, ['pending','payment_sent']))
                        <form x-ref="confirm_{{ $reg->id }}" action="{{ route('admin.registrations.confirm-payment', $reg) }}" method="POST">@csrf</form>
                        <button type="button"
                            @click="confirm = { title: 'Confirmer le paiement manuellement ?', body: 'À utiliser seulement si le paiement en ligne n\'a pas abouti. Le QR sera généré et envoyé à {{ $reg->email }}.' }; confirmAction = 'confirm_{{ $reg->id }}'"
                            class="text-xs px-3 py-1.5 rounded-lg font-medium" style="background:#ECFDF5;color:#059669;border:1px solid #A7F3D0;">
                            ✓ Confirmer (manuel)
                        </button>
                        @endif

                        @if($reg->qr_code_path)
                        <a href="{{ route('admin.registrations.qr', $reg) }}" class="text-xs px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">⬇ QR</a>
                        @endif

                        @if(!in_array($reg->status, ['cancelled','attended']))
                        <form x-ref="cancel_{{ $reg->id }}" action="{{ route('admin.registrations.cancel', $reg) }}" method="POST">@csrf</form>
                        <button type="button"
                            @click="confirm = { title: 'Annuler cette inscription ?', body: '{{ $reg->full_name }} sera notifié(e) de l\'annulation.' }; confirmAction = 'cancel_{{ $reg->id }}'"
                            class="text-xs px-3 py-1.5 rounded-lg text-red-400 hover:bg-red-50">Annuler</button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center py-12 text-gray-400">Aucune inscription{{ request('status') ? ' pour ce statut' : ' pour cet événement' }}</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>{{-- /overflow-x-auto --}}

    @if($registrations->hasPages())
    <div class="px-5 py-4 border-t border-gray-50">{{ $registrations->links() }}</div>
    @endif
</div>
@endsection
