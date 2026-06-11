<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewEbookMail;
use App\Models\ActivityLog;
use App\Models\Ebook;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class EbookController extends Controller
{
    public function index(Request $request)
    {
        $query = Ebook::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->search.'%')
                    ->orWhere('category', 'like', '%'.$request->search.'%');
            });
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
            'title' => 'required|string|max:200',
            'category' => 'required|string|max:100',
            'description' => 'required|string',
            'author_note' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'cta_label' => 'required|string|max:60',
            'cta_url' => 'required|url|max:500|starts_with:https://,http://',
            'status' => 'required|in:draft,published',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('ebooks', 'public');
        }

        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $ebook = Ebook::create($validated);

        ActivityLog::record('ebook.created', $ebook);

        $this->notifyNewsletterOnce($ebook);

        return redirect()->route('admin.ebooks.index')
            ->with('success', 'Ebook « '.$ebook->title.' » créé avec succès.');
    }

    public function edit(Ebook $ebook)
    {
        return view('admin.ebooks.edit', compact('ebook'));
    }

    public function update(Request $request, Ebook $ebook)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'category' => 'required|string|max:100',
            'description' => 'required|string',
            'author_note' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'cta_label' => 'required|string|max:60',
            'cta_url' => 'required|url|max:500|starts_with:https://,http://',
            'status' => 'required|in:draft,published',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('image')) {
            if ($ebook->image) {
                Storage::disk('public')->delete($ebook->image);
            }
            $validated['image'] = $request->file('image')->store('ebooks', 'public');
        }

        $validated['sort_order'] = $validated['sort_order'] ?? $ebook->sort_order;

        $ebook->update($validated);

        ActivityLog::record('ebook.updated', $ebook);

        // Envoie la newsletter à la 1re publication seulement (jamais de renvoi).
        $this->notifyNewsletterOnce($ebook);

        return redirect()->route('admin.ebooks.index')
            ->with('success', 'Ebook mis à jour.');
    }

    public function destroy(Ebook $ebook)
    {
        // Soft-delete : on conserve l'image de couverture pour permettre une restauration.
        ActivityLog::record('ebook.deleted', $ebook);
        $ebook->delete();

        return redirect()->route('admin.ebooks.index')
            ->with('success', 'Ebook supprimé.');
    }

    /**
     * Envoie la newsletter d'annonce une seule fois, à la première publication.
     * Empêche tout renvoi lors d'un cycle dépublication/republication.
     */
    private function notifyNewsletterOnce(Ebook $ebook): void
    {
        if ($ebook->status !== 'published' || $ebook->newsletter_sent_at !== null) {
            return;
        }

        $this->sendNewsletterForEbook($ebook);
        $ebook->forceFill(['newsletter_sent_at' => now()])->save();
    }

    private function sendNewsletterForEbook(Ebook $ebook): void
    {
        // Uniquement les abonnés confirmés et non désinscrits (RGPD), par lots, en file.
        NewsletterSubscriber::mailable()->chunkById(200, function ($subscribers) use ($ebook) {
            foreach ($subscribers as $subscriber) {
                try {
                    Mail::to($subscriber->email)->queue(new NewEbookMail($ebook, $subscriber));
                } catch (\Throwable $e) {
                    Log::warning('Newsletter ebook: échec mise en file', [
                        'ebook_id' => $ebook->id, 'email' => $subscriber->email, 'error' => $e->getMessage(),
                    ]);
                }
            }
        });
    }
}
