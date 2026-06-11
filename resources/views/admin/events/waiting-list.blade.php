@extends('layouts.admin')
@section('title','Liste d\'attente — '.$event->title)
@section('page-title','Liste d\'attente')
@section('page-subtitle', $event->title.' — '.$waitingList->count().' personne(s)')
@section('header-actions')
<a href="{{ route('admin.events.show', $event) }}" class="text-sm px-5 py-2 rounded-full border border-gray-200 hover:bg-gray-50 font-medium">← Événement</a>
@endsection

@section('content')

@if($waitingList->isEmpty())
<div class="admin-card text-center py-16">
    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:var(--gold-pale);">
        <svg class="w-8 h-8" style="color:var(--gold);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
    </div>
    <p class="text-gray-400">Aucune personne sur la liste d'attente pour le moment.</p>
</div>
@else
<div class="admin-card overflow-hidden p-0">
    <div class="overflow-x-auto">
    <table class="w-full text-sm min-w-[640px]">
        <thead>
            <tr style="background:#F8F7F9;">
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Personne</th>
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Email</th>
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Téléphone</th>
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Inscrit le</th>
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Notifié</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($waitingList as $entry)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 font-medium" style="color:var(--dark);">{{ $entry->full_name }}</td>
                <td class="px-6 py-4 text-gray-600">{{ $entry->email }}</td>
                <td class="px-6 py-4 text-gray-400 text-xs">{{ $entry->phone ?? '—' }}</td>
                <td class="px-6 py-4 text-gray-400 text-xs">{{ $entry->created_at->format('d/m/Y H:i') }}</td>
                <td class="px-6 py-4">
                    @if($entry->notified)
                    <span class="text-xs px-2 py-1 rounded-full font-medium" style="background:#05966918;color:#059669;">✓ Oui</span>
                    @else
                    <span class="text-xs px-2 py-1 rounded-full font-medium bg-gray-100 text-gray-400">Non</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>
@endif
@endsection
