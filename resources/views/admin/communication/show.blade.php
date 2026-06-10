@extends('layouts.admin')
@section('title', $campaign->title)
@section('page-title','Communication')
@section('page-subtitle', $campaign->title)

@section('header-actions')
@if(in_array($campaign->status, ['draft','scheduled']))
<form method="POST" action="{{ route('admin.communication.send', $campaign) }}"
      x-data @submit.prevent="if(confirm('Envoyer cette campagne maintenant ?')) $el.submit()">
    @csrf
    <button type="submit" class="btn-rose text-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
        Envoyer maintenant
    </button>
</form>
<a href="{{ route('admin.communication.edit', $campaign) }}" class="btn-gold text-sm">Modifier</a>
@elseif($campaign->status === 'sending')
<span class="inline-flex items-center gap-2 text-sm px-4 py-2 rounded-xl pulse-btn" style="background:#EFF6FF;color:#1D4ED8;">
    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
    Envoi en cours…
</span>
@endif
<a href="{{ route('admin.communication.index') }}" class="text-sm px-4 py-2 rounded-xl hover:bg-gray-100 transition-colors" style="color:var(--gray);">← Retour</a>
@endsection

@section('content')

{{-- Stats de la campagne --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
    <div class="admin-card !py-5 text-center">
        <p class="text-3xl font-bold mb-1" style="color:var(--dark);">{{ number_format($campaign->sent_count) }}</p>
        <p class="text-xs" style="color:var(--gray);">Emails envoyés</p>
    </div>
    <div class="admin-card !py-5 text-center">
        <p class="text-3xl font-bold mb-1" style="color:var(--rose);">{{ number_format($campaign->open_count) }}</p>
        <p class="text-xs" style="color:var(--gray);">Ouvertures totales</p>
    </div>
    <div class="admin-card !py-5 text-center">
        <p class="text-3xl font-bold mb-1" style="color:#3B82F6;">{{ number_format($uniqueOpens) }}</p>
        <p class="text-xs" style="color:var(--gray);">Ouvreurs uniques</p>
    </div>
    <div class="admin-card !py-5 text-center">
        <p class="text-3xl font-bold mb-1" style="color:{{ $campaign->failed_count > 0 ? '#DC2626' : '#D1D5DB' }};">{{ number_format($campaign->failed_count) }}</p>
        <p class="text-xs" style="color:var(--gray);">Échecs d'envoi</p>
    </div>
    <div class="admin-card !py-5 text-center">
        <p class="text-3xl font-bold mb-1" style="color:#10B981;">{{ $campaign->open_rate }}%</p>
        <p class="text-xs" style="color:var(--gray);">Taux d'ouverture</p>
        @if($campaign->sent_count > 0)
        <div class="mt-2 h-1.5 rounded-full overflow-hidden" style="background:#E5E7EB;">
            <div class="h-full rounded-full" style="background:#10B981;width:{{ min(100,$campaign->open_rate) }}%;"></div>
        </div>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Détails de la campagne --}}
    <div class="lg:col-span-1 space-y-4">
        <div class="admin-card space-y-4">
            <h3 class="text-sm font-bold" style="color:var(--dark);">Détails</h3>

            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-3">
                    <dt style="color:var(--gray);">Statut</dt>
                    <dd><span class="font-semibold text-white text-xs px-2.5 py-1 rounded-full" style="background:{{ $campaign->status_color }};">
                        {{ ['draft'=>'Brouillon','scheduled'=>'Programmée','sending'=>'En cours','sent'=>'Envoyée'][$campaign->status] ?? $campaign->status }}
                    </span></dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt style="color:var(--gray);">Type</dt>
                    <dd class="font-medium text-right" style="color:var(--dark);">{{ $campaign->type_label }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt style="color:var(--gray);">Cible</dt>
                    <dd class="font-medium text-right" style="color:var(--dark);">{{ $campaign->target_label }}</dd>
                </div>
                @if($campaign->scheduled_at)
                <div class="flex justify-between gap-3">
                    <dt style="color:var(--gray);">Programmée</dt>
                    <dd class="font-medium text-right" style="color:var(--dark);">{{ $campaign->scheduled_at->format('d/m/Y H:i') }}</dd>
                </div>
                @endif
                @if($campaign->sent_at)
                <div class="flex justify-between gap-3">
                    <dt style="color:var(--gray);">Envoyée le</dt>
                    <dd class="font-medium text-right" style="color:var(--dark);">{{ $campaign->sent_at->format('d/m/Y à H:i') }}</dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- Aperçu du message --}}
        <div class="admin-card">
            <h3 class="text-sm font-bold mb-3" style="color:var(--dark);">Aperçu du message</h3>
            <p class="text-xs font-semibold mb-1" style="color:var(--gray);">Objet :</p>
            <p class="text-sm font-medium mb-4" style="color:var(--dark);">{{ $campaign->subject }}</p>

            @if($campaign->image && in_array($campaign->type, ['text_image','text_image_cta']))
            <img src="{{ asset('storage/'.$campaign->image) }}" alt="" class="w-full rounded-xl object-cover mb-4" style="max-height:150px;">
            @endif

            <p class="text-sm leading-relaxed whitespace-pre-line" style="color:var(--gray);">{{ Str::limit($campaign->body, 300) }}</p>

            @if($campaign->cta_url && in_array($campaign->type, ['text_cta','text_image_cta']))
            <div class="mt-4">
                <span class="inline-block px-4 py-2 rounded-full text-xs font-bold text-white" style="background:var(--rose);">
                    {{ $campaign->cta_label ?: 'En savoir plus' }}
                </span>
            </div>
            @endif
        </div>
    </div>

    {{-- Table des destinataires --}}
    <div class="lg:col-span-2">
        <div class="admin-card overflow-hidden p-0">
            <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid var(--border);">
                <h3 class="text-sm font-bold" style="color:var(--dark);">Destinataires</h3>
                <span class="text-xs" style="color:var(--gray);">{{ $recipients->total() }} membre(s)</span>
            </div>

            @if($recipients->isEmpty())
            <div class="py-12 text-center">
                <p class="text-sm" style="color:var(--gray);">Aucun destinataire pour l'instant.</p>
            </div>
            @else
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:#FAFAFA;border-bottom:1px solid var(--border);">
                        <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wide" style="color:var(--gray);">Membre</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wide" style="color:var(--gray);">Envoyé</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wide" style="color:var(--gray);">Ouvert</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold uppercase tracking-wide" style="color:var(--gray);">Nb. vues</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="divide-color:var(--border);">
                @foreach($recipients as $r)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3">
                        <p class="font-semibold text-xs" style="color:var(--dark);">{{ $r->name }}</p>
                        <p class="text-xs" style="color:var(--gray);">{{ $r->email }}</p>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($r->sent_at)
                        <span class="text-xs" style="color:var(--gray);">{{ $r->sent_at->format('d/m H:i') }}</span>
                        @elseif($r->failed_at)
                        <span class="inline-flex items-center gap-1 text-xs font-semibold" style="color:#DC2626;" title="Échec d'envoi">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            Échec
                        </span>
                        @else
                        <span class="text-xs text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($r->opened_at)
                        <span class="inline-flex items-center gap-1 text-xs font-semibold" style="color:#10B981;">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            {{ $r->opened_at->format('d/m H:i') }}
                        </span>
                        @else
                        <span class="text-xs" style="color:#D1D5DB;">Non ouvert</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right">
                        <span class="text-sm font-bold" style="color:{{ $r->open_count > 0 ? 'var(--rose)' : '#D1D5DB' }};">
                            {{ $r->open_count }}
                        </span>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @if($recipients->hasPages())
            <div class="px-5 py-4" style="border-top:1px solid var(--border);">{{ $recipients->links() }}</div>
            @endif
            @endif
        </div>
    </div>

</div>
@endsection
