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

    public function index(Event $event, Request $request)
    {
        $query = $event->registrations()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $registrations = $query->paginate(50)->withQueryString();

        $totals = [
            'pending'      => $event->registrations()->where('status', 'pending')->count(),
            'payment_sent' => $event->registrations()->where('status', 'payment_sent')->count(),
            'paid'         => $event->registrations()->where('status', 'paid')->count(),
            'attended'     => $event->registrations()->where('status', 'attended')->count(),
            'cancelled'    => $event->registrations()->where('status', 'cancelled')->count(),
        ];

        $totalRevenue = $event->is_paid
            ? $event->registrations()->where('status', 'paid')->count() * (float) $event->price
            : 0;

        return view('admin.registrations.index', compact('event', 'registrations', 'totals', 'totalRevenue'));
    }

    public function sendPaymentLink(Registration $registration)
    {
        $event = $registration->event;

        if (!$event->is_paid || !$event->payment_link) {
            return back()->with('error', 'Cet événement n\'a pas de lien de paiement configuré.');
        }

        if (!in_array($registration->status, ['pending', 'payment_sent'])) {
            return back()->with('error', 'Impossible d\'envoyer le lien pour ce statut.');
        }

        \Mail::to($registration->email)->send(new \App\Mail\PaymentLinkMail($registration, $event));

        $registration->update([
            'status'          => 'payment_sent',
            'payment_sent_at' => now(),
        ]);

        return back()->with('success', 'Lien de paiement envoyé à ' . $registration->email);
    }

    public function confirmPayment(Registration $registration)
    {
        if ($registration->status === 'paid') {
            return back()->with('error', 'Ce paiement est déjà confirmé. Le QR code a déjà été envoyé.');
        }

        $qrToken = \Illuminate\Support\Str::uuid()->toString();
        $qrPath  = $this->qrService->generate($qrToken, $registration);

        $registration->update([
            'status'       => 'paid',
            'qr_token'     => $qrToken,
            'qr_code_path' => $qrPath,
            'paid_at'      => now(),
        ]);

        \Mail::to($registration->email)->send(new \App\Mail\QrCodeMail($registration));

        \App\Models\ActivityLog::record('payment.confirmed', $registration->event, [
            'registration_id' => $registration->id,
            'email'           => $registration->email,
        ]);

        return back()->with('success', 'Paiement confirmé et QR code envoyé à ' . $registration->email);
    }

    public function cancel(Registration $registration)
    {
        $registration->update(['status' => 'cancelled']);
        return back()->with('success', 'Inscription annulée.');
    }

    public function exportCsv(Event $event)
    {
        $registrations = $event->registrations()->latest()->get();

        $filename = 'inscriptions-' . $event->slug . '-' . now()->format('Ymd') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($registrations, $event) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

            fputcsv($handle, ['Prénom', 'Nom', 'Email', 'Téléphone', 'Statut', 'Inscrit le', 'Payé le'], ';');

            foreach ($registrations as $r) {
                $statusMap = [
                    'pending'      => 'En attente',
                    'payment_sent' => 'Lien envoyé',
                    'paid'         => 'Payé',
                    'attended'     => 'Présent',
                    'cancelled'    => 'Annulé',
                ];
                fputcsv($handle, [
                    $r->first_name,
                    $r->last_name,
                    $r->email,
                    $r->phone ?? '',
                    $statusMap[$r->status] ?? $r->status,
                    $r->created_at->format('d/m/Y H:i'),
                    $r->paid_at ? $r->paid_at->format('d/m/Y H:i') : '',
                ], ';');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
