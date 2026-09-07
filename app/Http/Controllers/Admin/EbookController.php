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
use Illuminate\Validation\Rule;

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
            'price' => 'nullable|numeric|min:0',
            // Promotion : prix réduit + fenêtre. Voir promoRules() pour le détail.
            ...$this->promoRules($request),
            'currency' => 'nullable|string|max:10',
            // Un prix sans PDF donnerait une fiche publique invendable, sans le dire à l'admin.
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:30720', Rule::requiredIf($this->isPriced($request))],
            'cta_label' => 'nullable|string|max:60',
            'cta_url' => 'nullable|url|max:500|starts_with:https://,http://',
            'status' => 'required|in:draft,published',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'pdf.required' => 'Un ebook avec un prix doit avoir son fichier PDF, sinon il ne peut être ni vendu ni livré.',
            'pdf.mimes' => 'Le fichier de l\'ebook doit être un PDF.',
            'pdf.max' => 'Le PDF ne doit pas dépasser 30 Mo.',
            ...self::PROMO_MESSAGES,
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('ebooks', 'public');
        }

        if ($request->hasFile('pdf')) {
            // Disque PRIVÉ : le PDF n'est jamais accessible par URL directe (livré via lien signé).
            $validated['file_path'] = $request->file('pdf')->store('ebooks/files', 'local');
        }
        unset($validated['pdf']);

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
            'price' => 'nullable|numeric|min:0',
            // Promotion : prix réduit + fenêtre. Voir promoRules() pour le détail.
            ...$this->promoRules($request),
            'currency' => 'nullable|string|max:10',
            // Idem à l'édition, sauf si un PDF est déjà en place (on le conserve).
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:30720', Rule::requiredIf($this->isPriced($request) && ! $ebook->file_path)],
            'cta_label' => 'nullable|string|max:60',
            'cta_url' => 'nullable|url|max:500|starts_with:https://,http://',
            'status' => 'required|in:draft,published',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'pdf.required' => 'Un ebook avec un prix doit avoir son fichier PDF, sinon il ne peut être ni vendu ni livré.',
            'pdf.mimes' => 'Le fichier de l\'ebook doit être un PDF.',
            'pdf.max' => 'Le PDF ne doit pas dépasser 30 Mo.',
            ...self::PROMO_MESSAGES,
        ]);

        if ($request->hasFile('image')) {
            if ($ebook->image) {
                Storage::disk('public')->delete($ebook->image);
            }
            $validated['image'] = $request->file('image')->store('ebooks', 'public');
        }

        if ($request->hasFile('pdf')) {
            if ($ebook->file_path) {
                Storage::disk('local')->delete($ebook->file_path);
            }
            $validated['file_path'] = $request->file('pdf')->store('ebooks/files', 'local');
        }
        unset($validated['pdf']);

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

    /** Un prix strictement positif signifie « vendu sur le site » : le PDF devient indispensable. */
    private function isPriced(Request $request): bool
    {
        return (float) $request->input('price', 0) > 0;
    }

    /**
     * Règles de la promotion.
     *
     * Une promotion n'a de sens que sur un ebook payant, à un prix strictement
     * inférieur au prix normal, et — comme demandé — avec une date de fin : c'est
     * elle qui fait reprendre le prix normal. La date de début est facultative
     * (promo immédiate) mais permet de programmer une promo à l'avance.
     *
     * Une date de fin passée est acceptée : la promotion est simplement terminée.
     * L'exiger dans le futur empêcherait de modifier le titre d'un ebook dont une
     * ancienne promotion figure encore dans le formulaire.
     *
     * @return array<string, mixed>
     */
    private function promoRules(Request $request): array
    {
        $hasPromo = $request->filled('promo_price') && (float) $request->input('promo_price') > 0;

        return [
            'promo_price' => [
                'nullable', 'numeric', 'gt:0',
                // Comparé au prix normal soumis dans le même formulaire.
                'lt:price',
                Rule::requiredIf(fn () => $request->filled('promo_ends_at') || $request->filled('promo_starts_at')),
            ],
            'promo_starts_at' => ['nullable', 'date'],
            'promo_ends_at' => array_values(array_filter([
                Rule::requiredIf(fn () => $hasPromo),
                'nullable', 'date',
                // « after » ne s'applique qu'avec une date de début réelle à comparer.
                $request->filled('promo_starts_at') ? 'after:promo_starts_at' : null,
            ])),
        ];
    }

    /**
     * Messages de la promotion : les règles par défaut (« lt », « after ») sont
     * incompréhensibles pour l'administratrice qui remplit le formulaire.
     */
    private const PROMO_MESSAGES = [
        'promo_price.gt' => 'Le prix promotionnel doit être supérieur à 0.',
        'promo_price.lt' => 'Le prix promotionnel doit être inférieur au prix normal, sinon ce n\'est pas une promotion.',
        'promo_price.required' => 'Indiquez le prix promotionnel, ou videz les dates de la promotion.',
        'promo_ends_at.required' => 'Une promotion doit avoir une date de fin : c\'est elle qui fait reprendre le prix normal.',
        'promo_ends_at.after' => 'La fin de la promotion doit être postérieure à sa date de début.',
        'promo_starts_at.date' => 'La date de début de promotion est invalide.',
        'promo_ends_at.date' => 'La date de fin de promotion est invalide.',
    ];

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
