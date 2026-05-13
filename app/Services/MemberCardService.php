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

        // Load the branded template image for the member type
        $templatePath = public_path('images/FSL_carte_' . $member->type . '.png');
        if (!file_exists($templatePath)) {
            $templatePath = public_path('images/FSL_carte_standard.png');
        }

        $img = $manager->decodePath($templatePath)->coverDown($this->width, $this->height);

        $accentColor = match($member->type) {
            'premium' => 'D91E6E',
            'gold'    => 'C9A84C',
            default   => '8B96A0',
        };

        // ── Member photo (right side circular area) ──
        $photoX    = $this->width - 220;
        $photoY    = 55;
        $photoSize = 185;

        if ($member->photo && Storage::disk('public')->exists($member->photo)) {
            $photoPath = Storage::disk('public')->path($member->photo);
            $photo = $manager->decodePath($photoPath)->coverDown($photoSize, $photoSize);
            $img->insert($photo, $photoX, $photoY);
        } else {
            // Initials placeholder when no photo
            $img->drawCircle(function ($draw) use ($accentColor, $photoX, $photoY, $photoSize) {
                $cx = $photoX + intval($photoSize / 2);
                $cy = $photoY + intval($photoSize / 2);
                $draw->at($cx, $cy)->radius(intval($photoSize / 2))->background('#' . $accentColor . '55');
            });
        }

        if ($font) {
            // ── Member name ──
            $name = mb_strtoupper($member->name);
            // Truncate long names to avoid overflow
            if (mb_strlen($name) > 22) {
                $name = mb_substr($name, 0, 21) . '…';
            }
            $img->text($name, 50, 210, function ($f) use ($bold) {
                $f->file($bold)->size(27)->color('#FFFFFF')->align('left', 'top');
            });

            // ── Profession ──
            $profession = $member->profession;
            if (mb_strlen($profession) > 30) {
                $profession = mb_substr($profession, 0, 29) . '…';
            }
            $img->text($profession, 50, 258, function ($f) use ($font, $accentColor) {
                $f->file($font)->size(15)->color('#' . $accentColor)->align('left', 'top');
            });

            // ── Location ──
            $img->text($member->city . ', ' . $member->country, 50, 302, function ($f) use ($font) {
                $f->file($font)->size(13)->color('#AAAAAA')->align('left', 'top');
            });

            // ── Member number ──
            $img->text($member->member_number, 50, 332, function ($f) use ($font) {
                $f->file($font)->size(12)->color('#888888')->align('left', 'top');
            });

            // ── Since date ──
            $since = 'Membre depuis ' . ($member->created_at ? $member->created_at->format('M Y') : date('M Y'));
            $img->text($since, 50, 356, function ($f) use ($font) {
                $f->file($font)->size(12)->color('#666666')->align('left', 'top');
            });
        }

        // ── Save PNG ──
        $dirPath = Storage::disk('public')->path('members/cards');
        if (!is_dir($dirPath)) mkdir($dirPath, 0755, true);

        $filePath = $dirPath . DIRECTORY_SEPARATOR . $member->member_number . '.png';
        $img->save($filePath);

        return 'members/cards/' . $member->member_number . '.png';
    }
}
