@extends('layouts.admin')
@section('title','Journal d\'activité')
@section('page-title','Journal d\'activité')
@section('page-subtitle', 'Audit des actions effectuées dans le back-office')

@section('content')

<div class="admin-card overflow-hidden p-0">
    <div class="overflow-x-auto">
    <table class="w-full text-sm min-w-[640px]">
        <thead>
            <tr style="background:#F8F7F9;">
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Action</th>
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Sujet</th>
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Admin</th>
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">IP</th>
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Date</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($logs as $log)
            @php
                $actionColors = [
                    'member.created'    => ['#059669', 'Membre créé'],
                    'member.activated'  => ['#7C3AED', 'Membre activé'],
                    'member.deleted'    => ['#DC2626', 'Membre supprimé'],
                    'payment.confirmed' => ['#C9A84C', 'Paiement confirmé'],
                ];
                [$color, $label] = $actionColors[$log->action] ?? ['#6B7280', ucwords(str_replace('.', ' ', $log->action))];
            @endphp
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium" style="background:{{ $color }}18;color:{{ $color }};">{{ $label }}</span>
                </td>
                <td class="px-6 py-4 text-gray-600">
                    @if($log->subject_label)
                    <p class="font-medium" style="color:var(--dark);">{{ $log->subject_label }}</p>
                    @endif
                    @if($log->subject_type)<p class="text-xs text-gray-400">{{ $log->subject_type }} #{{ $log->subject_id }}</p>@endif
                </td>
                <td class="px-6 py-4 text-gray-600">{{ $log->user?->name ?? 'Système' }}</td>
                <td class="px-6 py-4 text-gray-400 text-xs font-mono">{{ $log->ip ?? '—' }}</td>
                <td class="px-6 py-4 text-gray-400 text-xs">{{ $log->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center py-12 text-gray-400">Aucune activité enregistrée pour le moment.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    @if($logs->hasPages())
    <div class="px-6 py-4 border-t border-gray-50">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
