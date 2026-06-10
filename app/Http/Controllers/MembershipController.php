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
                'email' => 'required|email|unique:members,email',
                'phone' => 'nullable|string|max:30',
                'motivation' => 'required|string|min:20|max:1000',
                'profession' => 'required|string|max:100',
                'country' => 'required|string|max:100',
                'city' => 'required|string|max:100',
                'photo' => 'nullable|image|max:3072',
            ], [], [
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
