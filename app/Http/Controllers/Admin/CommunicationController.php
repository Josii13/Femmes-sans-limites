<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CampaignMail;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\CampaignTemplate;
use App\Models\Member;
use App\Services\CampaignDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CommunicationController extends Controller
{
    public function __construct(private CampaignDispatcher $dispatcher) {}

    public function index()
    {
        $campaigns = Campaign::latest()->paginate(20);

        $statsMonth = Campaign::sent()
            ->whereMonth('sent_at', now()->month)
            ->whereYear('sent_at', now()->year)
            ->selectRaw('COUNT(*) as total, SUM(sent_count) as emails, SUM(open_count) as opens')
            ->first();

        $statsYear = Campaign::sent()
            ->whereYear('sent_at', now()->year)
            ->selectRaw('COUNT(*) as total, SUM(sent_count) as emails, SUM(open_count) as opens')
            ->first();

        // 12 derniers mois pour le graphe
        $monthly = Campaign::sent()
            ->selectRaw('YEAR(sent_at) as y, MONTH(sent_at) as m, COUNT(*) as campaigns, SUM(sent_count) as emails')
            ->where('sent_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupByRaw('YEAR(sent_at), MONTH(sent_at)')
            ->orderByRaw('YEAR(sent_at), MONTH(sent_at)')
            ->get();

        return view('admin.communication.index', compact('campaigns', 'statsMonth', 'statsYear', 'monthly'));
    }

    public function create(Request $request)
    {
        $members = Member::where('status', 'active')->whereNotNull('email')->orderBy('name')->get(['id', 'name', 'email', 'type']);
        $templates = CampaignTemplate::orderBy('name')->get();
        $prefill = $request->template_id ? CampaignTemplate::find($request->template_id) : null;

        return view('admin.communication.create', compact('members', 'templates', 'prefill'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'subject' => 'required|string|max:200',
            'body' => 'required|string',
            'type' => 'required|in:text,text_image,text_cta,text_image_cta',
            'image' => 'nullable|image|max:5120',
            'cta_label' => 'nullable|string|max:100',
            'cta_url' => 'nullable|url|max:500',
            'target_type' => 'required|in:all,standard,gold,premium,custom,single',
            'target_member_id' => 'nullable|exists:members,id',
            'target_member_ids' => 'nullable|array',
            'target_member_ids.*' => 'exists:members,id',
            'send_mode' => 'required|in:draft,now,scheduled',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('campaigns', 'public');
        }

        $validated['status'] = match ($validated['send_mode']) {
            'now' => 'draft',   // sera sent juste après
            'scheduled' => 'scheduled',
            default => 'draft',
        };

        if ($validated['send_mode'] === 'scheduled') {
            $validated['scheduled_at'] = $request->scheduled_at;
        }

        $campaign = Campaign::create($validated);

        if ($validated['send_mode'] === 'now') {
            $this->dispatcher->dispatch($campaign);

            return redirect()->route('admin.communication.show', $campaign)
                ->with('success', "Campagne en cours d'envoi — les emails partent en arrière-plan.");
        }

        return redirect()->route('admin.communication.show', $campaign)
            ->with('success', $validated['send_mode'] === 'scheduled'
                ? 'Campagne programmée pour le '.$campaign->scheduled_at->format('d/m/Y à H\hi').'.'
                : 'Brouillon enregistré.');
    }

    public function show(Campaign $campaign)
    {
        $recipients = $campaign->recipients()->with('member')->latest('sent_at')->paginate(30);

        // Statistiques précalculées (évite les requêtes SQL dans la vue).
        $uniqueOpens = $campaign->recipients()->whereNotNull('opened_at')->count();

        return view('admin.communication.show', compact('campaign', 'recipients', 'uniqueOpens'));
    }

    public function edit(Campaign $campaign)
    {
        if ($campaign->status === 'sent') {
            return redirect()->route('admin.communication.show', $campaign)
                ->with('error', 'Une campagne déjà envoyée ne peut pas être modifiée.');
        }
        $members = Member::where('status', 'active')->whereNotNull('email')->orderBy('name')->get(['id', 'name', 'email', 'type']);
        $templates = CampaignTemplate::orderBy('name')->get();

        return view('admin.communication.edit', compact('campaign', 'members', 'templates'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        if ($campaign->status === 'sent') {
            return redirect()->route('admin.communication.show', $campaign)
                ->with('error', 'Campagne déjà envoyée, modification impossible.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'subject' => 'required|string|max:200',
            'body' => 'required|string',
            'type' => 'required|in:text,text_image,text_cta,text_image_cta',
            'image' => 'nullable|image|max:5120',
            'cta_label' => 'nullable|string|max:100',
            'cta_url' => 'nullable|url|max:500',
            'target_type' => 'required|in:all,standard,gold,premium,custom,single',
            'target_member_id' => 'nullable|exists:members,id',
            'target_member_ids' => 'nullable|array',
            'target_member_ids.*' => 'exists:members,id',
            'send_mode' => 'required|in:draft,now,scheduled',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        if ($request->hasFile('image')) {
            if ($campaign->image) {
                Storage::disk('public')->delete($campaign->image);
            }
            $validated['image'] = $request->file('image')->store('campaigns', 'public');
        }

        $validated['status'] = match ($validated['send_mode']) {
            'scheduled' => 'scheduled',
            default => 'draft',
        };

        $validated['scheduled_at'] = $validated['send_mode'] === 'scheduled' ? $request->scheduled_at : null;

        $campaign->update($validated);

        if ($validated['send_mode'] === 'now') {
            $this->dispatcher->dispatch($campaign);

            return redirect()->route('admin.communication.show', $campaign)
                ->with('success', "Campagne en cours d'envoi — les emails partent en arrière-plan.");
        }

        return redirect()->route('admin.communication.show', $campaign)->with('success', 'Campagne mise à jour.');
    }

    public function destroy(Campaign $campaign)
    {
        if ($campaign->image) {
            Storage::disk('public')->delete($campaign->image);
        }
        $campaign->delete();

        return redirect()->route('admin.communication.index')->with('success', 'Campagne supprimée.');
    }

    // Aperçu HTML du mail de campagne (rendu réel dans un nouvel onglet).
    // Utilise EXACTEMENT la même logique de personnalisation que l'envoi réel.
    public function preview(Campaign $campaign)
    {
        $member = Member::where('status', 'active')->first();

        $recipient = new CampaignRecipient([
            'campaign_id' => $campaign->id,
            'member_id' => $member?->id,
            'email' => $member?->email ?? 'apercu@fsl.ci',
            'name' => $member?->name ?? 'Marie Koné',
            'token' => 'preview',
        ]);

        // Valeurs d'exemple si aucun membre actif n'existe encore.
        $vars = CampaignMail::variables($member, [
            'prenom' => 'Marie',
            'nom' => 'Marie Koné',
            'numero' => 'FSL-STD-2026-00001',
            'type' => 'Standard',
            'ville' => 'Abidjan',
            'pays' => "Côte d'Ivoire",
            'profession' => 'Entrepreneur',
        ]);

        $resolvedBody = CampaignMail::personalize($campaign->body, $vars);

        return response()
            ->view('emails.campaign', compact('campaign', 'recipient', 'resolvedBody') + ['unsubscribeUrl' => null])
            ->header('X-Frame-Options', 'SAMEORIGIN');
    }

    // Envoi d'une campagne draft ou scheduled via bouton manuel
    public function send(Campaign $campaign)
    {
        if (in_array($campaign->status, ['sending', 'sent'], true)) {
            return redirect()->route('admin.communication.show', $campaign)
                ->with('error', $campaign->status === 'sending'
                    ? "Cette campagne est déjà en cours d'envoi."
                    : 'Cette campagne a déjà été envoyée.');
        }

        $this->dispatcher->dispatch($campaign);

        return redirect()->route('admin.communication.show', $campaign)
            ->with('success', "Campagne en cours d'envoi — les emails partent en arrière-plan.");
    }
}
