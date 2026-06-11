<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use App\Models\WaitingList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::where('status', 'published')
            ->where('event_date', '>=', now())
            ->withActiveRegistrationsCount()
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

        if ($event->event_date->isPast()) {
            return back()->with('error', 'Cet événement est terminé, les inscriptions sont closes.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:191',
            'phone' => 'nullable|string|max:30',
        ]);

        $validated['email'] = mb_strtolower(trim($validated['email']));

        // Transaction + verrou sur l'événement : sérialise les inscriptions concurrentes
        // (évite le surbooking et les doublons en cas de soumissions simultanées).
        return DB::transaction(function () use ($event, $validated) {
            $event = Event::whereKey($event->id)->lockForUpdate()->first();

            if (! $event->registration_open) {
                return back()->with('error', $event->is_sold_out
                    ? 'Cet événement est complet. Inscrivez-vous sur la liste d\'attente.'
                    : 'Les inscriptions pour cet événement sont closes.');
            }

            $exists = Registration::where('event_id', $event->id)
                ->where('email', $validated['email'])
                ->whereNotIn('status', ['cancelled'])
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                return back()->with('error', 'Vous êtes déjà inscrit(e) à cet événement avec cet email.');
            }

            Registration::create([
                ...$validated,
                'event_id' => $event->id,
                'status' => 'pending',
            ]);

            return back()->with('success', 'Inscription confirmée ! Vous recevrez bientôt un email de confirmation.');
        });
    }

    public function joinWaitingList(Request $request, string $slug)
    {
        $event = Event::where('slug', $slug)->where('status', 'published')->firstOrFail();

        if (! $event->is_sold_out) {
            return redirect()->route('events.show', $slug)
                ->with('error', 'Des places sont encore disponibles. Inscrivez-vous directement.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:191',
            'phone' => 'nullable|string|max:30',
        ]);

        $validated['email'] = mb_strtolower(trim($validated['email']));

        $existing = WaitingList::where('event_id', $event->id)
            ->where('email', $validated['email'])
            ->first();

        if ($existing) {
            return back()->with('error', 'Vous êtes déjà sur la liste d\'attente pour cet événement.');
        }

        WaitingList::create([...$validated, 'event_id' => $event->id]);

        return back()->with('success', 'Vous êtes ajouté(e) à la liste d\'attente. Nous vous contacterons si une place se libère.');
    }

    public function ical(string $slug)
    {
        $event = Event::where('slug', $slug)->where('status', 'published')->firstOrFail();

        // Échappement conforme RFC 5545 (\\, \; \, et sauts de ligne en \n) + conversion UTC.
        $esc = fn (string $s): string => addcslashes(
            str_replace(["\r\n", "\n", "\r"], '\n', $s),
            ',;\\'
        );

        $dtstart = $event->event_date->copy()->utc()->format('Ymd\THis\Z');
        $dtend = $event->event_date->copy()->addHours(2)->utc()->format('Ymd\THis\Z');
        $dtstamp = now()->utc()->format('Ymd\THis\Z');
        $uid = $event->slug.'@femme-sans-limites.com';
        $summary = $esc($event->title);
        $desc = $esc(strip_tags($event->description ?? ''));
        $location = $esc($event->location.($event->city ? ', '.$event->city : ''));
        $url = route('events.show', $event->slug);

        $ical = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//FSL//FSL Events//FR\r\nCALSCALE:GREGORIAN\r\n"
              ."BEGIN:VEVENT\r\nUID:{$uid}\r\nDTSTAMP:{$dtstamp}\r\nDTSTART:{$dtstart}\r\nDTEND:{$dtend}\r\n"
              ."SUMMARY:{$summary}\r\nDESCRIPTION:{$desc}\r\nLOCATION:{$location}\r\nURL:{$url}\r\n"
              ."END:VEVENT\r\nEND:VCALENDAR";

        return response($ical, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$event->slug.'.ics"',
        ]);
    }
}
