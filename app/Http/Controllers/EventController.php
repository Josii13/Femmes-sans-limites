<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\WaitingList;
use App\Services\GeniusPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function __construct(private GeniusPayService $genius) {}

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

        // Transaction + verrou : sérialise les inscriptions concurrentes (anti-surbooking/doublon).
        // L'appel réseau au prestataire de paiement se fait APRÈS la transaction (verrou non tenu).
        $result = DB::transaction(function () use ($event, $validated) {
            $event = Event::whereKey($event->id)->lockForUpdate()->first();

            if (! $event->registration_open) {
                return ['error' => $event->is_sold_out
                    ? 'Cet événement est complet. Inscrivez-vous sur la liste d\'attente.'
                    : 'Les inscriptions pour cet événement sont closes.'];
            }

            $exists = Registration::where('event_id', $event->id)
                ->where('email', $validated['email'])
                ->whereNotIn('status', ['cancelled'])
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                return ['error' => 'Vous êtes déjà inscrit(e) à cet événement avec cet email.'];
            }

            return ['registration' => Registration::create([
                ...$validated,
                'event_id' => $event->id,
                'status' => 'pending',
            ])];
        });

        if (! empty($result['error'])) {
            return back()->with('error', $result['error']);
        }

        // Événement payant → paiement en ligne (GeniusPay). Sinon, inscription gratuite confirmée.
        if ($event->is_paid && (float) $event->price > 0) {
            return $this->initiateEventPayment($event, $result['registration']);
        }

        return back()->with('success', 'Inscription confirmée ! Vous recevrez bientôt un email de confirmation.');
    }

    /** Crée le paiement GeniusPay pour une inscription et redirige vers le checkout. */
    private function initiateEventPayment(Event $event, Registration $registration)
    {
        $payment = Payment::create([
            'provider' => 'geniuspay',
            'reference' => (string) Str::uuid(),
            'status' => 'pending',
            'amount' => $event->price,
            'currency' => $event->currency ?: config('services.geniuspay.currency', 'XOF'),
            'customer_name' => $registration->full_name,
            'customer_email' => $registration->email,
            'customer_phone' => $registration->phone,
            'payable_type' => $registration->getMorphClass(),
            'payable_id' => $registration->id,
            'metadata' => ['type' => 'event', 'registration_id' => $registration->id],
        ]);

        try {
            $res = $this->genius->createPayment([
                'amount' => (float) $event->price,
                'currency' => $payment->currency,
                'description' => 'Inscription : '.$event->title,
                'customer' => [
                    'name' => $registration->full_name,
                    'email' => $registration->email,
                    'phone' => $registration->phone,
                    'country' => 'CI',
                ],
                'success_url' => route('payment.success', $payment->reference),
                'error_url' => route('payment.cancel', $payment->reference),
                'metadata' => ['reference' => $payment->reference, 'type' => 'event', 'registration_id' => $registration->id],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('events.show', $event->slug)
                ->with('error', "Ton inscription est enregistrée, mais le paiement n'a pas pu démarrer. L'équipe te recontactera avec un lien de paiement.");
        }

        $payment->update(['provider_reference' => $res['reference'], 'checkout_url' => $res['checkout_url']]);

        return redirect()->away($res['checkout_url']);
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
