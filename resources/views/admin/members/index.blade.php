@extends('layouts.admin')
@section('title','Membres')
@section('page-title','Membres')
@section('page-subtitle', $members->total().' membre(s) — '.$pendingCount.' en attente d\'activation')

@section('header-actions')
<a href="{{ route('admin.members.export-csv', request()->only(['status','type'])) }}" class="text-sm px-5 py-2 rounded-full border border-gray-200 hover:bg-gray-50 font-medium">⬇ Exporter CSV</a>
<a href="{{ route('admin.members.create') }}" class="btn-rose text-sm px-5 py-2">+ Nouveau membre</a>
@endsection

@section('content')

{{-- Filters --}}
<form method="GET" class="flex flex-wrap gap-3 mb-6" id="filter-form">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, email, n° membre..." class="form-input flex-1 min-w-[200px] sm:w-80 sm:flex-none">
    <div class="flex gap-3 flex-wrap">
        <select name="type" class="form-input w-32">
            <option value="">Tous types</option>
            <option value="standard" @selected(request('type')=='standard')>Standard</option>
            <option value="gold" @selected(request('type')=='gold')>Gold</option>
            <option value="premium" @selected(request('type')=='premium')>Premium</option>
        </select>
        <select name="status" class="form-input w-40">
            <option value="">Tous statuts</option>
            <option value="pending" @selected(request('status')=='pending')>En attente</option>
            <option value="active" @selected(request('status')=='active')>Actifs</option>
            <option value="rejected" @selected(request('status')=='rejected')>Refusés</option>
            <option value="expired" @selected(request('status')=='expired')>Expirés</option>
            <option value="suspended" @selected(request('status')=='suspended')>Suspendus</option>
        </select>
        <button type="submit" class="btn-rose text-sm px-4 py-2">Filtrer</button>
        @if(request()->anyFilled(['search','type','status']))
        <a href="{{ route('admin.members.index') }}" class="btn-gold text-sm px-4 py-2">✕</a>
        @endif
    </div>
</form>

<div x-data="bulkManager()" @keydown.escape.window="confirm = null">

    {{-- Confirm modal --}}
    <div x-show="confirm !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.5);">
        <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4">
            <p class="font-semibold text-gray-800 mb-2" x-text="confirm?.title ?? ''"></p>
            <p class="text-sm text-gray-500 mb-5" x-text="confirm?.body ?? ''"></p>
            <div class="flex gap-3">
                <button @click="submitForm(); confirm = null" class="flex-1 btn-rose text-sm py-2.5">Confirmer</button>
                <button @click="confirm = null" class="flex-1 text-sm py-2.5 rounded-full border border-gray-200 hover:bg-gray-50 font-medium">Annuler</button>
            </div>
        </div>
    </div>

    {{-- Bulk action bar (shows when items selected) --}}
    <div x-show="selected.length > 0" x-cloak
         class="flex items-center justify-between px-5 py-3 rounded-xl mb-3" style="background:var(--rose-pale);border:1px solid var(--rose-mid);">
        <span class="text-sm font-medium" style="color:var(--dark);"><span x-text="selected.length"></span> membre(s) sélectionné(s)</span>
        <div class="flex gap-2">
            <button @click="bulkActivate()" class="text-xs px-4 py-2 rounded-lg font-medium text-white" style="background:#059669;">✓ Activer</button>
            <button @click="bulkDelete()" class="text-xs px-4 py-2 rounded-lg font-medium text-red-500 bg-white border border-red-200 hover:bg-red-50">Supprimer</button>
            <button @click="selected = []" class="text-xs px-4 py-2 rounded-lg text-gray-500 hover:bg-white">Désélectionner</button>
        </div>
    </div>

    <form x-ref="bulk_form" action="{{ route('admin.members.bulk-action') }}" method="POST">
        @csrf
        <input type="hidden" name="action" x-bind:value="bulkAction">
        <template x-for="id in selected" :key="id">
            <input type="hidden" name="ids[]" :value="id">
        </template>
    </form>

    <div class="admin-card overflow-hidden p-0">
        <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[700px]">
            <thead>
                <tr style="background:#F8F7F9;">
                    <th class="px-6 py-3 w-10">
                        <input type="checkbox" @change="toggleAll($event)" class="rounded" :checked="allSelected">
                    </th>
                    @php
                        $sortBy  = request('sort', 'created_at');
                        $sortDir = request('dir', 'desc');
                        $nextDir = $sortDir === 'asc' ? 'desc' : 'asc';
                        $sortIcon = fn($col) => $sortBy === $col ? ($sortDir === 'asc' ? ' ↑' : ' ↓') : '';
                    @endphp
                    <th class="text-left px-5 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">
                        <a href="{{ request()->fullUrlWithQuery(['sort'=>'name','dir'=>$sortBy==='name'?$nextDir:'asc']) }}" class="hover:text-gray-800">Membre{{ $sortIcon('name') }}</a>
                    </th>
                    <th class="text-left px-5 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Profession / Ville</th>
                    <th class="text-left px-5 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">
                        <a href="{{ request()->fullUrlWithQuery(['sort'=>'type','dir'=>$sortBy==='type'?$nextDir:'asc']) }}" class="hover:text-gray-800">Type{{ $sortIcon('type') }}</a>
                    </th>
                    <th class="text-left px-5 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">
                        <a href="{{ request()->fullUrlWithQuery(['sort'=>'member_number','dir'=>$sortBy==='member_number'?$nextDir:'asc']) }}" class="hover:text-gray-800">N° Membre{{ $sortIcon('member_number') }}</a>
                    </th>
                    <th class="text-left px-5 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Statut</th>
                    <th class="text-right px-5 py-3 font-semibold text-xs uppercase tracking-wider text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($members as $member)
                <tr class="hover:bg-gray-50 transition-colors" :class="{ 'bg-rose-50': selected.includes({{ $member->id }}) }">
                    <td class="px-6 py-4 w-10">
                        <input type="checkbox" class="rounded" :checked="selected.includes({{ $member->id }})"
                               @change="toggle({{ $member->id }})">
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 flex items-center justify-center font-bold text-white text-sm" style="background:var(--rose);">
                                @if($member->photo)
                                <img src="{{ asset('storage/'.$member->photo) }}" alt="" class="w-full h-full object-cover">
                                @else
                                {{ substr($member->name, 0, 1) }}
                                @endif
                            </div>
                            <div>
                                <p class="font-medium" style="color:var(--dark);">{{ $member->name }}</p>
                                <p class="text-xs text-gray-400">{{ $member->email }}</p>
                                @if($member->phone)<p class="text-xs text-gray-300">{{ $member->phone }}</p>@endif
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-gray-600">
                        <p class="text-sm">{{ $member->profession }}</p>
                        <p class="text-xs text-gray-400">{{ $member->city }}, {{ $member->country }}</p>
                    </td>
                    <td class="px-5 py-4"><span class="badge-{{ $member->type }}">{{ ucfirst($member->type) }}</span></td>
                    <td class="px-5 py-4 font-mono text-xs text-gray-500">{{ $member->member_number }}</td>
                    <td class="px-5 py-4">
                        @php
                            $statusLabels = ['pending'=>'En attente','active'=>'Actif','rejected'=>'Refusé','expired'=>'Expiré','suspended'=>'Suspendu'];
                        @endphp
                        <span class="badge-{{ $member->status }}">{{ $statusLabels[$member->status] ?? $member->status }}</span>
                        @if($member->status === 'active' && $member->expires_at && $member->expires_at->diffInDays(now(), false) > -30)
                        <p class="text-[10px] mt-1" style="color:#B45309;">Expire {{ $member->expires_at->translatedFormat('d/m/Y') }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-1.5">
                            @if(in_array($member->status, ['pending','expired']))
                            <form x-ref="act_{{ $member->id }}" action="{{ route('admin.members.activate', $member) }}" method="POST">@csrf</form>
                            <button type="button"
                                @click="bulkAction = ''; pendingRef = 'act_{{ $member->id }}'; confirm = { title: 'Activer ce membre ?', body: '{{ $member->name }} recevra sa carte membre par email.' }"
                                class="text-xs px-3 py-1.5 rounded-lg text-white font-medium" style="background:#059669;">✓ Activer</button>
                            @endif
                            @if($member->status === 'pending')
                            <form x-ref="rej_{{ $member->id }}" action="{{ route('admin.members.reject', $member) }}" method="POST">@csrf</form>
                            <button type="button"
                                @click="bulkAction = ''; pendingRef = 'rej_{{ $member->id }}'; confirm = { title: 'Refuser cette candidature ?', body: '{{ $member->name }} recevra un email de refus bienveillant.' }"
                                class="text-xs px-2.5 py-1.5 rounded-lg font-medium" style="background:#FEE2E2;color:#991B1B;">Refuser</button>
                            @endif
                            <a href="{{ route('admin.members.show', $member) }}" class="text-xs px-2.5 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">Voir</a>
                            <a href="{{ route('admin.members.edit', $member) }}" class="text-xs px-2.5 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">Éditer</a>
                            <a href="{{ route('admin.members.download-card', $member) }}" class="text-xs px-2.5 py-1.5 rounded-lg text-white" style="background:var(--gold);" title="Télécharger carte">⬇</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-12 text-gray-400">Aucun membre trouvé</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>{{-- /overflow-x-auto --}}
        @if($members->hasPages())
        <div class="px-6 py-4 border-t border-gray-50">{{ $members->links() }}</div>
        @endif
    </div>
</div>

<script>
function bulkManager() {
    return {
        selected: [],
        confirm: null,
        bulkAction: '',
        pendingRef: '',

        get allSelected() {
            const ids = @json($members->pluck('id'));
            return ids.length > 0 && ids.every(id => this.selected.includes(id));
        },

        toggle(id) {
            const idx = this.selected.indexOf(id);
            if (idx === -1) this.selected.push(id);
            else this.selected.splice(idx, 1);
        },

        toggleAll(e) {
            const ids = @json($members->pluck('id'));
            if (e.target.checked) this.selected = [...ids];
            else this.selected = [];
        },

        bulkActivate() {
            this.pendingRef = '';
            this.bulkAction = 'activate';
            this.confirm = {
                title: `Activer ${this.selected.length} membre(s) ?`,
                body: 'Chaque membre recevra sa carte par email.'
            };
        },

        bulkDelete() {
            this.pendingRef = '';
            this.bulkAction = 'delete';
            this.confirm = {
                title: `Supprimer ${this.selected.length} membre(s) ?`,
                body: 'Cette action est irréversible.'
            };
        },

        submitForm() {
            if (this.pendingRef) {
                this.$refs[this.pendingRef]?.submit();
            } else if (this.bulkAction) {
                this.$refs.bulk_form.submit();
            }
        }
    }
}
</script>

@endsection
