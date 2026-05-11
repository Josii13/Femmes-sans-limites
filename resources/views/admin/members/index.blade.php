@extends('layouts.admin')
@section('title','Membres')
@section('page-title','Membres')
@section('page-subtitle', $members->total().' membre(s) — '.$pendingCount.' en attente d\'activation')

@section('header-actions')
<a href="{{ route('admin.members.create') }}" class="btn-rose text-sm px-5 py-2">
    + Nouveau membre
</a>
@endsection

@section('content')

{{-- Filters --}}
<form method="GET" class="flex gap-3 mb-6">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par nom ou email..." class="form-input w-64">
    <select name="type" class="form-input w-36">
        <option value="">Tous les types</option>
        <option value="standard" @selected(request('type')=='standard')>Standard</option>
        <option value="gold" @selected(request('type')=='gold')>Gold</option>
        <option value="premium" @selected(request('type')=='premium')>Premium</option>
    </select>
    <select name="status" class="form-input w-40">
        <option value="">Tous les statuts</option>
        <option value="active" @selected(request('status')=='active')>Actifs</option>
        <option value="inactive" @selected(request('status')=='inactive')>En attente</option>
    </select>
    <button type="submit" class="btn-rose text-sm px-5 py-2">Filtrer</button>
    @if(request()->anyFilled(['search','type','status']))
    <a href="{{ route('admin.members.index') }}" class="btn-gold text-sm px-5 py-2">Réinitialiser</a>
    @endif
</form>

<div class="admin-card overflow-hidden p-0">
    <table class="w-full text-sm">
        <thead>
            <tr style="background:#F8F7F9;">
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Membre</th>
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Profession</th>
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Localisation</th>
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Type</th>
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">N° Membre</th>
                <th class="text-left px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Statut</th>
                <th class="text-right px-6 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($members as $member)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 flex items-center justify-center font-bold text-white" style="background:var(--rose);">
                            @if($member->photo)
                            <img src="{{ asset('storage/'.$member->photo) }}" alt="" class="w-full h-full object-cover">
                            @else
                            {{ substr($member->name, 0, 1) }}
                            @endif
                        </div>
                        <div>
                            <p class="font-medium" style="color:var(--dark);">{{ $member->name }}</p>
                            <p class="text-xs text-gray-400">{{ $member->email }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-gray-600">{{ $member->profession }}</td>
                <td class="px-6 py-4 text-gray-600">{{ $member->city }}, {{ $member->country }}</td>
                <td class="px-6 py-4"><span class="badge-{{ $member->type }}">{{ ucfirst($member->type) }}</span></td>
                <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ $member->member_number }}</td>
                <td class="px-6 py-4">
                    @if($member->status === 'active')
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium" style="background:#05966918;color:#059669;">Actif</span>
                    @else
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium" style="background:#D91E6E18;color:#D91E6E;">En attente</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                        @if($member->status === 'inactive')
                        <form action="{{ route('admin.members.activate', $member) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs px-3 py-1.5 rounded-lg text-white font-medium transition-colors" style="background:#059669;" onclick="return confirm('Activer ce membre et lui envoyer sa carte ?')">✓ Activer</button>
                        </form>
                        @endif
                        <a href="{{ route('admin.members.show', $member) }}" class="text-xs px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors">Voir</a>
                        <a href="{{ route('admin.members.edit', $member) }}" class="text-xs px-3 py-1.5 rounded-lg hover:bg-gray-100 text-gray-600 transition-colors">Éditer</a>
                        <a href="{{ route('admin.members.download-card', $member) }}" class="text-xs px-3 py-1.5 rounded-lg text-white transition-colors" style="background:var(--gold);">⬇ Carte</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-12 text-gray-400">Aucun membre trouvé</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($members->hasPages())
    <div class="px-6 py-4 border-t border-gray-50">{{ $members->links() }}</div>
    @endif
</div>

@endsection
