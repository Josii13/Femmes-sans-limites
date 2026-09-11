@extends('layouts.member')
@section('title', 'Annuaire')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold" style="color:var(--dark);font-family:'Playfair Display',serif;">Annuaire de la communauté</h1>
    <p class="text-sm mt-1" style="color:var(--gray);">
        {{ $members->total() }} membre{{ $members->total() > 1 ? 's' : '' }} active{{ $members->total() > 1 ? 's' : '' }}.
        Réservé aux adhérentes — aucune coordonnée n’y figure.
    </p>
</div>

{{-- Recherche et filtres --}}
<form method="GET" action="{{ route('member.directory') }}" class="card p-4 mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
        <div class="sm:col-span-2">
            <input type="search" name="q" value="{{ request('q') }}" class="form-input"
                   placeholder="Nom, métier, ville…">
        </div>
        <select name="pays" class="form-input">
            <option value="">Tous les pays</option>
            @foreach($countries as $country)
            <option value="{{ $country }}" @selected(request('pays') === $country)>{{ $country }}</option>
            @endforeach
        </select>
        <select name="profession" class="form-input">
            <option value="">Tous les métiers</option>
            @foreach($professions as $profession)
            <option value="{{ $profession }}" @selected(request('profession') === $profession)>{{ $profession }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex items-center gap-3 mt-3">
        <button type="submit" class="btn-rose text-sm px-6 py-2">Rechercher</button>
        @if(request()->anyFilled(['q', 'pays', 'profession']))
        <a href="{{ route('member.directory') }}" class="text-sm" style="color:var(--gray);">Réinitialiser</a>
        @endif
    </div>
</form>

@if($members->isEmpty())
<div class="card p-10 text-center">
    <p class="font-semibold" style="color:var(--dark);">Aucune membre ne correspond</p>
    <p class="text-sm mt-1" style="color:var(--gray);">Essayez d’élargir votre recherche.</p>
</div>
@else
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
    @foreach($members as $member)
    <div class="card p-5 text-center {{ $currentMember && $currentMember->id === $member->id ? 'ring-2' : '' }}"
         @if($currentMember && $currentMember->id === $member->id) style="--tw-ring-color:var(--rose);" @endif>

        @if($member->photo)
        <img src="{{ asset('storage/'.$member->photo) }}" alt=""
             class="w-16 h-16 rounded-full object-cover object-top mx-auto mb-3">
        @else
        <div class="w-16 h-16 rounded-full mx-auto mb-3 flex items-center justify-center text-xl font-bold text-white"
             style="background:{{ $member->badge_color }};font-family:'Playfair Display',serif;">
            {{ mb_strtoupper(mb_substr($member->name, 0, 1)) }}
        </div>
        @endif

        <p class="font-bold text-sm leading-snug" style="color:var(--dark);">{{ $member->name }}</p>

        @if($member->profession)
        <p class="text-xs mt-1 line-clamp-2" style="color:var(--gray);">{{ $member->profession }}</p>
        @endif

        @if($member->city || $member->country)
        <p class="text-[11px] mt-1.5" style="color:var(--gray-light);">
            {{ collect([$member->city, $member->country])->filter()->join(', ') }}
        </p>
        @endif

        @if($member->type !== 'standard')
        <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full mt-2 text-white"
              style="background:{{ $member->badge_color }};">{{ ucfirst($member->type) }}</span>
        @endif

        @if($currentMember && $currentMember->id === $member->id)
        <p class="text-[10px] font-semibold mt-2" style="color:var(--rose);">C’est vous</p>
        @endif
    </div>
    @endforeach
</div>

<div class="mt-6">{{ $members->links() }}</div>
@endif

@endsection
