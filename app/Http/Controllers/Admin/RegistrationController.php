<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PaymentLinkMail;
use App\Mail\QrCodeMail;
use App\Mail\WaitingListSpotMail;
use App\Models\ActivityLog;
use App\Models\Event;
use App\Models\Registration;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistrationController extends Controller
{
    public function __construct(private QrCodeService $qrService) {}

    public function index(Event $event, Request $request)
    {
        $query = $event->registrations()->with('latestPayment')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $registrations = $query->paginate(50)->withQueryString();

        $totals = [
            'pending' => $event->registrations()->where('status', 'pending')->count(),
            'payment_sent' => $event->registrations()->where('status', 'payment_sent')->count(),
            'paid' => $event->registrations()->where('status', 'paid')->count(),
            'attended' => $event->registrations()->where('status', 'attended')->count(),
            'cancelled' => $event->registrations()->where('status', 'cancelled')->count(),
        ];

        $totalRevenue = $event->is_paid
            ? $event->registrations()->where('status', 'paid')->count() * (float) $event->price
            : 0;

        return view('admin.registrations.index', compact('event', 'registrations', 'totals', 'totalRevenue'));
    }

    public function sendPaymentLink(Registration $registration)
    {
        $event = $registration->event;

        if (! $event->is_paid || ! $event->payment_link) {
            return back()->with('error', 'Cet événement n\'a pas de lien de paiement configuré.');
        }

        if (! in_array($registration->status, ['pending', 'payment_sent'])) {
            return back()->with('error', 'Impossible d\'envoyer le lien pour ce statut.');
        }

        if (! $this->safeMail($registration->email, new PaymentLinkMail($registration, $event), 'PaymentLinkMail')) {
            return back()->with('error', "L'envoi du lien de paiement a échoué — vérifiez la config mail.");
        }

        $registration->update([
            'status' => 'payment_sent',
            'payment_sent_at' => now(),
        ]);

        return back()->with('success', 'Lien de paiement envoyé à '.$registration->email);
    }

    /**
     * Confirme l'accès d'un inscrit et lui envoie son QR code.
     * Pour un événement payant : vaut confirmation de paiement.
     * Pour un événement gratuit : génère simplement le QR d'accès.
     */
    public function confirmPayment(Registration $registration)
    {
        if (in_array($registration->status, ['paid', 'attended'], true)) {
            return back()->with('error', 'Cette inscription est déjà confirmée (QR déjà envoyé).');
        }

        if ($registration->status === 'cancelled') {
            return back()->with('error', 'Cette inscription est annulée. Réactivez-la avant de confirmer.');
        }

        $qrToken = Str::uuid()->toString();
        $qrPath = $this->qrService->generate($qrToken, $registration);

        $registration->update([
            'status' => 'paid',
            'qr_token' => $qrToken,
            'qr_code_path' => $qrPath,
            'paid_at' => now(),
        ]);

        $mailSent = $this->safeMail($registration->email, new QrCodeMail($registration), 'QrCodeMail');

        ActivityLog::record('payment.confirmed', $registration->event, [
            'registration_id' => $registration->id,
            'email' => $registration->email,
        ]);

        if (! $mailSent) {
            return back()->with('error', ($registration->event->is_paid ? 'Paiement confirmé' : 'Accès validé')
                .", mais l'envoi du QR par email a échoué — vérifiez la config mail.");
        }

        $msg = $registration->event->is_paid
            ? 'Paiement confirmé et QR code envoyé à '.$registration->email
            : 'Accès validé et QR code envoyé à '.$registration->email;

        return back()->with('success', $msg);
    }

    public function cancel(Registration $registration)
    {
        $registration->update(['status' => 'cancelled']);

        $this->notifyNextOnWaitingList($registration->event);

        return back()->with('success', 'Inscription annulée.');
    }

    /**
     * Met un email en file sans jamais renvoyer une page d'erreur à l'administrateur.
     * Retourne false si l'envoi a échoué, pour l'en informer dans le message flash.
     */
    private function safeMail(string $to, Mailable $mailable, string $label): bool
    {
        try {
            Mail::to($to)->queue($mailable);

            return true;
        } catch (\Throwable $e) {
            Log::warning("Inscriptions: échec mise en file {$label}", ['to' => $to, 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Quand une place se libère, prévient le prochain inscrit sur la liste d'attente.
     */
    private function notifyNextOnWaitingList(Event $event): void
    {
        // Pas de capacité = pas de liste d'attente ; et seulement s'il reste une place.
        if (! $event->capacity || $event->fresh()->is_sold_out) {
            return;
        }

        $next = $event->waitingList()->where('notified', false)->oldest()->first();

        if (! $next) {
            return;
        }

        try {
            Mail::to($next->email)->queue(new WaitingListSpotMail($event, $next));
            $next->update(['notified' => true]);
        } catch (\Throwable $e) {
            Log::warning('Liste d\'attente: échec notification', [
                'event_id' => $event->id, 'email' => $next->email, 'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Télécharge le QR d'une inscription depuis le disque privé (non exposé publiquement).
     */
    public function downloadQr(Registration $registration): StreamedResponse
    {
        abort_unless($registration->qr_code_path && Storage::disk('local')->exists($registration->qr_code_path), 404);

        return Storage::disk('local')->download($registration->qr_code_path, 'qr-acces-'.$registration->id.'.svg');
    }

    public function exportCsv(Event $event)
    {
        $registrations = $event->registrations()->latest()->get();

        $filename = 'inscriptions-'.$event->slug.'-'.now()->format('Ymd').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($registrations) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8 (fwrite : pas de format à interpréter)

            fputcsv($handle, ['Prénom', 'Nom', 'Email', 'Téléphone', 'Statut', 'Inscrit le', 'Payé le'], ';');

            foreach ($registrations as $r) {
                $statusMap = [
                    'pending' => 'En attente',
                    'payment_sent' => 'Lien envoyé',
                    'paid' => 'Payé',
                    'attended' => 'Présent',
                    'cancelled' => 'Annulé',
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
