<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    public function index(Event $event)
    {
        return view('admin.scanner', compact('event'));
    }

    public function verify(Request $request)
    {
        $token = $request->input('token');

        $registration = Registration::where('qr_token', $token)->with('event')->first();

        if (!$registration) {
            return response()->json(['valid' => false, 'message' => 'QR code invalide ou introuvable.']);
        }

        if ($registration->status === 'attended') {
            return response()->json([
                'valid'   => false,
                'already' => true,
                'message' => 'Ce QR code a déjà été scanné.',
                'name'    => $registration->full_name,
                'event'   => $registration->event->title,
                'at'      => $registration->attended_at->format('H:i'),
            ]);
        }

        if ($registration->status !== 'paid') {
            return response()->json([
                'valid'   => false,
                'message' => 'Ce participant n\'a pas payé.',
                'name'    => $registration->full_name,
                'status'  => $registration->status,
            ]);
        }

        $registration->update(['status' => 'attended', 'attended_at' => now()]);

        return response()->json([
            'valid'  => true,
            'message' => 'Accès autorisé ✓',
            'name'   => $registration->full_name,
            'event'  => $registration->event->title,
        ]);
    }
}
