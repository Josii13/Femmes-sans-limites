<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::where('status', 'published')
            ->where('event_date', '>=', now())
            ->orderBy('event_date')
            ->get();

        return view('public.events.index', compact('events'));
    }

    public function show(string $slug)
    {
        $event = Event::where('slug', $slug)->where('status', 'published')->firstOrFail();
        return view('public.events.show', compact('event'));
    }

    public function register(Request $request, string $slug)
    {
        $event = Event::where('slug', $slug)->where('status', 'published')->firstOrFail();

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|max:191',
            'phone'      => 'nullable|string|max:30',
        ]);

        $existing = Registration::where('event_id', $event->id)
            ->where('email', $validated['email'])
            ->whereNotIn('status', ['cancelled'])
            ->first();

        if ($existing) {
            return back()->with('error', 'Vous êtes déjà inscrit(e) à cet événement avec cet email.');
        }

        Registration::create([
            ...$validated,
            'event_id' => $event->id,
            'status'   => 'pending',
        ]);

        return back()->with('success', 'Inscription confirmée ! Vous recevrez bientôt un email de confirmation.');
    }
}
