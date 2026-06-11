@extends('layouts.admin')
@section('title','Ventes & paiements')
@section('page-title','Ventes & paiements')
@section('page-subtitle','Suivi des paiements en ligne (événements & ebooks)')

@section('content')
@php
    $statusConfig = [
        'pending' => ['En attente', '#6B7280'],
        'processing' => ['En cours', '#3B82F6'],
        'completed' => ['Payé', '#059669'],
        'paid' => ['Payé', '#059669'],
        'failed' => ['Échoué', '#DC2626'],
        'cancelled' => ['Annulé', '#DC2626'],
        'expired' => ['Expiré', '#9CA3AF'],
        'refunded' => ['Remboursé', '#B45309'],
        'amount_mismatch' => ['Montant incohérent', '#DC2626'],
    ];
@endphp

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="admin-card !py-5">
        <p class="text-xs" style="color:var(--gray);">Total encaissé</p>
        <p class="text-2xl font-bold mt-1" style="color:var(--rose);">{{ number_format($stats['total'], 0, ',', ' ') }} <span class="text-sm">XOF</span></p>
        <p class="text-xs mt-1" style="color:var(--gray);">{{ $stats['count'] }} paiement(s)</p>
    </div>
    <div class="admin-card !py-5">
        <p class="text-xs" style="color:var(--gray);">Événements</p>
        <p class="text-2xl font-bold mt-1" style="color:var(--dark);">{{ number_format($stats['events'], 0, ',', ' ') }} <span class="text-sm">XOF</span></p>
    </div>
    <div class="admin-card !py-5">
        <p class="text-xs" style="color:var(--gray);">Ebooks</p>
        <p class="text-2xl font-bold mt-1" style="color:var(--gold-dark);">{{ number_format($stats['ebooks'], 0, ',', ' ') }} <span class="text-sm">XOF</span></p>
    </div>
    <div class="admin-card !py-5">
        <p class="text-xs" style="color:var(--gray);">Ebooks vendus</p>
        <p class="text-2xl font-bold mt-1" style="color:#7C3AED;">{{ $stats['ebooks_count'] }}</p>
    </div>
</div>

{{-- Filtres --}}
<form method="GET" class="flex flex-wrap gap-3 mb-5">
    <select name="type" class="form-input w-40" onchange="this.form.submit()">
        <option value="">Tous types</option>
        <option value="event" @selected(request('type')==='event')>Événements</option>
        <option value="ebook" @selected(request('type')==='ebook')>Ebooks</option>
    </select>
    <select name="status" class="form-input w-44" onchange="this.form.submit()">
        <option value="">Tous statuts</option>
        <option value="completed" @selected(request('status')==='completed')>Payé</option>
        <option value="pending" @selected(request('status')==='pending')>En attente</option>
        <option value="failed" @selected(request('status')==='failed')>Échoué</option>
        <option value="refunded" @selected(request('status')==='refunded')>Remboursé</option>
    </select>
    @if(request()->anyFilled(['type','status']))
    <a href="{{ route('admin.sales.index') }}" class="btn-gold text-sm px-4 py-2">✕ Réinitialiser</a>
    @endif
</form>

<div class="admin-card overflow-hidden p-0">
    <div class="overflow-x-auto">
    <table class="w-full text-sm min-w-[720px]">
        <thead>
            <tr style="background:#F8F7F9;">
                <th class="text-left px-5 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Date</th>
                <th class="text-left px-5 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Client</th>
                <th class="text-left px-5 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Objet</th>
                <th class="text-right px-5 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Montant</th>
                <th class="text-left px-5 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Statut</th>
                <th class="text-left px-5 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Référence</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($payments as $p)
            @php
                [$label, $color] = $statusConfig[$p->status] ?? [$p->status, '#6B7280'];
                $isEbook = str_contains((string) $p->payable_type, 'Ebook');
                $objet = $isEbook ? ('📚 '.($p->payable->title ?? 'Ebook supprimé')) : ('🎟️ '.($p->payable?->event->title ?? 'Événement'));
            @endphp
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 text-gray-400 text-xs whitespace-nowrap">{{ $p->created_at->format('d/m/Y H:i') }}</td>
                <td class="px-5 py-3">
                    <p class="font-medium" style="color:var(--dark);">{{ $p->customer_name ?? '—' }}</p>
                    <p class="text-xs text-gray-400">{{ $p->customer_email }}</p>
                </td>
                <td class="px-5 py-3" style="color:var(--dark);">{{ $objet }}</td>
                <td class="px-5 py-3 text-right font-bold whitespace-nowrap" style="color:var(--dark);">{{ number_format($p->amount, 0, ',', ' ') }} {{ $p->currency }}</td>
                <td class="px-5 py-3"><span class="text-xs px-2 py-1 rounded-full font-medium" style="background:{{ $color }}18;color:{{ $color }};">{{ $label }}</span></td>
                <td class="px-5 py-3 text-xs font-mono text-gray-400">{{ $p->provider_reference ?? $p->reference }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-12 text-gray-400">Aucun paiement{{ request()->anyFilled(['type','status']) ? ' pour ce filtre' : '' }}.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    @if($payments->hasPages())
    <div class="px-5 py-4 border-t border-gray-50">{{ $payments->links() }}</div>
    @endif
</div>
@endsection
