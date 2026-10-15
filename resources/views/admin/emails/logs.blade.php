@extends('layouts.admin')
@section('title','Journal des envois')
@section('page-title','Journal des envois')
@section('page-subtitle', $health['total'].' email(s) tracé(s)')
@section('header-actions')
<a href="{{ route('admin.emails.index') }}" class="text-sm px-5 py-2 rounded-full border border-gray-200 hover:bg-gray-50 font-medium">Tester un envoi</a>
@endsection

@section('content')
@php
    $lastSent = $health['last_sent'];
    // Au-delà de 48 h sans le moindre envoi, c'est soit le calme plat, soit
    // une panne. La nuance est pour l'œil humain ; l'alerte est pour l'attirer.
    $silent = ! $lastSent || $lastSent->lt(now()->subDays(2));
@endphp

{{-- L'état de santé, en trois chiffres --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
    <div class="admin-card py-4 px-5">
        <p class="text-xs mb-1" style="color:var(--gray);">Dernier envoi</p>
        <p class="text-lg font-bold" style="color:{{ $silent ? '#DC2626' : '#059669' }};">
            {{ $lastSent ? $lastSent->diffForHumans() : 'Jamais' }}
        </p>
        @if($lastSent)<p class="text-xs" style="color:var(--gray);">{{ $lastSent->format('d/m/Y H:i') }}</p>@endif
    </div>
    <div class="admin-card py-4 px-5">
        <p class="text-xs mb-1" style="color:var(--gray);">Envoyés (24 h)</p>
        <p class="text-2xl font-bold" style="color:var(--dark);">{{ $health['sent_24h'] }}</p>
    </div>
    <div class="admin-card py-4 px-5">
        <p class="text-xs mb-1" style="color:var(--gray);">En échec (24 h)</p>
        <p class="text-2xl font-bold" style="color:{{ $health['failed_24h'] > 0 ? '#DC2626' : '#059669' }};">{{ $health['failed_24h'] }}</p>
    </div>
</div>

@if($silent && $health['total'] > 0)
<div class="rounded-xl px-4 py-3 mb-6 text-sm" style="background:#FEF2F2;border:1px solid #FCA5A5;color:#991B1B;">
    Aucun email envoyé depuis plus de 48 h. Si l'association est active, vérifiez le cron
    <code class="font-mono">schedule:run</code> et la configuration SMTP.
</div>
@endif

@if($health['total'] === 0)
<div class="rounded-xl px-4 py-3 mb-6 text-sm" style="background:#EFF6FF;color:#1D4ED8;">
    Le journal est vide : il n'enregistre que les envois postérieurs à sa mise en service.
    Envoyez-vous un email depuis <a href="{{ route('admin.emails.index') }}" class="underline">Test des emails</a> pour vérifier qu'il fonctionne.
</div>
@endif

{{-- Par type : c'est l'absence qu'on cherche --}}
<div class="admin-card p-5 mb-6">
    <h3 class="font-semibold text-sm mb-1" style="color:var(--dark);">Par type d'email</h3>
    <p class="text-xs mb-4" style="color:var(--gray);">
        Sur 90 jours. Un type qui ne part plus se repère à sa date, pas à son compteur.
    </p>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left" style="color:var(--gray);">
                    <th class="font-medium pb-2 pr-4">Type</th>
                    <th class="font-medium pb-2 pr-4 text-right">Envoyés</th>
                    <th class="font-medium pb-2 pr-4 text-right">Échecs</th>
                    <th class="font-medium pb-2 text-right">Dernier</th>
                </tr>
            </thead>
            <tbody>
                @foreach($byType as $row)
                <tr style="border-top:1px solid var(--border);">
                    <td class="py-2.5 pr-4">
                        <span style="color:var(--dark);">{{ $row['label'] }}</span>
                        <span class="text-xs block" style="color:var(--gray);">{{ $row['group'] }}</span>
                    </td>
                    <td class="py-2.5 pr-4 text-right" style="color:var(--dark);">{{ $row['sent'] }}</td>
                    <td class="py-2.5 pr-4 text-right" style="color:{{ $row['failed'] > 0 ? '#DC2626' : 'var(--gray)' }};">{{ $row['failed'] }}</td>
                    <td class="py-2.5 text-right text-xs" style="color:var(--gray);">
                        {{ $row['last_sent'] ? $row['last_sent']->diffForHumans() : '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Détail --}}
<div class="admin-card p-5">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h3 class="font-semibold text-sm" style="color:var(--dark);">Derniers envois</h3>

        <form method="GET" class="flex flex-wrap items-center gap-2">
            <select name="statut" class="form-input text-sm py-1.5" onchange="this.form.submit()">
                <option value="">Tous les statuts</option>
                <option value="sent" @selected(request('statut')==='sent')>Envoyés</option>
                <option value="failed" @selected(request('statut')==='failed')>Échecs</option>
                <option value="sans-confirmation" @selected(request('statut')==='sans-confirmation')>Sans confirmation</option>
            </select>
            <label class="text-xs flex items-center gap-1.5" style="color:var(--gray);">
                <input type="checkbox" name="tests" value="1" @checked(request()->boolean('tests')) onchange="this.form.submit()">
                Inclure les tests
            </label>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left" style="color:var(--gray);">
                    <th class="font-medium pb-2 pr-4">Type</th>
                    <th class="font-medium pb-2 pr-4">Destinataire</th>
                    <th class="font-medium pb-2 pr-4">Statut</th>
                    <th class="font-medium pb-2">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                @php
                    $color = match($log->statusLabel()) {
                        'Envoyé' => '#059669',
                        'Échec' => '#DC2626',
                        'Sans confirmation' => '#C9A84C',
                        default => '#6B7280',
                    };
                @endphp
                <tr style="border-top:1px solid var(--border);">
                    <td class="py-2.5 pr-4">
                        <span style="color:var(--dark);">{{ $log->typeLabel() }}</span>
                        @if($log->is_test)<span class="text-[10px] px-1.5 py-0.5 rounded ml-1" style="background:#EFF6FF;color:#1D4ED8;">test</span>@endif
                        @if($log->subject)<span class="text-xs block" style="color:var(--gray);">{{ Str::limit($log->subject, 70) }}</span>@endif
                    </td>
                    <td class="py-2.5 pr-4 text-xs" style="color:var(--gray);">
                        {{ $log->recipient }}
                        @if($log->recipients_count > 1)<span class="block">+{{ $log->recipients_count - 1 }} autre(s)</span>@endif
                    </td>
                    <td class="py-2.5 pr-4">
                        <span class="text-xs px-2 py-1 rounded-full font-medium" style="background:{{ $color }}18;color:{{ $color }};">{{ $log->statusLabel() }}</span>
                        @if($log->error)<span class="text-[11px] block mt-0.5 font-mono" style="color:#991B1B;">{{ Str::limit($log->error, 90) }}</span>@endif
                    </td>
                    <td class="py-2.5 text-xs" style="color:var(--gray);">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-10" style="color:var(--gray);">Aucun envoi pour ce filtre.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
    <div class="pt-4 mt-2" style="border-top:1px solid var(--border);">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
