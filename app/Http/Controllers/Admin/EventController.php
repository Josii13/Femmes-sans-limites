<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::withCount('registrations')->latest()->paginate(20);
        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'             => 'required|string|max:200',
            'description'       => 'required|string',
            'short_description' => 'nullable|string|max:300',
            'event_date'        => 'required|date|after:now',
            'location'          => 'required|string|max:200',
            'city'              => 'nullable|string|max:100',
            'capacity'          => 'nullable|integer|min:1',
            'is_paid'           => 'boolean',
            'price'             => 'nullable|numeric|min:0',
            'currency'          => 'nullable|string|max:20',
            'payment_link'      => 'nullable|url|max:500',
            'status'            => 'required|in:draft,published,cancelled',
            'image'             => 'nullable|image|max:5120',
        ]);

        $validated['is_paid'] = $request->boolean('is_paid');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        $event = Event::create($validated);

        return redirect()->route('admin.events.show', $event)
            ->with('success', 'Événement créé avec succès.');
    }

    public function show(Event $event)
    {
        $event->loadCount(['registrations', 'registrations as paid_count' => fn($q) => $q->where('status', 'paid')]);
        return view('admin.events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title'             => 'required|string|max:200',
            'description'       => 'required|string',
            'short_description' => 'nullable|string|max:300',
            'event_date'        => 'required|date',
            'location'          => 'required|string|max:200',
            'city'              => 'nullable|string|max:100',
            'capacity'          => 'nullable|integer|min:1',
            'is_paid'           => 'boolean',
            'price'             => 'nullable|numeric|min:0',
            'currency'          => 'nullable|string|max:20',
            'payment_link'      => 'nullable|url|max:500',
            'status'            => 'required|in:draft,published,cancelled,completed',
            'image'             => 'nullable|image|max:5120',
        ]);

        $validated['is_paid'] = $request->boolean('is_paid');

        if ($request->hasFile('image')) {
            if ($event->image) Storage::disk('public')->delete($event->image);
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        $event->update($validated);

        return redirect()->route('admin.events.show', $event)->with('success', 'Événement mis à jour.');
    }

    public function destroy(Event $event)
    {
        if ($event->image) Storage::disk('public')->delete($event->image);
        $event->delete();

        return redirect()->route('admin.events.index')->with('success', 'Événement supprimé.');
    }
}
