<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\MemberCardMail;
use App\Mail\MembershipRejectedMail;
use App\Models\ActivityLog;
use App\Models\Member;
use App\Services\MemberCardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    /** Champs imprimés sur la carte : tout changement impose une régénération. */
    private const CARD_FIELDS = ['name', 'profession', 'city', 'country', 'type'];

    public function __construct(private MemberCardService $cardService) {}

    public function index(Request $request)
    {
        $query = Member::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('member_number', 'like', '%'.$search.'%')
                    ->orWhere('profession', 'like', '%'.$search.'%')
                    ->orWhere('city', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sortBy = in_array($request->sort, ['name', 'created_at', 'type', 'member_number', 'expires_at']) ? $request->sort : 'created_at';
        $sortDir = $request->dir === 'asc' ? 'asc' : 'desc';

        $members = $query->orderBy($sortBy, $sortDir)->paginate(20)->withQueryString();

        $pendingCount = Member::pending()->count();

        return view('admin.members.index', compact('members', 'pendingCount'));
    }

    public function create()
    {
        return view('admin.members.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', 'email', Rule::unique('members', 'email')->whereNull('deleted_at')],
            'phone' => 'nullable|string|max:30',
            'profession' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'type' => 'required|in:standard,gold,premium',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:3072',
        ]);

        $validated['member_number'] = Member::generateNumber($validated['type']);
        // Un membre créé manuellement par l'admin est actif immédiatement.
        $validated['status'] = 'active';
        $validated['joined_at'] = now();
        $validated['expires_at'] = now()->addYears(Member::MEMBERSHIP_YEARS);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('members/photos', 'public');
        }

        // Libère l'email d'éventuels membres supprimés (l'index unique inclut les soft-deleted).
        $this->releaseTrashedEmail($validated['email']);

        $member = Member::create($validated);

        $this->ensureCard($member);

        ActivityLog::record('member.created', $member);

        return redirect()->route('admin.members.show', $member)
            ->with('success', 'Membre créé et carte générée avec succès.');
    }

    public function show(Member $member)
    {
        $member->load(['registrations.event' => fn ($q) => $q->latest('event_date')]);

        return view('admin.members.show', compact('member'));
    }

    public function edit(Member $member)
    {
        return view('admin.members.edit', compact('member'));
    }

    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', 'email', Rule::unique('members', 'email')->ignore($member->id)->whereNull('deleted_at')],
            'phone' => 'nullable|string|max:30',
            'profession' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'type' => 'required|in:standard,gold,premium',
            'status' => 'required|in:'.implode(',', Member::STATUSES),
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:3072',
        ]);

        $becomingActive = $member->status !== 'active' && $validated['status'] === 'active';
        $photoChanged = $request->hasFile('photo');

        if ($photoChanged) {
            if ($member->photo) {
                Storage::disk('public')->delete($member->photo);
            }
            $validated['photo'] = $request->file('photo')->store('members/photos', 'public');
        }

        $member->fill($validated);

        if ($becomingActive) {
            $member->joined_at = $member->joined_at ?? now();
            $member->expires_at = now()->addYears(Member::MEMBERSHIP_YEARS);
        }

        // Régénérer la carte si un champ imprimé a changé, si la photo change, ou à l'activation.
        $cardNeedsRefresh = $member->isDirty(self::CARD_FIELDS) || $photoChanged || $becomingActive;

        $member->save();

        if ($cardNeedsRefresh) {
            $member->update(['card_path' => $this->cardService->generate($member->fresh())]);
            $member->refresh();
        }

        // Envoyer la carte si le membre est actif ET (devient actif OU sa carte a changé).
        $sendMail = $member->status === 'active' && $cardNeedsRefresh;
        $mailSent = $sendMail ? $this->sendCardMail($member) : false;

        $message = $sendMail
            ? ($mailSent
                ? 'Membre mis à jour. Sa carte lui est envoyée par email.'
                : "Membre mis à jour. (Attention : l'envoi de l'email a échoué.)")
            : 'Membre mis à jour.';

        return redirect()->route('admin.members.show', $member)->with($sendMail && ! $mailSent ? 'error' : 'success', $message);
    }

    public function destroy(Member $member)
    {
        if ($member->photo) {
            Storage::disk('public')->delete($member->photo);
        }
        if ($member->card_path) {
            Storage::disk('public')->delete($member->card_path);
        }

        ActivityLog::record('member.deleted', $member);
        $this->releaseEmail($member);
        $member->delete();

        return redirect()->route('admin.members.index')->with('success', 'Membre supprimé.');
    }

    public function downloadCard(Member $member)
    {
        $this->ensureCard($member);

        return Storage::disk('public')->download(
            $member->card_path,
            'carte-membre-'.Str::slug($member->name).'.png'
        );
    }

    public function activate(Member $member)
    {
        $this->markActive($member);
        $mailSent = $this->sendCardMail($member);

        ActivityLog::record('member.activated', $member);

        $msg = $mailSent
            ? $member->name.' est maintenant active. Sa carte lui est envoyée par email.'
            : $member->name." est maintenant active. (Attention : l'envoi de l'email a échoué — vérifiez la config mail.)";

        return back()->with($mailSent ? 'success' : 'error', $msg);
    }

    public function reject(Member $member)
    {
        $member->update(['status' => 'rejected']);

        try {
            Mail::to($member->email)->send(new MembershipRejectedMail($member));
        } catch (\Throwable $e) {
            Log::warning('MembershipRejectedMail échec pour '.$member->email.' : '.$e->getMessage());
        }

        ActivityLog::record('member.rejected', $member);

        return back()->with('success', 'Candidature de '.$member->name.' refusée.');
    }

    public function regenerateCard(Member $member)
    {
        $member->update(['card_path' => $this->cardService->generate($member)]);

        return back()->with('success', 'Carte de '.$member->name.' régénérée avec succès.');
    }

    public function sendCard(Member $member)
    {
        return $this->sendCardMail($member)
            ? back()->with('success', 'Carte envoyée à '.$member->email)
            : back()->with('error', "Erreur lors de l'envoi de l'email — vérifiez la config mail.");
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,delete',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:members,id',
        ]);

        $members = Member::whereIn('id', $request->ids)->get();

        if ($request->action === 'activate') {
            $mailErrors = 0;
            foreach ($members as $member) {
                if ($member->status !== 'active') {
                    $this->markActive($member);
                    if (! $this->sendCardMail($member)) {
                        $mailErrors++;
                    }
                }
            }
            $msg = $members->count().' membre(s) activé(s).';
            $msg .= $mailErrors === 0 ? ' Cartes envoyées par email.' : ' ('.$mailErrors.' email(s) en échec — vérifiez la config mail.)';

            return back()->with($mailErrors > 0 ? 'error' : 'success', $msg);
        }

        if ($request->action === 'delete') {
            foreach ($members as $member) {
                if ($member->photo) {
                    Storage::disk('public')->delete($member->photo);
                }
                if ($member->card_path) {
                    Storage::disk('public')->delete($member->card_path);
                }
                $this->releaseEmail($member);
                $member->delete();
            }

            return back()->with('success', $members->count().' membre(s) supprimé(s).');
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

        $members = $query->latest()->get();
        $filename = 'membres-fsl-'.now()->format('Ymd').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $statusLabels = [
            'pending' => 'En attente', 'active' => 'Actif', 'rejected' => 'Refusé',
            'expired' => 'Expiré', 'suspended' => 'Suspendu',
        ];

        $callback = function () use ($members, $statusLabels) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8 (fwrite : pas de format à interpréter)

            fputcsv($handle, ['N° Membre', 'Nom', 'Email', 'Téléphone', 'Profession', 'Ville', 'Pays', 'Type', 'Statut', 'Inscrit le', 'Expire le'], ';');

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
                    $statusLabels[$m->status] ?? $m->status,
                    $m->created_at->format('d/m/Y'),
                    $m->expires_at?->format('d/m/Y') ?? '',
                ], ';');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Libère l'email (et le token) d'un membre avant suppression : la contrainte
     * unique au niveau base inclut les lignes soft-deleted, donc on « tombe » l'email
     * pour qu'il redevienne réutilisable par une nouvelle candidature.
     */
    private function releaseEmail(Member $member): void
    {
        $member->forceFill([
            'email' => Str::limit($member->email.'#supprime-'.$member->id, 180, ''),
            'verification_token' => null,
        ])->save();
    }

    /** Libère l'email détenu par d'éventuels membres supprimés portant cet email. */
    private function releaseTrashedEmail(string $email): void
    {
        Member::onlyTrashed()->where('email', $email)->get()->each(fn (Member $old) => $this->releaseEmail($old));
    }

    /** Garantit qu'une carte existe sur le disque (la régénère sinon). */
    private function ensureCard(Member $member): void
    {
        if (! $member->card_path || ! Storage::disk('public')->exists($member->card_path)) {
            $member->update(['card_path' => $this->cardService->generate($member)]);
            $member->refresh();
        }
    }

    /** Garantit la carte puis l'envoie par email. Retourne false en cas d'échec d'envoi. */
    private function sendCardMail(Member $member): bool
    {
        $this->ensureCard($member);

        try {
            Mail::to($member->email)->send(new MemberCardMail($member));

            return true;
        } catch (\Throwable $e) {
            Log::error('MemberCardMail échec pour '.$member->email.' : '.$e->getMessage());

            return false;
        }
    }

    /** Active un membre et (re)calcule ses dates d'adhésion / expiration. */
    private function markActive(Member $member): void
    {
        $member->forceFill([
            'status' => 'active',
            'joined_at' => $member->joined_at ?? now(),
            'expires_at' => now()->addYears(Member::MEMBERSHIP_YEARS),
        ])->save();
    }
}
