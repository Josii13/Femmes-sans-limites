<?php

namespace App\Services;

use App\Mail\EbookDeliveryMail;
use App\Mail\QrCodeMail;
use App\Models\ActivityLog;
use App\Models\Ebook;
use App\Models\Payment;
use App\Models\Registration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PaymentFulfiller
{
    public function __construct(private QrCodeService $qrService) {}

    /**
     * Exécute l'action métier après un paiement réussi, selon le type d'objet payé.
     * Idempotent : ne refait rien si le paiement était déjà honoré.
     */
    public function fulfill(Payment $payment): void
    {
        $payable = $payment->payable;

        if ($payable instanceof Registration) {
            $this->fulfillRegistration($payment, $payable);

            return;
        }

        if ($payable instanceof Ebook) {
            $this->fulfillEbook($payment, $payable);
        }
    }

    private function fulfillRegistration(Payment $payment, Registration $registration): void
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
            Mail::to($registration->email)->queue(new QrCodeMail($registration));
        } catch (\Throwable $e) {
            Log::warning('Paiement: échec envoi QrCodeMail', ['reg' => $registration->id, 'error' => $e->getMessage()]);
        }

        ActivityLog::record('payment.confirmed', $registration->event, [
            'registration_id' => $registration->id,
            'reference' => $payment->reference,
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
