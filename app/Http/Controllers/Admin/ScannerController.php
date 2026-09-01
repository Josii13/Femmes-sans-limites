<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    public function index(Event $event)
    {
        return view('admin.scanner', compact('event'));
    }

    public function verify(Request $request, Event $event)
    {
        $token = $request->input('token');

        // Scopé à l'événement scanné : un QR valide pour un autre événement est rejeté.
        $registration = $event->registrations()->where('qr_token', $token)->with('event')->first();

        if (! $registration) {
            return response()->json(['valid' => false, 'message' => 'QR code invalide ou non valable pour cet événement.']);
        }

        if ($registration->status === 'attended') {
            return response()->json([
                'valid' => false,
                'already' => true,
                'message' => 'Ce QR code a déjà été scanné.',
                'name' => $registration->full_name,
                'event' => $registration->event->title,
                // attended_at peut être absent sur une donnée marquée à la main : jamais de 500 sur un scan.
                'at' => $registration->attended_at?->format('H:i'),
            ]);
        }

        if ($registration->status !== 'paid') {
            return response()->json([
                'valid' => false,
                'message' => 'Ce participant n\'a pas payé.',
                'name' => $registration->full_name,
                'status' => $registration->status,
            ]);
        }

        $registration->update(['status' => 'attended', 'attended_at' => now()]);

        return response()->json([
            'valid' => true,
            'message' => 'Accès autorisé ✓',
            'name' => $registration->full_name,
            'event' => $registration->event->title,
        ]);
    }
}
