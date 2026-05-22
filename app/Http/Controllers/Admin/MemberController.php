<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Services\MemberCardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MemberController extends Controller
{
    public function __construct(private MemberCardService $cardService) {}

    public function index(Request $request)
    {
        $query = Member::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('member_number', 'like', '%' . $search . '%')
                  ->orWhere('profession', 'like', '%' . $search . '%')
                  ->orWhere('city', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sortBy  = in_array($request->sort, ['name', 'created_at', 'type', 'member_number']) ? $request->sort : 'created_at';
        $sortDir = $request->dir === 'asc' ? 'asc' : 'desc';

        $members = $query->orderBy($sortBy, $sortDir)->paginate(20)->withQueryString();

        $pendingCount = Member::where('status', 'inactive')->count();

        return view('admin.members.index', compact('members', 'pendingCount'));
    }

    public function create()
    {
        return view('admin.members.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'email'      => 'required|email|unique:members,email',
            'phone'      => 'nullable|string|max:30',
            'profession' => 'required|string|max:100',
            'country'    => 'required|string|max:100',
            'city'       => 'required|string|max:100',
            'type'       => 'required|in:standard,gold,premium',
            'photo'      => 'nullable|image|max:3072',
        ]);

        $validated['member_number'] = 'FSL-' . strtoupper(Str::random(6));

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('members/photos', 'public');
        }

        $member = Member::create($validated);

        $cardPath = $this->cardService->generate($member);
        $member->update(['card_path' => $cardPath]);

        \App\Models\ActivityLog::record('member.created', $member);

        return redirect()->route('admin.members.show', $member)
            ->with('success', 'Membre créé et carte générée avec succès.');
    }

    public function show(Member $member)
    {
        $member->load(['registrations.event' => fn($q) => $q->latest('event_date')]);
        return view('admin.members.show', compact('member'));
    }

    public function edit(Member $member)
    {
        return view('admin.members.edit', compact('member'));
    }

    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'email'      => 'required|email|unique:members,email,' . $member->id,
            'phone'      => 'nullable|string|max:30',
            'profession' => 'required|string|max:100',
            'country'    => 'required|string|max:100',
            'city'       => 'required|string|max:100',
            'type'       => 'required|in:standard,gold,premium',
            'status'     => 'required|in:active,inactive',
            'photo'      => 'nullable|image|max:3072',
        ]);

        $typeChanged    = $request->type   !== $member->type;
        $photoChanged   = $request->hasFile('photo');
        $becomingActive = $member->status !== 'active' && $request->status === 'active';

        if ($photoChanged) {
            if ($member->photo) Storage::disk('public')->delete($member->photo);
            $validated['photo'] = $request->file('photo')->store('members/photos', 'public');
        }

        $member->update($validated);

        if ($typeChanged || $photoChanged) {
            $cardPath = $this->cardService->generate($member->fresh());
            $member->update(['card_path' => $cardPath]);
        }

        $member->refresh();

        $sendMail = $member->status === 'active' && ($typeChanged || $becomingActive);
        $mailSent = false;
        if ($sendMail) {
            if (!$member->card_path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($member->card_path)) {
                $cardPath = $this->cardService->generate($member);
                $member->update(['card_path' => $cardPath]);
                $member->refresh();
            }
            try {
                \Mail::to($member->email)->send(new \App\Mail\MemberCardMail($member));
                $mailSent = true;
            } catch (\Throwable $e) {
                \Log::error('MemberCardMail on update failed for ' . $member->email . ': ' . $e->getMessage());
            }
        }

        $message = $sendMail && $mailSent
            ? 'Membre mis à jour. Sa nouvelle carte lui a été envoyée par email.'
            : ($sendMail ? 'Membre mis à jour. (Attention : l\'envoi de l\'email a échoué.)' : 'Membre mis à jour.');

        return redirect()->route('admin.members.show', $member)->with('success', $message);
    }

    public function destroy(Member $member)
    {
        if ($member->photo) Storage::disk('public')->delete($member->photo);
        if ($member->card_path) Storage::disk('public')->delete($member->card_path);

        \App\Models\ActivityLog::record('member.deleted', $member);
        $member->delete();

        return redirect()->route('admin.members.index')->with('success', 'Membre supprimé.');
    }

    public function downloadCard(Member $member)
    {
        if (!$member->card_path || !Storage::disk('public')->exists($member->card_path)) {
            $cardPath = $this->cardService->generate($member);
            $member->update(['card_path' => $cardPath]);
        }

        return Storage::disk('public')->download(
            $member->card_path,
            'carte-membre-' . Str::slug($member->name) . '.png'
        );
    }

    public function activate(Member $member)
    {
        $member->update(['status' => 'active']);

        if (!$member->card_path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($member->card_path)) {
            $cardPath = $this->cardService->generate($member);
            $member->update(['card_path' => $cardPath]);
            $member->refresh();
        }

        $mailSent = false;
        try {
            \Mail::to($member->email)->send(new \App\Mail\MemberCardMail($member));
            $mailSent = true;
        } catch (\Throwable $e) {
            \Log::error('MemberCardMail failed for ' . $member->email . ': ' . $e->getMessage());
        }

        \App\Models\ActivityLog::record('member.activated', $member);

        $msg = $mailSent
            ? $member->name . ' est maintenant active. Sa carte lui a été envoyée par email.'
            : $member->name . ' est maintenant active. (Attention : l\'envoi de l\'email a échoué — vérifiez la config mail.)';

        return back()->with($mailSent ? 'success' : 'error', $msg);
    }

    public function regenerateCard(Member $member)
    {
        $cardPath = $this->cardService->generate($member);
        $member->update(['card_path' => $cardPath]);

        return back()->with('success', 'Carte de ' . $member->name . ' régénérée avec succès.');
    }

    public function sendCard(Member $member)
    {
        if (!$member->card_path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($member->card_path)) {
            $cardPath = $this->cardService->generate($member);
            $member->update(['card_path' => $cardPath]);
            $member->refresh();
        }

        try {
            \Mail::to($member->email)->send(new \App\Mail\MemberCardMail($member));
        } catch (\Throwable $e) {
            \Log::error('MemberCardMail failed for ' . $member->email . ': ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
        }

        return back()->with('success', 'Carte envoyée à ' . $member->email);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action'  => 'required|in:activate,delete',
            'ids'     => 'required|array|min:1',
            'ids.*'   => 'integer|exists:members,id',
        ]);

        $members = Member::whereIn('id', $request->ids)->get();

        if ($request->action === 'activate') {
            $mailErrors = 0;
            foreach ($members as $member) {
                if ($member->status !== 'active') {
                    $member->update(['status' => 'active']);
                    if (!$member->card_path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($member->card_path)) {
                        $cardPath = $this->cardService->generate($member);
                        $member->update(['card_path' => $cardPath]);
                        $member->refresh();
                    }
                    try {
                        \Mail::to($member->email)->send(new \App\Mail\MemberCardMail($member));
                    } catch (\Throwable $e) {
                        $mailErrors++;
                        \Log::error('BulkActivate MemberCardMail failed for ' . $member->email . ': ' . $e->getMessage());
                    }
                }
            }
            $msg = $members->count() . ' membre(s) activé(s).';
            if ($mailErrors === 0) $msg .= ' Cartes envoyées par email.';
            else $msg .= ' (' . $mailErrors . ' email(s) en échec — vérifiez la config mail.)';
            return back()->with($mailErrors > 0 ? 'error' : 'success', $msg);
        }

        if ($request->action === 'delete') {
            foreach ($members as $member) {
                if ($member->photo) Storage::disk('public')->delete($member->photo);
                if ($member->card_path) Storage::disk('public')->delete($member->card_path);
                $member->delete();
            }
            return back()->with('success', $members->count() . ' membre(s) supprimé(s).');
        }

        return back();
    }

    public function exportCsv(Request $request)
    {
        $query = Member::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $members  = $query->latest()->get();
        $filename = 'membres-fsl-' . now()->format('Ymd') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($members) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

            fputcsv($handle, ['N° Membre', 'Nom', 'Email', 'Téléphone', 'Profession', 'Ville', 'Pays', 'Type', 'Statut', 'Inscrit le'], ';');

            foreach ($members as $m) {
                fputcsv($handle, [
                    $m->member_number,
                    $m->name,
                    $m->email,
                    $m->phone ?? '',
                    $m->profession,
                    $m->city,
                    $m->country,
                    ucfirst($m->type),
                    $m->status === 'active' ? 'Actif' : 'En attente',
                    $m->created_at->format('d/m/Y'),
                ], ';');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
