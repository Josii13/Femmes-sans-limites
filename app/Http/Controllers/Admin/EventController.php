<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewEventMail;
use App\Models\ActivityLog;
use App\Models\Event;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::withCount('registrations')->withActiveRegistrationsCount();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->search.'%')
                    ->orWhere('location', 'like', '%'.$request->search.'%')
                    ->orWhere('city', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sortBy = in_array($request->sort, ['event_date', 'title', 'registrations_count', 'created_at']) ? $request->sort : 'created_at';
        $sortDir = $request->dir === 'asc' ? 'asc' : 'desc';

        $events = $query->orderBy($sortBy, $sortDir)->paginate(20)->withQueryString();

        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:300',
            'event_date' => 'required|date|after:now',
            'registration_closes_at' => 'nullable|date|before:event_date',
            'location' => 'required|string|max:200',
            'city' => 'nullable|string|max:100',
            'capacity' => 'nullable|integer|min:1',
            'is_paid' => 'boolean',
            'price' => 'nullable|required_if:is_paid,1|numeric|min:0',
            'currency' => 'nullable|string|max:20',
            'payment_link' => 'nullable|url|max:500',
            'status' => 'required|in:draft,published,cancelled',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ], [
            'price.required_if' => 'Le tarif est obligatoire pour un événement payant.',
            'payment_link.required_if' => 'Le lien de paiement est obligatoire pour un événement payant.',
            'event_date.after' => 'La date de l\'événement doit être dans le futur.',
        ]);

        $validated['is_paid'] = $request->boolean('is_paid');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        $event = Event::create($validated);

        if ($event->status === 'published') {
            $this->sendNewsletterForEvent($event);
        }

        return redirect()->route('admin.events.show', $event)
            ->with('success', 'Événement créé avec succès.');
    }

    public function show(Event $event)
    {
        $event->loadCount([
            'registrations',
            'registrations as paid_count' => fn ($q) => $q->where('status', 'paid'),
            'registrations as pending_count' => fn ($q) => $q->where('status', 'pending'),
            'registrations as attended_count' => fn ($q) => $q->where('status', 'attended'),
            'registrations as active_registrations_count' => fn ($q) => $q->whereNotIn('status', ['cancelled']),
            'waitingList as waiting_list_count',
        ]);

        return view('admin.events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:300',
            // Pas de contrainte de date future à l'édition : on doit pouvoir corriger
            // un événement déjà passé (libellé, lieu…) sans en changer la date.
            'event_date' => 'required|date',
            'registration_closes_at' => 'nullable|date|before:event_date',
            'location' => 'required|string|max:200',
            'city' => 'nullable|string|max:100',
            'capacity' => 'nullable|integer|min:1',
            'is_paid' => 'boolean',
            'price' => 'nullable|required_if:is_paid,1|numeric|min:0',
            'currency' => 'nullable|string|max:20',
            'payment_link' => 'nullable|url|max:500',
            'status' => 'required|in:draft,published,cancelled,completed',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ], [
            'price.required_if' => 'Le tarif est obligatoire pour un événement payant.',
            'payment_link.required_if' => 'Le lien de paiement est obligatoire pour un événement payant.',
        ]);

        $validated['is_paid'] = $request->boolean('is_paid');

        $wasPublished = $event->status === 'published';

        if ($request->hasFile('image')) {
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        $event->update($validated);

        if (! $wasPublished && $event->status === 'published') {
            $this->sendNewsletterForEvent($event);
        }

        return redirect()->route('admin.events.show', $event)->with('success', 'Événement mis à jour.');
    }

    public function destroy(Event $event)
    {
        if ($event->image) {
            Storage::disk('public')->delete($event->image);
        }

        // Archive l'événement et nettoie les données liées.
        // (Soft-delete : l'événement et ses inscriptions restent récupérables en base.)
        $event->registrations()->delete();   // soft-delete (Registration utilise SoftDeletes)
        $event->waitingList()->delete();      // suppression de la liste d'attente
        $event->delete();

        ActivityLog::record('event.deleted', $event);

        return redirect()->route('admin.events.index')->with('success', 'Événement supprimé.');
    }

    public function duplicate(Event $event)
    {
        $clone = $event->replicate(['slug', 'image']);
        $clone->title = $event->title.' (copie)';
        $clone->slug = Str::slug($clone->title).'-'.Str::random(5);
        $clone->status = 'draft';
        $clone->save();

        return redirect()->route('admin.events.edit', $clone)
            ->with('success', 'Événement dupliqué. Modifiez les détails avant de publier.');
    }

    private function sendNewsletterForEvent(Event $event): void
    {
        // Uniquement les abonnés confirmés et non désinscrits (RGPD), par lots, en file.
        NewsletterSubscriber::mailable()->chunkById(200, function ($subscribers) use ($event) {
            foreach ($subscribers as $subscriber) {
                try {
                    Mail::to($subscriber->email)->queue(new NewEventMail($event, $subscriber));
                } catch (\Throwable $e) {
                    Log::warning('Newsletter événement: échec mise en file', [
                        'event_id' => $event->id, 'email' => $subscriber->email, 'error' => $e->getMessage(),
                    ]);
                }
            }
        });
    }
}
