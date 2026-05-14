<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CampaignMail;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CommunicationController extends Controller
{
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

        return view('admin.communication.index', compact('campaigns','statsMonth','statsYear','monthly'));
    }

    public function create()
    {
        $members = Member::where('status','active')->whereNotNull('email')->orderBy('name')->get(['id','name','email','type']);
        return view('admin.communication.create', compact('members'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'             => 'required|string|max:200',
            'subject'           => 'required|string|max:200',
            'body'              => 'required|string',
            'type'              => 'required|in:text,text_image,text_cta,text_image_cta',
            'image'             => 'nullable|image|max:5120',
            'cta_label'         => 'nullable|string|max:100',
            'cta_url'           => 'nullable|url|max:500',
            'target_type'       => 'required|in:all,standard,gold,premium,custom,single',
            'target_member_id'  => 'nullable|exists:members,id',
            'target_member_ids' => 'nullable|array',
            'target_member_ids.*'=> 'exists:members,id',
            'send_mode'         => 'required|in:draft,now,scheduled',
            'scheduled_at'      => 'nullable|date|after:now',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('campaigns','public');
        }

        $validated['status'] = match($validated['send_mode']) {
            'now'       => 'draft',   // sera sent juste après
            'scheduled' => 'scheduled',
            default     => 'draft',
        };

        if ($validated['send_mode'] === 'scheduled') {
            $validated['scheduled_at'] = $request->scheduled_at;
        }

        $campaign = Campaign::create($validated);

        if ($validated['send_mode'] === 'now') {
            $this->dispatch($campaign);
            return redirect()->route('admin.communication.show', $campaign)
                ->with('success', 'Campagne envoyée avec succès — '.$campaign->sent_count.' destinataire(s).');
        }

        return redirect()->route('admin.communication.show', $campaign)
            ->with('success', $validated['send_mode'] === 'scheduled'
                ? 'Campagne programmée pour le '.$campaign->scheduled_at->format('d/m/Y à H\hi').'.'
                : 'Brouillon enregistré.');
    }

    public function show(Campaign $campaign)
    {
        $recipients = $campaign->recipients()->with('member')->latest('sent_at')->paginate(30);
        return view('admin.communication.show', compact('campaign','recipients'));
    }

    public function edit(Campaign $campaign)
    {
        if ($campaign->status === 'sent') {
            return redirect()->route('admin.communication.show', $campaign)
                ->with('error', 'Une campagne déjà envoyée ne peut pas être modifiée.');
        }
        $members = Member::where('status','active')->whereNotNull('email')->orderBy('name')->get(['id','name','email','type']);
        return view('admin.communication.edit', compact('campaign','members'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        if ($campaign->status === 'sent') {
            return redirect()->route('admin.communication.show', $campaign)
                ->with('error', 'Campagne déjà envoyée, modification impossible.');
        }

        $validated = $request->validate([
            'title'             => 'required|string|max:200',
            'subject'           => 'required|string|max:200',
            'body'              => 'required|string',
            'type'              => 'required|in:text,text_image,text_cta,text_image_cta',
            'image'             => 'nullable|image|max:5120',
            'cta_label'         => 'nullable|string|max:100',
            'cta_url'           => 'nullable|url|max:500',
            'target_type'       => 'required|in:all,standard,gold,premium,custom,single',
            'target_member_id'  => 'nullable|exists:members,id',
            'target_member_ids' => 'nullable|array',
            'target_member_ids.*'=> 'exists:members,id',
            'send_mode'         => 'required|in:draft,now,scheduled',
            'scheduled_at'      => 'nullable|date|after:now',
        ]);

        if ($request->hasFile('image')) {
            if ($campaign->image) Storage::disk('public')->delete($campaign->image);
            $validated['image'] = $request->file('image')->store('campaigns','public');
        }

        $validated['status'] = match($validated['send_mode']) {
            'scheduled' => 'scheduled',
            default     => 'draft',
        };

        $validated['scheduled_at'] = $validated['send_mode'] === 'scheduled' ? $request->scheduled_at : null;

        $campaign->update($validated);

        if ($validated['send_mode'] === 'now') {
            $this->dispatch($campaign);
            return redirect()->route('admin.communication.show', $campaign)
                ->with('success', 'Campagne envoyée — '.$campaign->sent_count.' destinataire(s).');
        }

        return redirect()->route('admin.communication.show', $campaign)->with('success', 'Campagne mise à jour.');
    }

    public function destroy(Campaign $campaign)
    {
        if ($campaign->image) Storage::disk('public')->delete($campaign->image);
        $campaign->delete();
        return redirect()->route('admin.communication.index')->with('success', 'Campagne supprimée.');
    }

    // Envoi d'une campagne draft ou scheduled via bouton manuel
    public function send(Campaign $campaign)
    {
        if ($campaign->status === 'sent') {
            return redirect()->route('admin.communication.show', $campaign)
                ->with('error', 'Cette campagne a déjà été envoyée.');
        }
        $this->dispatch($campaign);
        return redirect()->route('admin.communication.show', $campaign)
            ->with('success', 'Campagne envoyée — '.$campaign->sent_count.' destinataire(s).');
    }

    // Résolution des destinataires selon la cible
    public function resolveRecipients(Campaign $campaign): \Illuminate\Database\Eloquent\Collection
    {
        $base = Member::where('status','active')->whereNotNull('email')->where('email','!=','');

        return match($campaign->target_type) {
            'all'                               => $base->get(),
            'standard','gold','premium'         => $base->where('type', $campaign->target_type)->get(),
            'single'                            => Member::where('id', $campaign->target_member_id)
                                                         ->where('status','active')->get(),
            'custom'                            => $base->whereIn('id', $campaign->target_member_ids ?? [])->get(),
            default                             => collect(),
        };
    }

    // Envoi effectif
    public function dispatch(Campaign $campaign): void
    {
        $campaign->update(['status' => 'sending']);
        $members = $this->resolveRecipients($campaign);

        foreach ($members as $member) {
            $recipient = CampaignRecipient::create([
                'campaign_id' => $campaign->id,
                'member_id'   => $member->id,
                'email'       => $member->email,
                'name'        => $member->name,
            ]);

            try {
                Mail::to($member->email)->send(new CampaignMail($campaign, $recipient));
                $recipient->update(['sent_at' => now()]);
                $campaign->increment('sent_count');
            } catch (\Throwable) {
                // fail silently per recipient
            }
        }

        $campaign->update(['status' => 'sent', 'sent_at' => now()]);
    }
}
