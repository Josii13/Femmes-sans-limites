@extends('layouts.admin')
@section('title','Communication')
@section('page-title','Communication')
@section('page-subtitle','Campagnes d\'emailing')

@section('header-actions')
<a href="{{ route('admin.communication.create') }}" class="btn-rose text-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Nouvelle campagne
</a>
@endsection

@section('content')

{{-- Stats globales --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @php
    $cards = [
        ['Ce mois — campagnes', $statsMonth->total ?? 0, 'var(--rose)','M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
        ['Ce mois — emails',    number_format($statsMonth->emails ?? 0), 'var(--gold)','M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
        ['Cette année — emails',number_format($statsYear->emails ?? 0), '#3B82F6','M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
        ['Taux ouverture moyen',
            ($statsYear->emails > 0 ? round(($statsYear->opens / $statsYear->emails) * 100, 1) : 0).'%',
            '#10B981','M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'],
    ];
    @endphp
    @foreach($cards as [$label,$val,$color,$path])
    <div class="admin-card !py-5 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:{{ $color }}18;">
            <svg class="w-5 h-5" style="color:{{ $color }};" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $path }}"/></svg>
        </div>
        <div>
            <p class="text-xl font-bold" style="color:var(--dark);">{{ $val }}</p>
            <p class="text-xs" style="color:var(--gray);">{{ $label }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- Graphe mensuel (12 mois) --}}
@if($monthly->isNotEmpty())
<div class="admin-card mb-8">
    <h3 class="text-sm font-bold mb-5" style="color:var(--dark);">Emails envoyés — 12 derniers mois</h3>
    @php $maxEmails = $monthly->max('emails') ?: 1; @endphp
    <div class="flex items-end gap-2 h-24">
        @foreach($monthly as $m)
        <div class="flex-1 flex flex-col items-center gap-1">
            <div class="w-full rounded-t-lg transition-all duration-500"
                 style="height:{{ max(4, round(($m->emails / $maxEmails) * 80)) }}px;background:var(--rose);opacity:0.85;"
                 title="{{ $m->emails }} emails"></div>
            <p class="text-[9px] text-center" style="color:var(--gray);">
                {{ \Carbon\Carbon::createFromDate($m->y, $m->m, 1)->translatedFormat('M') }}
            </p>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Liste des campagnes --}}
<div class="admin-card overflow-hidden p-0">
    <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid var(--border);">
        <h3 class="text-sm font-bold" style="color:var(--dark);">Toutes les campagnes</h3>
        <span class="text-xs" style="color:var(--gray);">{{ $campaigns->total() }} campagne(s)</span>
    </div>

    @if($campaigns->isEmpty())
    <div class="py-16 text-center">
        <svg class="w-10 h-10 mx-auto mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        <p class="text-sm" style="color:var(--gray);">Aucune campagne. Créez votre première communication.</p>
    </div>
    @else
    <table class="w-full text-sm">
        <thead>
            <tr style="background:#FAFAFA;border-bottom:1px solid var(--border);">
                <th class="text-left px-6 py-3 text-xs font-semibold uppercase tracking-wide" style="color:var(--gray);">Campagne</th>
                <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide" style="color:var(--gray);">Cible</th>
                <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide" style="color:var(--gray);">Statut</th>
                <th class="text-right px-4 py-3 text-xs font-semibold uppercase tracking-wide" style="color:var(--gray);">Envoyés</th>
                <th class="text-right px-4 py-3 text-xs font-semibold uppercase tracking-wide" style="color:var(--gray);">Ouvertures</th>
                <th class="text-right px-4 py-3 text-xs font-semibold uppercase tracking-wide" style="color:var(--gray);">Taux</th>
                <th class="text-right px-6 py-3 text-xs font-semibold uppercase tracking-wide" style="color:var(--gray);">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y" style="divide-color:var(--border);">
        @foreach($campaigns as $c)
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4">
                <p class="font-semibold text-sm" style="color:var(--dark);">{{ $c->title }}</p>
                <p class="text-xs mt-0.5" style="color:var(--gray);">
                    {{ $c->type_label }}
                    @if($c->scheduled_at && $c->status === 'scheduled')
                    · Prog. {{ $c->scheduled_at->format('d/m/Y H:i') }}
                    @elseif($c->sent_at)
                    · {{ $c->sent_at->diffForHumans() }}
                    @else
                    · {{ $c->created_at->format('d/m/Y') }}
                    @endif
                </p>
            </td>
            <td class="px-4 py-4">
                <span class="text-xs font-medium px-2 py-1 rounded-full" style="background:var(--rose-pale);color:var(--rose);">
                    {{ $c->target_label }}
                </span>
            </td>
            <td class="px-4 py-4">
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full text-white"
                      style="background:{{ $c->status_color }};">
                    @if($c->status === 'sent') ✓
                    @elseif($c->status === 'scheduled') ⏰
                    @elseif($c->status === 'sending') ↗
                    @else ✎
                    @endif
                    {{ ['draft'=>'Brouillon','scheduled'=>'Programmée','sending'=>'En cours','sent'=>'Envoyée'][$c->status] ?? $c->status }}
                </span>
            </td>
            <td class="px-4 py-4 text-right font-mono text-sm" style="color:var(--dark);">
                {{ number_format($c->sent_count) }}
            </td>
            <td class="px-4 py-4 text-right font-mono text-sm" style="color:var(--dark);">
                {{ number_format($c->open_count) }}
            </td>
            <td class="px-4 py-4 text-right">
                @if($c->sent_count > 0)
                <div class="flex items-center justify-end gap-2">
                    <div class="w-14 h-1.5 rounded-full overflow-hidden" style="background:#EEE;">
                        <div class="h-full rounded-full" style="background:var(--rose);width:{{ min(100,$c->open_rate) }}%;"></div>
                    </div>
                    <span class="text-xs font-bold" style="color:var(--rose);">{{ $c->open_rate }}%</span>
                </div>
                @else
                <span class="text-xs" style="color:var(--gray);">—</span>
                @endif
            </td>
            <td class="px-6 py-4">
                <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('admin.communication.show', $c) }}" class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors" title="Voir">
                        <svg class="w-4 h-4" style="color:var(--gray);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </a>
                    @if($c->status !== 'sent')
                    <a href="{{ route('admin.communication.edit', $c) }}" class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors" title="Modifier">
                        <svg class="w-4 h-4" style="color:var(--gray);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    @endif
                    <form method="POST" action="{{ route('admin.communication.destroy', $c) }}" x-data @submit.prevent="if(confirm('Supprimer cette campagne ?')) $el.submit()">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 transition-colors" title="Supprimer">
                            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>

    @if($campaigns->hasPages())
    <div class="px-6 py-4" style="border-top:1px solid var(--border);">
        {{ $campaigns->links() }}
    </div>
    @endif
    @endif
</div>

@endsection
