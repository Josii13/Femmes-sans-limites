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

        Storage::disk('public')->makeDirectory('registrations/qrcodes');

        $qr = QrCode::format('svg')
            ->size(300)
            ->errorCorrection('H')
            ->generate($token);

        Storage::disk('public')->put($path, $qr);

        return $path;
    }
}
