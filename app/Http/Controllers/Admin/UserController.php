<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * Comptes du back-office.
 *
 * Il n'existait aucun écran : créer une administratrice imposait la ligne de
 * commande `fsl:make-admin`, et le rôle se résumait à un booléen — toute
 * personne ayant accès pouvait tout faire, paiements et suppression de membres
 * compris.
 *
 * Réservé au rôle « propriétaire » : donner le pouvoir de créer des comptes à
 * tout le monde reviendrait à n'avoir aucun rôle.
 */
class UserController extends Controller
{
    public function index()
    {
        return view('admin.users.index', [
            'users' => User::orderByRaw("CASE role WHEN 'owner' THEN 0 WHEN 'admin' THEN 1 ELSE 2 END")
                ->orderBy('name')
                ->paginate(20),
        ]);
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:191|unique:users,email',
            'role' => ['required', Rule::in(User::ROLES)],
        ], [
            'email.unique' => 'Un compte existe déjà avec cette adresse.',
        ]);

        // Aucun mot de passe n'est choisi ici : la personne définit le sien via le
        // lien reçu par email. Un mot de passe transmis par un tiers finit partagé.
        $user = User::create([
            'name' => $validated['name'],
            'email' => mb_strtolower(trim($validated['email'])),
            'role' => $validated['role'],
            'is_admin' => true,
            'password' => Hash::make(bin2hex(random_bytes(16))),
        ]);

        Password::broker()->sendResetLink(['email' => $user->email]);

        ActivityLog::record('user.created', $user, ['role' => $user->role]);

        return redirect()->route('admin.users.index')
            ->with('success', $user->name.' a reçu un email pour définir son mot de passe.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(User::ROLES)],
            'password' => ['nullable', 'confirmed', PasswordRule::min(8)],
        ]);

        // Une propriétaire ne peut pas se rétrograder elle-même : le back-office
        // se retrouverait sans personne pour gérer les comptes.
        if ($user->id === $request->user()->id && $user->isOwner() && $validated['role'] !== User::ROLE_OWNER) {
            return back()->with('error', 'Vous ne pouvez pas retirer votre propre rôle de propriétaire. Confiez-le d’abord à quelqu’un d’autre.');
        }

        if ($this->wouldRemoveLastOwner($user, $validated['role'])) {
            return back()->with('error', 'Il doit rester au moins une propriétaire.');
        }

        $user->fill([
            'name' => $validated['name'],
            'email' => mb_strtolower(trim($validated['email'])),
            'role' => $validated['role'],
        ]);

        if (! empty($validated['password'])) {
            $user->password = $validated['password']; // haché par le cast du modèle
        }

        $user->save();

        ActivityLog::record('user.updated', $user, ['role' => $user->role]);

        return redirect()->route('admin.users.index')->with('success', 'Compte mis à jour.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        if ($this->wouldRemoveLastOwner($user, null)) {
            return back()->with('error', 'Il doit rester au moins une propriétaire.');
        }

        ActivityLog::record('user.deleted', $user);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Compte supprimé.');
    }

    /**
     * Vérifie qu'on ne retire pas la dernière propriétaire — un back-office sans
     * propriétaire ne peut plus créer ni modifier aucun compte.
     */
    private function wouldRemoveLastOwner(User $user, ?string $newRole): bool
    {
        if (! $user->isOwner() || $newRole === User::ROLE_OWNER) {
            return false;
        }

        return User::where('role', User::ROLE_OWNER)->where('id', '!=', $user->id)->doesntExist();
    }
}
