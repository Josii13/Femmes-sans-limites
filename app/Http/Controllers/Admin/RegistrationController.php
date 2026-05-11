<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Services\QrCodeService;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    public function __construct(private QrCodeService $qrService) {}

    public function index(Event $event)
    {
        $registrations = $event->registrations()->latest()->get();
        return view('admin.registrations.index', compact('event', 'registrations'));
    }

    public function sendPaymentLink(Registration $registration)
    {
        $event = $registration->event;

        if (!$event->is_paid || !$event->payment_link) {
            return back()->with('error', 'Cet événement n\'a pas de lien de paiement configuré.');
        }

        \Mail::to($registration->email)->send(new \App\Mail\PaymentLinkMail($registration, $event));

        $registration->update([
            'status'           => 'payment_sent',
            'payment_sent_at'  => now(),
        ]);

        return back()->with('success', 'Lien de paiement envoyé à '.$registration->email);
    }

    public function confirmPayment(Registration $registration)
    {
        $qrToken = \Illuminate\Support\Str::uuid()->toString();
        $qrPath  = $this->qrService->generate($qrToken, $registration);

        $registration->update([
            'status'    => 'paid',
            'qr_token'  => $qrToken,
            'qr_code_path' => $qrPath,
            'paid_at'   => now(),
        ]);

        \Mail::to($registration->email)->send(new \App\Mail\QrCodeMail($registration));

        return back()->with('success', 'Paiement confirmé et QR code envoyé à '.$registration->email);
    }

    public function cancel(Registration $registration)
    {
        $registration->update(['status' => 'cancelled']);
        return back()->with('success', 'Inscription annulée.');
    }
}
