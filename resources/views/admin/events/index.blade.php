@extends('layouts.admin')
@section('title','Événements')
@section('page-title','Événements')
@section('page-subtitle', $events->total().' événement(s)')
@section('header-actions')
<a href="{{ route('admin.events.create') }}" class="btn-rose text-sm px-5 py-2">+ Créer un événement</a>
@endsection

@section('content')

{{-- Filters --}}
<form method="GET" class="flex flex-wrap gap-3 mb-6">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Titre, lieu..." class="form-input flex-1 min-w-[180px] sm:w-64 sm:flex-none">
    <div class="flex gap-3 flex-wrap">
        <select name="status" class="form-input w-36">
            <option value="">Tous statuts</option>
            <option value="published" @selected(request('status')=='published')>Publié</option>
            <option value="draft"     @selected(request('status')=='draft')>Brouillon</option>
            <option value="cancelled" @selected(request('status')=='cancelled')>Annulé</option>
            <option value="completed" @selected(request('status')=='completed')>Terminé</option>
        </select>
        <button type="submit" class="btn-rose text-sm px-4 py-2">Filtrer</button>
        @if(request()->anyFilled(['search','status']))
        <a href="{{ route('admin.events.index') }}" class="btn-gold text-sm px-4 py-2">✕</a>
        @endif
    </div>
</form>

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
    <table class="w-full text-sm min-w-[700px]">
        <thead>
            <tr style="background:#F8F7F9;">
                @php
                    $sortBy  = request('sort', 'created_at');
                    $sortDir = request('dir', 'desc');
                    $nextDir = $sortDir === 'asc' ? 'desc' : 'asc';
                    $sortIcon = fn($col) => $sortBy === $col ? ($sortDir === 'asc' ? ' ↑' : ' ↓') : '';
                @endphp
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'title','dir'=>$sortBy==='title'?$nextDir:'asc']) }}" class="hover:text-gray-800">Événement{{ $sortIcon('title') }}</a>
                </th>
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'event_date','dir'=>$sortBy==='event_date'?$nextDir:'asc']) }}" class="hover:text-gray-800">Date{{ $sortIcon('event_date') }}</a>
                </th>
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Lieu</th>
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Prix</th>
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'registrations_count','dir'=>$sortBy==='registrations_count'?$nextDir:'desc']) }}" class="hover:text-gray-800">Inscrits{{ $sortIcon('registrations_count') }}</a>
                </th>
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Statut</th>
                <th class="text-right px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($events as $event)
            @php $colors = ['published'=>'#059669','draft'=>'#6B7280','cancelled'=>'#DC2626','completed'=>'#7C3AED']; @endphp
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        @if($event->image)
                        <img src="{{ asset('storage/'.$event->image) }}" alt="{{ $event->title }}"
                             class="w-14 h-10 rounded-lg object-cover flex-shrink-0 shadow-sm" loading="lazy">
                        @else
                        <div class="w-14 h-10 rounded-lg flex-shrink-0 flex items-center justify-center" style="background:var(--rose-pale);">
                            <svg class="w-5 h-5" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        @endif
                        <div class="min-w-0">
                            <p class="font-medium truncate" style="color:var(--dark);">{{ $event->title }}</p>
                            <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $event->city ?? $event->location }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-gray-600">
                    {{ $event->event_date->format('d M Y') }}<br>
                    <span class="text-xs">{{ $event->event_date->format('H:i') }}</span>
                </td>
                <td class="px-6 py-4 text-gray-600 max-w-[160px] truncate">{{ $event->location }}</td>
                <td class="px-6 py-4">
                    @if($event->is_paid)
                    <span style="color:var(--rose);">{{ number_format($event->price,0,',',' ') }} {{ $event->currency }}</span>
                    @else
                    <span class="text-green-600">Gratuit</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold" style="color:var(--dark);">{{ $event->activeRegistrationsCount() }}</span>
                        @if($event->capacity)
                        <div class="w-16 h-1.5 rounded-full bg-gray-100 overflow-hidden">
                            <div class="h-full rounded-full" style="width:{{ min(100, intval($event->activeRegistrationsCount() / $event->capacity * 100)) }}%;background:var(--rose);"></div>
                        </div>
                        <span class="text-xs text-gray-400">/ {{ $event->capacity }}</span>
                        @endif
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="text-xs px-2 py-1 rounded-full font-medium" style="background:{{ $colors[$event->status] ?? '#6B7280' }}18;color:{{ $colors[$event->status] ?? '#6B7280' }};">{{ ucfirst($event->status) }}</span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-1.5">
                        <a href="{{ route('events.show', $event->slug) }}" target="_blank" title="Voir sur le site public" class="text-xs px-2.5 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600">🌐</a>
                        <a href="{{ route('admin.events.show', $event) }}" class="text-xs px-2.5 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">Voir</a>
                        <a href="{{ route('admin.registrations.index', $event) }}" class="text-xs px-2.5 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">Inscrits</a>
                        <a href="{{ route('admin.events.edit', $event) }}" class="text-xs px-2.5 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">Éditer</a>
                        <form x-ref="dup_{{ $event->id }}" action="{{ route('admin.events.duplicate', $event) }}" method="POST">@csrf</form>
                        <button type="button"
                            @click="confirm = { title: 'Dupliquer cet événement ?', body: 'Une copie brouillon sera créée.' }; confirmAction = 'dup_{{ $event->id }}'"
                            title="Dupliquer" class="text-xs px-2.5 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">⎘</button>
                        <form x-ref="del_{{ $event->id }}" action="{{ route('admin.events.destroy', $event) }}" method="POST">@csrf @method('DELETE')</form>
                        <button type="button"
                            @click="confirm = { title: 'Supprimer cet événement ?', body: 'L\'événement et ses inscriptions seront supprimés (archivés).' }; confirmAction = 'del_{{ $event->id }}'"
                            title="Supprimer" class="text-xs px-2.5 py-1.5 rounded-lg text-red-500 bg-red-50 hover:bg-red-100">Supprimer</button>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-12 text-gray-400">Aucun événement. <a href="{{ route('admin.events.create') }}" style="color:var(--rose);">En créer un</a></td></tr>
            @endforelse
        </tbody>
    </table>
    </div>{{-- /overflow-x-auto --}}
    @if($events->hasPages())<div class="px-6 py-4 border-t border-gray-50">{{ $events->links() }}</div>@endif
</div>
@endsection
