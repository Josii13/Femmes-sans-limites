<?php

namespace App\Http\Controllers;

use App\Mail\MembershipConfirmationMail;
use App\Mail\NewMembershipMail;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MembershipController extends Controller
{
    public function index()
    {
        return view('public.join');
    }

    public function store(Request $request)
    {
        // Honeypot anti-bot : un humain ne remplit jamais ce champ caché.
        if ($request->filled('website')) {
            return redirect()->route('membership.success');
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                // Ignore les candidatures supprimées (soft delete) : un email libéré redevient disponible.
                'email' => ['required', 'email', Rule::unique('members', 'email')->whereNull('deleted_at')],
                'phone' => 'nullable|string|max:30',
                'motivation' => 'required|string|min:30|max:1000',
                'profession' => 'required|string|max:100',
                'country' => 'required|string|max:100',
                'city' => 'required|string|max:100',
                'photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:3072',
            ], [
                'name.required' => 'Merci d\'indiquer ton nom complet.',
                'email.required' => 'Merci d\'indiquer ton adresse email.',
                'email.email' => 'L\'adresse email saisie n\'est pas valide (exemple : prenom@email.com).',
                'email.unique' => 'Cette adresse email a déjà été utilisée pour une candidature.',
                'phone.max' => 'Le numéro de téléphone est trop long.',
                'motivation.required' => 'Merci de nous expliquer ta motivation.',
                'motivation.min' => 'Ta motivation doit contenir au moins 30 caractères, pour qu\'on apprenne à mieux te connaître.',
                'motivation.max' => 'Ta motivation ne doit pas dépasser 1000 caractères.',
                'profession.required' => 'Merci de sélectionner ta profession.',
                'country.required' => 'Merci d\'indiquer ton pays.',
                'city.required' => 'Merci d\'indiquer ta ville.',
                'photo.image' => 'Le fichier choisi doit être une image.',
                'photo.mimes' => 'La photo doit être au format JPG, PNG ou WEBP.',
                'photo.max' => 'La photo ne doit pas dépasser 3 Mo.',
            ], [
                'name' => 'nom complet',
                'email' => 'email',
                'phone' => 'téléphone',
                'motivation' => 'motivation',
                'profession' => 'profession',
                'country' => 'pays',
                'city' => 'ville',
            ]);
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('modal', 'join');
        }

        $validated['type'] = 'standard';
        $validated['status'] = 'pending';
        $validated['member_number'] = Member::generateNumber('standard');

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('members/photos', 'public');
        }

        // L'index unique de la base inclut les lignes soft-deleted : on libère l'email
        // d'éventuelles candidatures supprimées pour qu'il redevienne réutilisable.
        $this->releaseTrashedEmail($validated['email']);

        // La carte n'est PAS générée ici : elle le sera à l'activation par l'admin
        // (évite des fichiers orphelins pour les candidatures non retenues).
        $member = Member::create($validated);

        // Notification aux administrateurs (en file, sans bloquer la requête).
        $admins = User::where('is_admin', true)->get();
        if ($admins->isEmpty()) {
            $admins = User::limit(1)->get();
        }
        foreach ($admins as $admin) {
            $this->safeMail($admin->email, new NewMembershipMail($member), 'NewMembershipMail');
        }

        // Accusé de réception au candidat.
        $this->safeMail($member->email, new MembershipConfirmationMail($member), 'MembershipConfirmationMail');

        return redirect()->route('membership.success');
    }

    public function success()
    {
        return view('public.join-success');
    }

    /**
     * Libère l'email détenu par d'éventuels membres supprimés (soft delete), afin
     * que l'insertion d'une nouvelle candidature ne viole pas la contrainte unique.
     */
    private function releaseTrashedEmail(string $email): void
    {
        Member::onlyTrashed()->where('email', $email)->get()->each(function (Member $old) {
            $old->forceFill([
                'email' => Str::limit($old->email.'#supprime-'.$old->id, 180, ''),
                'verification_token' => null,
            ])->save();
        });
    }

    /** Envoie un mail sans jamais faire échouer la requête publique (les mailables sont en file). */
    private function safeMail(string $to, Mailable $mailable, string $label): void
    {
        try {
            Mail::to($to)->send($mailable);
        } catch (\Throwable $e) {
            Log::warning("Adhésion: échec envoi {$label}", ['to' => $to, 'error' => $e->getMessage()]);
        }
    }
}
