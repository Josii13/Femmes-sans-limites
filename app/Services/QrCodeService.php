<?php

namespace App\Services;

use App\Models\Registration;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    public function generate(string $token, Registration $registration): string
    {
        $path = 'registrations/qrcodes/'.$token.'.svg';

        // Disque PRIVÉ (storage/app) : le QR n'est pas exposé publiquement par URL.
        // Il est envoyé en pièce jointe et téléchargeable via une route admin protégée.
        $qr = QrCode::format('svg')
            ->size(300)
            ->errorCorrection('H')
            ->generate($token);

        Storage::disk('local')->put($path, $qr);

        return $path;
    }
}
