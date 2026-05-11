@extends('layouts.admin')
@section('title', $member->name)
@section('page-title', $member->name)
@section('page-subtitle', $member->member_number)

@section('header-actions')
<a href="{{ route('admin.members.edit', $member) }}" class="btn-gold text-sm px-5 py-2">Modifier</a>
<a href="{{ route('admin.members.download-card', $member) }}" class="btn-rose text-sm px-5 py-2">⬇ Télécharger la carte</a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Profile --}}
    <div class="lg:col-span-1 space-y-5">
        <div class="admin-card text-center">
            <div class="w-28 h-28 rounded-full overflow-hidden mx-auto mb-4 flex items-center justify-center font-bold text-white text-4xl" style="background:{{ $member->badge_color }};">
                @if($member->photo)
                <img src="{{ asset('storage/'.$member->photo) }}" alt="{{ $member->name }}" class="w-full h-full object-cover">
                @else
                {{ substr($member->name, 0, 1) }}
                @endif
            </div>
            <h2 class="text-xl font-bold mb-1" style="color:var(--dark);">{{ $member->name }}</h2>
            <p class="text-sm mb-3" style="color:var(--gray);">{{ $member->profession }}</p>
            <span class="badge-{{ $member->type }} text-sm px-4 py-1.5">{{ ucfirst($member->type) }}</span>

            <div class="mt-6 pt-6 border-t border-gray-50 space-y-3 text-left">
                <div class="flex items-center gap-3 text-sm">
                    <svg class="w-4 h-4 flex-shrink-0" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span class="text-gray-600 break-all">{{ $member->email }}</span>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <svg class="w-4 h-4 flex-shrink-0" style="color:var(--rose);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                    <span class="text-gray-600">{{ $member->city }}, {{ $member->country }}</span>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <svg class="w-4 h-4 flex-shrink-0" style="color:var(--gold);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="text-gray-600">Membre depuis {{ $member->created_at->format('M Y') }}</span>
                </div>
            </div>

            <div class="mt-6 space-y-2">
                <form action="{{ route('admin.members.send-card', $member) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full text-sm py-2.5 rounded-xl font-medium transition-all" style="background:rgba(217,30,110,0.08);color:var(--rose);" onmouseover="this.style.background='rgba(217,30,110,0.15)'" onmouseout="this.style.background='rgba(217,30,110,0.08)'">
                        ✉ Envoyer la carte par email
                    </button>
                </form>
                <form action="{{ route('admin.members.destroy', $member) }}" method="POST" onsubmit="return confirm('Supprimer ce membre ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full text-sm py-2 rounded-xl font-medium text-red-400 hover:bg-red-50 transition-colors">
                        Supprimer le membre
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Card preview + privileges --}}
    <div class="lg:col-span-2 space-y-5">
        {{-- Membre card preview --}}
        @if($member->card_path)
        <div class="admin-card">
            <h3 class="font-semibold text-sm mb-4" style="color:var(--dark);">Carte de membre</h3>
            <img src="{{ asset('storage/'.$member->card_path) }}" alt="Carte membre" class="w-full rounded-xl shadow-md">
        </div>
        @endif

        {{-- Privileges --}}
        <div class="admin-card">
            <h3 class="font-semibold text-sm mb-4" style="color:var(--dark);">Privilèges — {{ ucfirst($member->type) }}</h3>
            <ul class="space-y-2">
                @foreach($member->privileges_list as $priv)
                <li class="flex items-start gap-2 text-sm text-gray-600">
                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" style="color:{{ $member->badge_color }};" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ $priv }}
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
