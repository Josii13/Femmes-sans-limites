<?php

namespace App\Services;

use App\Mail\EbookDeliveryMail;
use App\Mail\MemberCardMail;
use App\Mail\QrCodeMail;
use App\Models\ActivityLog;
use App\Models\Ebook;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Registration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PaymentFulfiller
{
    public function __construct(
        private QrCodeService $qrService,
        private MemberCardService $cardService,
    ) {}

    /**
     * Exécute l'action métier après un paiement réussi, selon le type d'objet payé.
     * Idempotent : ne refait rien si le paiement était déjà honoré.
     */
    public function fulfill(Payment $payment): void
    {
        $payable = $payment->payable;

        if ($payable instanceof Registration) {
            $this->confirmRegistration($payable, $payment);

            return;
        }

        if ($payable instanceof Ebook) {
            $this->fulfillEbook($payment, $payable);

            return;
        }

        if ($payable instanceof Member) {
            $this->fulfillMembership($payment, $payable);
        }
    }

    /**
     * Confirme une inscription : génère le QR, passe en « paid » et envoie le reçu + QR.
     * Utilisable pour un paiement en ligne (avec $payment) OU une inscription gratuite (sans).
     * Idempotent.
     */
    public function confirmRegistration(Registration $registration, ?Payment $payment = null): void
    {
        if (in_array($registration->status, ['paid', 'attended'], true)) {
            return; // déjà honoré
        }

        $token = Str::uuid()->toString();
        $registration->update([
            'status' => 'paid',
            'qr_token' => $token,
            'qr_code_path' => $this->qrService->generate($token, $registration),
            'paid_at' => now(),
        ]);

        try {
            Mail::to($registration->email)->queue(new QrCodeMail($registration, $payment));
        } catch (\Throwable $e) {
            Log::warning('Confirmation: échec envoi QrCodeMail', ['reg' => $registration->id, 'error' => $e->getMessage()]);
        }

        ActivityLog::record($payment ? 'payment.confirmed' : 'registration.confirmed', $registration->event, [
            'registration_id' => $registration->id,
            'reference' => $payment?->reference,
            'free' => $payment === null,
        ]);
    }

    /**
     * Cotisation réglée : on prolonge l'adhésion et on applique le niveau acheté.
     *
     * La prolongation part de l'échéance existante quand elle est encore à venir,
     * pour qu'un renouvellement anticipé n'ampute pas les jours restants. Si
     * l'adhésion était échue, elle repart d'aujourd'hui.
     *
     * Le niveau et la durée sont relus depuis les métadonnées du paiement, figées
     * à l'achat : un changement de tarif entre-temps ne doit pas modifier ce que
     * la membre a effectivement payé.
     */
    private function fulfillMembership(Payment $payment, Member $member): void
    {
        $plan = (string) data_get($payment->metadata, 'plan', $member->type);
        $months = (int) data_get($payment->metadata, 'duration_months', 12);

        $from = $member->expires_at && $member->expires_at->isFuture() ? $member->expires_at : now();

        $member->forceFill([
            'type' => in_array($plan, Member::TYPES, true) ? $plan : $member->type,
            'status' => 'active',
            'joined_at' => $member->joined_at ?? now(),
            'expires_at' => $from->copy()->addMonths(max(1, $months)),
            // L'adhésion repart : la relance précédente ne doit pas bloquer la suivante.
            'renewal_reminded_at' => null,
        ])->save();

        try {
            // La carte porte le niveau et doit donc être régénérée après une montée en gamme.
            $member->update(['card_path' => $this->cardService->generate($member->fresh())]);
            Mail::to($member->email)->queue(new MemberCardMail($member->fresh()));
        } catch (\Throwable $e) {
            Log::warning('Adhésion: échec régénération/envoi de la carte', [
                'member' => $member->id, 'error' => $e->getMessage(),
            ]);
        }

        ActivityLog::record('membership.paid', $member, [
            'reference' => $payment->reference,
            'plan' => $plan,
            'expires_at' => $member->expires_at?->toDateString(),
        ]);
    }

    private function fulfillEbook(Payment $payment, Ebook $ebook): void
    {
        try {
            Mail::to($payment->customer_email)->queue(new EbookDeliveryMail($ebook, $payment));
        } catch (\Throwable $e) {
            Log::warning('Paiement: échec envoi EbookDeliveryMail', ['payment' => $payment->id, 'error' => $e->getMessage()]);
        }

        ActivityLog::record('ebook.purchased', $ebook, [
            'reference' => $payment->reference,
            'email' => $payment->customer_email,
        ]);
    }
}
