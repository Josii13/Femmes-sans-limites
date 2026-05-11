@extends('layouts.admin')
@section('title','Événements')
@section('page-title','Événements')
@section('page-subtitle', $events->total().' événement(s)')
@section('header-actions')
<a href="{{ route('admin.events.create') }}" class="btn-rose text-sm px-5 py-2">+ Créer un événement</a>
@endsection

@section('content')
<div class="admin-card overflow-hidden p-0">
    <table class="w-full text-sm">
        <thead>
            <tr style="background:#F8F7F9;">
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Événement</th>
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Date</th>
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Lieu</th>
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Prix</th>
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Inscrits</th>
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Statut</th>
                <th class="text-right px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($events as $event)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                    <p class="font-medium" style="color:var(--dark);">{{ $event->title }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $event->slug }}</p>
                </td>
                <td class="px-6 py-4 text-gray-600">{{ $event->event_date->format('d M Y') }}<br><span class="text-xs">{{ $event->event_date->format('H:i') }}</span></td>
                <td class="px-6 py-4 text-gray-600">{{ $event->city ?? $event->location }}</td>
                <td class="px-6 py-4">
                    @if($event->is_paid)
                    <span style="color:var(--rose);">{{ number_format($event->price,0,',',' ') }} {{ $event->currency }}</span>
                    @else
                    <span class="text-green-600">Gratuit</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <span class="font-semibold" style="color:var(--dark);">{{ $event->registrations_count }}</span>
                    @if($event->capacity)<span class="text-gray-400"> / {{ $event->capacity }}</span>@endif
                </td>
                <td class="px-6 py-4">
                    @php $colors = ['published'=>'#059669','draft'=>'#6B7280','cancelled'=>'#DC2626','completed'=>'#7C3AED']; @endphp
                    <span class="text-xs px-2 py-1 rounded-full font-medium" style="background:{{ $colors[$event->status] ?? '#6B7280' }}18;color:{{ $colors[$event->status] ?? '#6B7280' }};">{{ ucfirst($event->status) }}</span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.registrations.index', $event) }}" class="text-xs px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">Inscrits</a>
                        <a href="{{ route('admin.events.edit', $event) }}" class="text-xs px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">Éditer</a>
                        <a href="{{ route('admin.scanner.index', $event) }}" class="text-xs px-3 py-1.5 rounded-lg text-white" style="background:var(--rose);">Scanner</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-12 text-gray-400">Aucun événement. <a href="{{ route('admin.events.create') }}" style="color:var(--rose);">En créer un</a></td></tr>
            @endforelse
        </tbody>
    </table>
    @if($events->hasPages())<div class="px-6 py-4 border-t border-gray-50">{{ $events->links() }}</div>@endif
</div>
@endsection
