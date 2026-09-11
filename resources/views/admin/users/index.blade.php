@extends('layouts.admin')
@section('title','Comptes du back-office')
@section('page-title','Comptes du back-office')
@section('page-subtitle', $users->total().' compte(s)')

@section('content')

<div class="mb-6 p-4 rounded-xl text-sm" style="background:#FFF8F1;border:1px solid #FDDCAD;color:#92400E;">
    <strong>Les trois rôles :</strong>
    <strong>Propriétaire</strong> — tout, y compris les comptes et les tarifs ·
    <strong>Administratrice</strong> — tout sauf les comptes et les tarifs ·
    <strong>Éditrice</strong> — contenu éditorial seulement (ebooks, événements, témoignages, images),
    sans accès aux membres, aux paiements ni aux campagnes.
</div>

<div class="flex justify-end mb-4">
    <a href="{{ route('admin.users.create') }}" class="btn-rose text-sm">+ Nouveau compte</a>
</div>

<div class="admin-card overflow-x-auto" x-data="{ confirm: null, delForm: null }">
    <table class="w-full text-sm">
        <thead>
            <tr style="border-bottom:1px solid var(--border);">
                <th class="text-left px-5 py-3 font-semibold" style="color:var(--gray);">Nom</th>
                <th class="text-left px-5 py-3 font-semibold" style="color:var(--gray);">Email</th>
                <th class="text-left px-5 py-3 font-semibold" style="color:var(--gray);">Rôle</th>
                <th class="text-left px-5 py-3 font-semibold" style="color:var(--gray);">2FA</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr style="border-bottom:1px solid var(--border);">
                <td class="px-5 py-4 font-semibold" style="color:var(--dark);">
                    {{ $user->name }}
                    @if($user->id === auth()->id())
                    <span class="text-[10px] font-medium ml-1" style="color:var(--rose);">(vous)</span>
                    @endif
                </td>
                <td class="px-5 py-4" style="color:var(--gray);">{{ $user->email }}</td>
                <td class="px-5 py-4">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium"
                          style="background:{{ $user->isOwner() ? 'var(--rose-pale)' : '#F3F4F6' }};color:{{ $user->isOwner() ? 'var(--rose)' : '#6B7280' }};">
                        {{ $user->roleLabel() }}
                    </span>
                </td>
                <td class="px-5 py-4">
                    @if($user->hasTwoFactorEnabled())
                    <span class="text-xs" style="color:#059669;">✓ activée</span>
                    @else
                    <span class="text-xs" style="color:var(--gray-light);">non activée</span>
                    @endif
                </td>
                <td class="px-5 py-4 text-right whitespace-nowrap">
                    <a href="{{ route('admin.users.edit', $user) }}" class="text-xs px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">Éditer</a>
                    @if($user->id !== auth()->id())
                    <form x-ref="del_{{ $user->id }}" action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline">@csrf @method('DELETE')</form>
                    <button type="button" @click="confirm = @js($user->name); delForm = $refs.del_{{ $user->id }}"
                            class="text-xs px-3 py-1.5 rounded-lg text-red-400 hover:bg-red-50">Supprimer</button>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div x-show="confirm !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background:rgba(0,0,0,0.55);" @keydown.escape.window="confirm = null">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.outside="confirm = null">
            <p class="font-bold mb-2" style="color:var(--dark);">Supprimer ce compte ?</p>
            <p class="text-sm mb-5" style="color:var(--gray);">
                <strong x-text="confirm"></strong> perdra immédiatement l’accès au back-office.
            </p>
            <div class="flex gap-3">
                <button type="button" @click="delForm.submit()" class="btn-rose text-sm flex-1">Supprimer</button>
                <button type="button" @click="confirm = null" class="btn-gold text-sm flex-1">Annuler</button>
            </div>
        </div>
    </div>
</div>

<div class="mt-6">{{ $users->links() }}</div>

@endsection
