@extends('layouts.admin')
@section('title','Templates de campagne')
@section('page-title','Communication')
@section('page-subtitle','Templates')

@section('header-actions')
<a href="{{ route('admin.communication.templates.create') }}" class="btn-rose text-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Nouveau template
</a>
<a href="{{ route('admin.communication.index') }}" class="text-sm px-4 py-2 rounded-xl hover:bg-gray-100 transition-colors" style="color:var(--gray);">← Campagnes</a>
@endsection

@section('content')

@if(session('success'))
<div class="mb-6 px-4 py-3 rounded-xl text-sm font-medium" style="background:#F0FDF4;border:1px solid #86EFAC;color:#166534;">
    {{ session('success') }}
</div>
@endif

@if($templates->isEmpty())
<div class="admin-card py-16 text-center">
    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:var(--rose-pale);">
        <svg class="w-8 h-8" style="color:var(--rose)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    </div>
    <p class="font-semibold mb-1" style="color:var(--dark);">Aucun template pour l'instant</p>
    <p class="text-sm mb-5" style="color:var(--gray);">Créez votre premier template pour accélérer la création de campagnes.</p>
    <a href="{{ route('admin.communication.templates.create') }}" class="btn-rose text-sm">Créer un template</a>
</div>
@else
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
    @foreach($templates as $t)
    <div class="admin-card flex flex-col gap-4">

        {{-- Header --}}
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <h3 class="font-bold text-sm truncate" style="color:var(--dark);">{{ $t->name }}</h3>
                <p class="text-xs mt-0.5 truncate" style="color:var(--gray);">{{ $t->subject }}</p>
            </div>
            <span class="text-white text-[10px] font-bold px-2.5 py-1 rounded-full flex-shrink-0"
                  style="background:{{ $t->type_badge_color }};">{{ $t->type_label }}</span>
        </div>

        {{-- Aperçu corps --}}
        <div class="flex-1 px-3 py-3 rounded-xl text-xs leading-relaxed whitespace-pre-line line-clamp-5" style="background:#FAFAFA;color:var(--gray);font-family:monospace;">{{ Str::limit($t->body, 200) }}</div>

        {{-- CTA si présent --}}
        @if($t->cta_label)
        <div class="flex items-center gap-2 text-xs" style="color:var(--gray);">
            <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:var(--rose)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <span class="truncate">Bouton : <strong style="color:var(--dark);">{{ $t->cta_label }}</strong></span>
        </div>
        @endif

        {{-- Actions --}}
        <div class="flex items-center gap-2 pt-2" style="border-top:1px solid var(--border);">
            <a href="{{ route('admin.communication.create', ['template_id' => $t->id]) }}"
               class="flex-1 text-center text-xs font-semibold py-2 rounded-lg transition-colors"
               style="background:var(--rose-pale);color:var(--rose);"
               title="Utiliser ce template pour une nouvelle campagne">
                Utiliser
            </a>
            <a href="{{ route('admin.communication.templates.edit', $t) }}"
               class="flex-1 text-center text-xs font-semibold py-2 rounded-lg transition-colors"
               style="background:#FEF9EC;color:var(--gold);">
                Modifier
            </a>
            <form method="POST" action="{{ route('admin.communication.templates.destroy', $t) }}"
                  x-data @submit.prevent="if(confirm('Supprimer ce template ?')) $el.submit()">
                @csrf @method('DELETE')
                <button type="submit" class="px-3 py-2 rounded-lg text-xs transition-colors hover:bg-red-50" style="color:#EF4444;" title="Supprimer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </form>
        </div>

    </div>
    @endforeach
</div>
@endif
@endsection
