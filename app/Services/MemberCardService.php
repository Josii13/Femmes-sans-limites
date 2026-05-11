<?php

namespace App\Services;

use App\Models\Member;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MemberCardService
{
    private int $width  = 900;
    private int $height = 560;

    // Windows system fonts (fallbacks)
    private function fontPath(): string
    {
        $candidates = [
            'C:\Windows\Fonts\arial.ttf',
            'C:\Windows\Fonts\calibri.ttf',
            'C:\Windows\Fonts\segoeui.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
        ];
        foreach ($candidates as $path) {
            if (file_exists($path)) return $path;
        }
        return '';
    }

    private function boldFontPath(): string
    {
        $candidates = [
            'C:\Windows\Fonts\arialbd.ttf',
            'C:\Windows\Fonts\calibrib.ttf',
            'C:\Windows\Fonts\segoeuib.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
        ];
        foreach ($candidates as $path) {
            if (file_exists($path)) return $path;
        }
        return $this->fontPath();
    }

    public function generate(Member $member): string
    {
        $manager = new ImageManager(new Driver());
        $font    = $this->fontPath();
        $bold    = $this->boldFontPath();

        $stripeColor = match($member->type) {
            'premium' => 'D91E6E',
            'gold'    => 'C9A84C',
            default   => '8B96A0',
        };

        $bgColor = match($member->type) {
            'premium' => '1A0A10',
            'gold'    => '100E00',
            default   => '0D1418',
        };

        // Create canvas
        $img = $manager->createImage($this->width, $this->height);
        $img->fill('#'.$bgColor);

        // Left accent stripe
        $img->drawRectangle(function($draw) use ($stripeColor) {
            $draw->at(0, 0)->size(8, $this->height)->background('#'.$stripeColor);
        });

        // Decorative top-right circle
        $img->drawCircle(function($draw) use ($stripeColor) {
            $draw->at($this->width - 40, -40)->radius(160)->background('#'.$stripeColor.'18');
        });

        // Decorative bottom-left circle
        $img->drawCircle(function($draw) use ($stripeColor) {
            $draw->at(40, $this->height + 40)->radius(130)->background('#'.$stripeColor.'10');
        });

        // Bottom bar
        $img->drawRectangle(function($draw) use ($stripeColor) {
            $draw->at(0, $this->height - 60)->size($this->width, 60)->background('#'.$stripeColor.'25');
        });

        // Separator line
        $img->drawRectangle(function($draw) use ($stripeColor) {
            $draw->at(40, 295)->size(460, 2)->background('#'.$stripeColor.'55');
        });

        // Photo area (right side)
        $photoX    = $this->width - 210;
        $photoY    = 50;
        $photoSize = 190;

        // Photo border circle
        $img->drawCircle(function($draw) use ($stripeColor, $photoX, $photoY, $photoSize) {
            $cx = $photoX + intval($photoSize / 2);
            $cy = $photoY + intval($photoSize / 2);
            $draw->at($cx, $cy)->radius(intval($photoSize / 2) + 5)->background('#'.$stripeColor.'44');
        });

        // Member photo or initials background
        if ($member->photo && Storage::disk('public')->exists($member->photo)) {
            $photoPath = Storage::disk('public')->path($member->photo);
            $photo = $manager->decodePath($photoPath)->coverDown($photoSize, $photoSize);
            $img->insert($photo, $photoX, $photoY);
        } else {
            // Initials circle placeholder
            $img->drawCircle(function($draw) use ($stripeColor, $photoX, $photoY, $photoSize) {
                $cx = $photoX + intval($photoSize / 2);
                $cy = $photoY + intval($photoSize / 2);
                $draw->at($cx, $cy)->radius(intval($photoSize / 2))->background('#'.$stripeColor.'33');
            });
        }

        // Logo (top left)
        $logoPath = public_path('logo_FSL.png');
        if (file_exists($logoPath)) {
            $logo = $manager->decodePath($logoPath)->scale(width: 100);
            $img->insert($logo, 28, 18);
        }

        // Type badge pill background
        $img->drawRectangle(function($draw) use ($stripeColor) {
            $draw->at(30, 145)->size(160, 32)->background('#'.$stripeColor.'BB');
        });

        if ($font) {
            // Type badge text
            $badgeText = strtoupper($member->type);
            $img->text($badgeText, 110, 167, function($f) use ($bold) {
                $f->file($bold)->size(13)->color('#FFFFFF')->align('center', 'center');
            });

            // Member name
            $img->text(mb_strtoupper($member->name), 40, 220, function($f) use ($bold) {
                $f->file($bold)->size(30)->color('#FFFFFF')->align('left', 'top');
            });

            // Profession
            $img->text($member->profession, 40, 265, function($f) use ($font, $stripeColor) {
                $f->file($font)->size(16)->color('#'.$stripeColor)->align('left', 'top');
            });

            // Location
            $img->text($member->city.', '.$member->country, 40, 315, function($f) use ($font) {
                $f->file($font)->size(14)->color('#AAAAAA')->align('left', 'top');
            });

            // Member number
            $img->text($member->member_number, 40, 345, function($f) use ($font) {
                $f->file($font)->size(12)->color('#888888')->align('left', 'top');
            });

            // Since
            $since = 'Membre depuis '.($member->created_at ? $member->created_at->format('M Y') : date('M Y'));
            $img->text($since, 40, 370, function($f) use ($font) {
                $f->file($font)->size(12)->color('#666666')->align('left', 'top');
            });

            // Bottom branding
            $img->text('FEMMES SANS LIMITES', $this->width / 2, $this->height - 30, function($f) use ($bold) {
                $f->file($bold)->size(15)->color('#FFFFFF')->align('center', 'center');
            });

            $img->text('femmessanslimites.com', $this->width / 2, $this->height - 13, function($f) use ($font) {
                $f->file($font)->size(10)->color('#666666')->align('center', 'center');
            });
        }

        // Save PNG
        $dirPath = Storage::disk('public')->path('members/cards');
        if (!is_dir($dirPath)) mkdir($dirPath, 0755, true);

        $filePath = $dirPath.DIRECTORY_SEPARATOR.$member->member_number.'.png';
        $img->save($filePath);

        return 'members/cards/'.$member->member_number.'.png';
    }
}
