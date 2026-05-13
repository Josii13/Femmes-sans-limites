<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewEbookMail;
use App\Models\Ebook;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class EbookController extends Controller
{
    public function index(Request $request)
    {
        $query = Ebook::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $ebooks = $query->orderBy('sort_order')->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('admin.ebooks.index', compact('ebooks'));
    }

    public function create()
    {
        return view('admin.ebooks.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:200',
            'category'    => 'required|string|max:100',
            'description' => 'required|string',
            'author_note' => 'nullable|string',
            'image'       => 'nullable|image|max:5120',
            'cta_label'   => 'required|string|max:60',
            'cta_url'     => 'required|url|max:500',
            'status'      => 'required|in:draft,published',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('ebooks', 'public');
        }

        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $ebook = Ebook::create($validated);

        \App\Models\ActivityLog::record('ebook.created', $ebook);

        if ($ebook->status === 'published') {
            $this->sendNewsletterForEbook($ebook);
        }

        return redirect()->route('admin.ebooks.index')
            ->with('success', 'Ebook « ' . $ebook->title . ' » créé avec succès.');
    }

    public function edit(Ebook $ebook)
    {
        return view('admin.ebooks.edit', compact('ebook'));
    }

    public function update(Request $request, Ebook $ebook)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:200',
            'category'    => 'required|string|max:100',
            'description' => 'required|string',
            'author_note' => 'nullable|string',
            'image'       => 'nullable|image|max:5120',
            'cta_label'   => 'required|string|max:60',
            'cta_url'     => 'required|url|max:500',
            'status'      => 'required|in:draft,published',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $wasPublished = $ebook->status === 'published';

        if ($request->hasFile('image')) {
            if ($ebook->image) Storage::disk('public')->delete($ebook->image);
            $validated['image'] = $request->file('image')->store('ebooks', 'public');
        }

        $validated['sort_order'] = $validated['sort_order'] ?? $ebook->sort_order;

        $ebook->update($validated);

        // Envoyer la newsletter uniquement si on passe de draft → published
        if (!$wasPublished && $ebook->status === 'published') {
            $this->sendNewsletterForEbook($ebook);
        }

        return redirect()->route('admin.ebooks.index')
            ->with('success', 'Ebook mis à jour.');
    }

    public function destroy(Ebook $ebook)
    {
        if ($ebook->image) Storage::disk('public')->delete($ebook->image);
        $ebook->delete();

        return redirect()->route('admin.ebooks.index')
            ->with('success', 'Ebook supprimé.');
    }

    private function sendNewsletterForEbook(Ebook $ebook): void
    {
        NewsletterSubscriber::all()->each(function ($subscriber) use ($ebook) {
            try {
                Mail::to($subscriber->email)->send(new NewEbookMail($ebook, $subscriber));
            } catch (\Throwable) {
                // fail silently per subscriber
            }
        });
    }
}
