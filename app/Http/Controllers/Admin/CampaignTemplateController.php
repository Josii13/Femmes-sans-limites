<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampaignTemplate;
use Illuminate\Http\Request;

class CampaignTemplateController extends Controller
{
    public function index()
    {
        $templates = CampaignTemplate::latest()->get();

        return view('admin.communication.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.communication.templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'subject' => 'required|string|max:200',
            'type' => 'required|in:text,text_image,text_cta,text_image_cta',
            'body' => 'required|string',
            'cta_label' => 'nullable|string|max:100',
            'cta_url' => 'nullable|url|max:500',
        ]);

        CampaignTemplate::create($validated);

        return redirect()->route('admin.communication.templates.index')
            ->with('success', 'Template créé avec succès.');
    }

    public function edit(CampaignTemplate $template)
    {
        return view('admin.communication.templates.edit', compact('template'));
    }

    public function update(Request $request, CampaignTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'subject' => 'required|string|max:200',
            'type' => 'required|in:text,text_image,text_cta,text_image_cta',
            'body' => 'required|string',
            'cta_label' => 'nullable|string|max:100',
            'cta_url' => 'nullable|url|max:500',
        ]);

        $template->update($validated);

        return redirect()->route('admin.communication.templates.index')
            ->with('success', 'Template mis à jour.');
    }

    public function destroy(CampaignTemplate $template)
    {
        $template->delete();

        return redirect()->route('admin.communication.templates.index')
            ->with('success', 'Template supprimé.');
    }

    public function apply(CampaignTemplate $template)
    {
        return response()->json($template->only(['subject', 'type', 'body', 'cta_label', 'cta_url']));
    }
}
