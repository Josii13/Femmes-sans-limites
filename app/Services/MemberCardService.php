<?php

namespace App\Services;

use App\Models\Member;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use Illuminate\Support\Facades\Storage;

class MemberCardService
{
    private int $w = 900;

    private int $h = 560;

    // ── Design configs ──────────────────────────────────────────────────────────

    private function design(string $type): array
    {
        return match ($type) {
            'gold' => [
                'bg1' => [12, 8, 5],
                'bg2' => [26, 18, 5],
                'band_side' => 'top',
                'band_sz' => 5,
                'band_stops' => [[139, 105, 20], [240, 215, 138], [201, 168, 76], [240, 215, 138], [139, 105, 20]],
                'deco' => 'diagonal',
                'accent' => [201, 168, 76],
                'name_c' => [245, 237, 213],
                'prof_c' => [201, 168, 76],
                'loc_c' => [175, 155, 115],
                'badge' => 'GOLD',
                'badge_filled' => false,
                'badge_c' => [201, 168, 76],
                'ring1' => [240, 215, 138],
                'ring2' => [139, 105, 20],
                'double_ring' => false,
                'photo_bg' => [28, 20, 8],
                'init_c' => [201, 168, 76],
                'footer_a' => 40,
                'footer_bc' => [201, 168, 76],
                'num_c' => [201, 168, 76],
                'since_c' => [160, 130, 60],
                'quote' => "L'excellence est un choix\nque tu fais chaque jour.",
                'quote_c' => [235, 210, 130],
                'author_c' => [201, 168, 76],
                'quote_pos' => 'right',
                'chip' => true,
                'logo_text_c' => [201, 168, 76],
            ],
            'premium' => [
                'bg1' => [13, 5, 9],
                'bg2' => [31, 6, 26],
                'band_side' => 'left',
                'band_sz' => 9,
                'band_stops' => [[255, 91, 173], [217, 30, 110], [155, 0, 70]],
                'deco' => 'dots',
                'accent' => [217, 30, 110],
                'name_c' => [255, 255, 255],
                'prof_c' => [255, 91, 173],
                'loc_c' => [200, 160, 180],
                'badge' => 'PREMIUM',
                'badge_filled' => true,
                'badge_c' => [255, 255, 255],
                'ring1' => [255, 91, 173],
                'ring2' => [155, 0, 70],
                'double_ring' => true,
                'photo_bg' => [45, 10, 32],
                'init_c' => [217, 30, 110],
                'footer_a' => 45,
                'footer_bc' => [217, 30, 110],
                'num_c' => [217, 30, 110],
                'since_c' => [180, 140, 165],
                'quote' => "Vous n'etes pas seulement\nmembre, vous etes pionniere.",
                'quote_c' => [255, 200, 225],
                'author_c' => [255, 91, 173],
                'quote_pos' => 'left-top',
                'chip' => false,
                'logo_text_c' => [255, 255, 255],
            ],
            default => [ // standard
                'bg1' => [24, 16, 26],
                'bg2' => [35, 21, 41],
                'band_side' => 'left',
                'band_sz' => 6,
                'band_stops' => [[217, 30, 110], [240, 84, 160], [217, 30, 110]],
                'deco' => 'circles',
                'accent' => [217, 30, 110],
                'name_c' => [255, 255, 255],
                'prof_c' => [217, 30, 110],
                'loc_c' => [185, 170, 190],
                'badge' => 'STANDARD',
                'badge_filled' => false,
                'badge_c' => [220, 210, 228],
                'ring1' => [200, 185, 210],
                'ring2' => [140, 120, 155],
                'double_ring' => false,
                'photo_bg' => [45, 31, 38],
                'init_c' => [217, 30, 110],
                'footer_a' => 40,
                'footer_bc' => [217, 30, 110],
                'num_c' => [200, 185, 210],
                'since_c' => [165, 150, 175],
                'quote' => "Tu es sans limites.\nEt le monde a besoin de toi.",
                'quote_c' => [210, 195, 218],
                'author_c' => [217, 30, 110],
                'quote_pos' => 'right',
                'chip' => false,
                'logo_text_c' => [255, 255, 255],
            ],
        };
    }

    // ── Font discovery ──────────────────────────────────────────────────────────

    private function findFont(array $paths): string
    {
        foreach ($paths as $p) {
            if ($p && file_exists($p)) {
                return $p;
            }
        }

        return '';
    }

    private function bundledFont(string $name): string
    {
        return resource_path('fonts/'.$name);
    }

    private function sansFont(): string
    {
        return $this->findFont([
            $this->bundledFont('sans-regular.ttf'),
            'C:\Windows\Fonts\segoeui.ttf',
            'C:\Windows\Fonts\calibri.ttf',
            'C:\Windows\Fonts\arial.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
        ]);
    }

    private function boldFont(): string
    {
        return $this->findFont([
            $this->bundledFont('sans-bold.ttf'),
            'C:\Windows\Fonts\segoeuib.ttf',
            'C:\Windows\Fonts\calibrib.ttf',
            'C:\Windows\Fonts\arialbd.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
        ]);
    }

    private function serifFont(): string
    {
        return $this->findFont([
            $this->bundledFont('serif-regular.ttf'),
            'C:\Windows\Fonts\georgia.ttf',
            'C:\Windows\Fonts\times.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSerif.ttf',
            '/usr/share/fonts/dejavu/DejaVuSerif.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSerif-Regular.ttf',
        ]);
    }

    private function serifBoldFont(): string
    {
        return $this->findFont([
            $this->bundledFont('serif-bold.ttf'),
            'C:\Windows\Fonts\georgiab.ttf',
            'C:\Windows\Fonts\timesbd.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSerif-Bold.ttf',
            '/usr/share/fonts/dejavu/DejaVuSerif-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSerif-Bold.ttf',
        ]);
    }

    private function serifItalicFont(): string
    {
        return $this->findFont([
            $this->bundledFont('serif-italic.ttf'),
            'C:\Windows\Fonts\georgiai.ttf',
            'C:\Windows\Fonts\timesi.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSerif-Italic.ttf',
            '/usr/share/fonts/dejavu/DejaVuSerif-Italic.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSerif-Italic.ttf',
        ]);
    }

    // ── Color helpers ───────────────────────────────────────────────────────────

    private function col(\GdImage $img, array $rgb): int
    {
        return imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
    }

    /** alpha: 0=opaque … 127=transparent */
    private function colA(\GdImage $img, array $rgb, int $alpha): int
    {
        return imagecolorallocatealpha($img, $rgb[0], $rgb[1], $rgb[2], $alpha);
    }

    // ── Gradient helpers ────────────────────────────────────────────────────────

    private function gradV(\GdImage $img, array $c1, array $c2, int $x, int $y, int $w, int $h): void
    {
        for ($i = 0; $i < $h; $i++) {
            $t = $h > 1 ? $i / ($h - 1) : 0;
            $c = imagecolorallocate($img,
                (int) ($c1[0] + ($c2[0] - $c1[0]) * $t),
                (int) ($c1[1] + ($c2[1] - $c1[1]) * $t),
                (int) ($c1[2] + ($c2[2] - $c1[2]) * $t));
            imagefilledrectangle($img, $x, $y + $i, $x + $w - 1, $y + $i, $c);
        }
    }

    /** Multi-stop gradient along a horizontal or vertical axis */
    private function gradBand(\GdImage $img, array $stops, bool $vertical, int $x, int $y, int $w, int $h): void
    {
        $n = count($stops);
        $len = $vertical ? $h : $w;
        for ($i = 0; $i < $len; $i++) {
            $t = $len > 1 ? $i / ($len - 1) : 0;
            $seg = $t * ($n - 1);
            $idx = min((int) $seg, $n - 2);
            $f = $seg - $idx;
            $c1 = $stops[$idx];
            $c2 = $stops[$idx + 1];
            $c = imagecolorallocate($img,
                (int) ($c1[0] + ($c2[0] - $c1[0]) * $f),
                (int) ($c1[1] + ($c2[1] - $c1[1]) * $f),
                (int) ($c1[2] + ($c2[2] - $c1[2]) * $f));
            if ($vertical) {
                imagefilledrectangle($img, $x, $y + $i, $x + $w - 1, $y + $i, $c);
            } else {
                imagefilledrectangle($img, $x + $i, $y, $x + $i, $y + $h - 1, $c);
            }
        }
    }

    // ── Text helpers ────────────────────────────────────────────────────────────

    private function ttfw(int $size, string $font, string $text): int
    {
        if (! $font || ! $text) {
            return 0;
        }
        $box = @imagettfbbox($size, 0, $font, $text);

        return $box ? abs($box[4] - $box[0]) : (int) (strlen($text) * $size * 0.55);
    }

    private function ttfh(int $size, string $font): int
    {
        if (! $font) {
            return $size;
        }
        $box = @imagettfbbox($size, 0, $font, 'Ag');

        return $box ? abs($box[5] - $box[1]) : $size;
    }

    private function text(\GdImage $img, string $txt, int $x, int $y, int $sz, string $font, array $rgb, string $align = 'left'): void
    {
        if (! $font || ! $txt) {
            return;
        }
        $c = $this->col($img, $rgb);
        if ($align === 'right') {
            $x -= $this->ttfw($sz, $font, $txt);
        }
        if ($align === 'center') {
            $x -= intdiv($this->ttfw($sz, $font, $txt), 2);
        }
        @imagettftext($img, $sz, 0, $x, $y, $c, $font, $txt);
    }

    // ── Main entry ──────────────────────────────────────────────────────────────

    public function generate(Member $member): string
    {
        $d = $this->design($member->type);
        $img = imagecreatetruecolor($this->w, $this->h);
        imagealphablending($img, true);

        $this->drawBackground($img, $d);
        $this->drawBand($img, $d);
        $this->drawDecorations($img, $d);
        $this->drawLogo($img, $d);
        $this->drawBadge($img, $d);
        if ($d['chip']) {
            $this->drawChip($img);
        }
        $this->drawPhoto($img, $member, $d);
        $this->drawMemberInfo($img, $member, $d);
        $this->drawQuote($img, $d);
        $this->drawVerificationQr($img, $member, $d);
        $this->drawFooter($img, $member, $d);

        $dirPath = Storage::disk('public')->path('members/cards');
        if (! is_dir($dirPath)) {
            mkdir($dirPath, 0755, true);
        }

        $filePath = $dirPath.DIRECTORY_SEPARATOR.$member->member_number.'.png';
        imagepng($img, $filePath, 8);
        imagedestroy($img);

        return 'members/cards/'.$member->member_number.'.png';
    }

    // ── Drawing blocks ──────────────────────────────────────────────────────────

    private function drawBackground(\GdImage $img, array $d): void
    {
        $this->gradV($img, $d['bg1'], $d['bg2'], 0, 0, $this->w, $this->h);
    }

    private function drawBand(\GdImage $img, array $d): void
    {
        if ($d['band_side'] === 'left') {
            $this->gradBand($img, $d['band_stops'], true, 0, 0, $d['band_sz'], $this->h);
        } else {
            $this->gradBand($img, $d['band_stops'], false, 0, 0, $this->w, $d['band_sz']);
        }
    }

    private function drawDecorations(\GdImage $img, array $d): void
    {
        $a = $d['accent'];
        switch ($d['deco']) {
            case 'circles':
                $c1 = $this->colA($img, $a, 120);
                $c2 = $this->colA($img, $a, 123);
                imagearc($img, $this->w - 55, $this->h + 45, 390, 390, 0, 360, $c1);
                imagearc($img, $this->w - 55, $this->h + 45, 392, 392, 0, 360, $c1);
                imagearc($img, $this->w + 35, $this->h - 25, 530, 530, 0, 360, $c2);
                imagearc($img, $this->w + 35, $this->h - 25, 532, 532, 0, 360, $c2);
                break;

            case 'diagonal':
                $c = $this->colA($img, $a, 123);
                for ($i = -$this->h; $i < $this->w + $this->h; $i += 28) {
                    imageline($img, $i, 0, $i + $this->h, $this->h, $c);
                    imageline($img, $i + 1, 0, $i + 1 + $this->h, $this->h, $c);
                }
                break;

            case 'dots':
                $c = $this->colA($img, $a, 119);
                for ($x = 310; $x < $this->w - 10; $x += 24) {
                    for ($y = 10; $y < $this->h - 10; $y += 24) {
                        imagefilledrectangle($img, $x, $y, $x + 1, $y + 1, $c);
                    }
                }
                break;
        }
    }

    private function drawLogo(\GdImage $img, array $d): void
    {
        $ox = 36 + ($d['band_side'] === 'left' ? $d['band_sz'] + 10 : 0);
        $oy = 28;
        $boxSz = 44;
        $pad = 5;

        // Cream background square
        $bg = $this->col($img, [253, 240, 245]);
        imagefilledrectangle($img, $ox, $oy, $ox + $boxSz, $oy + $boxSz, $bg);

        // Logo PNG
        $logoPath = public_path('logo_FSL.png');
        if (file_exists($logoPath)) {
            $src = @imagecreatefrompng($logoPath);
            if ($src) {
                $sz = $boxSz - $pad * 2;
                imagecopyresampled($img, $src, $ox + $pad, $oy + $pad, 0, 0, $sz, $sz, imagesx($src), imagesy($src));
                imagedestroy($src);
            }
        }

        // "Femmes Sans Limites" text
        $f = $this->serifFont() ?: $this->sansFont();
        if ($f) {
            $this->text($img, 'Femme Sans Limites', $ox + $boxSz + 12, $oy + 30, 13, $f, $d['logo_text_c']);
        }
    }

    private function drawBadge(\GdImage $img, array $d): void
    {
        $font = $this->boldFont();
        if (! $font) {
            return;
        }

        $label = $d['badge'];
        $sz = 10;
        $tw = $this->ttfw($sz, $font, $label);
        $th = $this->ttfh($sz, $font);
        $padX = 20;
        $padY = 9;
        $bw = $tw + $padX * 2;
        $bh = $th + $padY * 2;
        $bx = $this->w - $bw - 36;
        $by = 30;

        if ($d['badge_filled']) {
            $bg = $this->col($img, $d['accent']);
            imagefilledrectangle($img, $bx, $by, $bx + $bw, $by + $bh, $bg);
        } else {
            $bc = $this->col($img, $d['badge_c']);
            imagerectangle($img, $bx, $by, $bx + $bw, $by + $bh, $bc);
            imagerectangle($img, $bx + 1, $by + 1, $bx + $bw - 1, $by + $bh - 1, $bc);
        }

        $this->text($img, $label, $bx + $padX, $by + $padY + $th, $sz, $font, $d['badge_c']);
    }

    private function drawChip(\GdImage $img): void
    {
        $x = 46;
        $y = 185;
        $cw = 52;
        $ch = 40;
        $this->gradV($img, [232, 206, 90], [184, 148, 28], $x, $y, $cw, $ch);
        $dl = $this->col($img, [90, 70, 10]);
        imagerectangle($img, $x + 7, $y + 6, $x + $cw - 8, $y + $ch - 7, $dl);
        imageline($img, $x + intdiv($cw, 2), $y + 6, $x + intdiv($cw, 2), $y + $ch - 7, $dl);
        imageline($img, $x + 7, $y + intdiv($ch, 2), $x + $cw - 8, $y + intdiv($ch, 2), $dl);
    }

    private function drawPhoto(\GdImage $img, Member $member, array $d): void
    {
        $isPremium = $member->type === 'premium';
        $radius = $isPremium ? 88 : 80;
        $cx = $this->w - ($isPremium ? 148 : 155);
        $cy = intdiv($this->h, 2) - ($isPremium ? 15 : 8);

        // Outer glow ring (premium only)
        if ($d['double_ring']) {
            $outer = $this->col($img, $d['ring1']);
            imagefilledellipse($img, $cx, $cy, ($radius + 10) * 2, ($radius + 10) * 2, $outer);
            $gap = $this->col($img, $d['photo_bg']);
            imagefilledellipse($img, $cx, $cy, ($radius + 5) * 2, ($radius + 5) * 2, $gap);
        }

        // Ring border
        $ring = $this->col($img, $d['ring1']);
        imagefilledellipse($img, $cx, $cy, ($radius + 4) * 2, ($radius + 4) * 2, $ring);

        // Photo or initials
        if ($member->photo && Storage::disk('public')->exists($member->photo)) {
            $this->insertCircularPhoto($img, Storage::disk('public')->path($member->photo), $cx, $cy, $radius);
        } else {
            $bg = $this->col($img, $d['photo_bg']);
            imagefilledellipse($img, $cx, $cy, $radius * 2, $radius * 2, $bg);

            $initials = $this->initials($member->name);
            $font = $this->serifBoldFont() ?: $this->boldFont();
            if ($font && $initials) {
                $fsz = 40;
                $tw = $this->ttfw($fsz, $font, $initials);
                $th = $this->ttfh($fsz, $font);
                $this->text($img, $initials,
                    $cx - intdiv($tw, 2),
                    $cy + intdiv($th, 2),
                    $fsz, $font, $d['init_c']);
            }
        }
    }

    private function insertCircularPhoto(\GdImage $card, string $path, int $cx, int $cy, int $r): void
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $src = match ($ext) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($path),
            'png' => @imagecreatefrompng($path),
            'webp' => @imagecreatefromwebp($path),
            default => null,
        };
        if (! $src) {
            return;
        }

        $size = $r * 2;
        $sw = imagesx($src);
        $sh = imagesy($src);
        $minD = min($sw, $sh);
        $sx = intdiv($sw - $minD, 2);
        $sy = intdiv($sh - $minD, 2);

        $sq = imagecreatetruecolor($size, $size);
        imagecopyresampled($sq, $src, 0, 0, $sx, $sy, $size, $size, $minD, $minD);
        imagedestroy($src);

        $ox = $cx - $r;
        $oy = $cy - $r;
        $r2 = $r * $r;
        // Sur une image truecolor, on peut écrire directement la valeur de couleur
        // retournée par imagecolorat (pas d'allocation par pixel → beaucoup plus rapide).
        for ($x = 0; $x < $size; $x++) {
            $dx = $x - $r;
            $dx2 = $dx * $dx;
            for ($y = 0; $y < $size; $y++) {
                $dy = $y - $r;
                if ($dx2 + $dy * $dy <= $r2) {
                    imagesetpixel($card, $ox + $x, $oy + $y, imagecolorat($sq, $x, $y));
                }
            }
        }
        imagedestroy($sq);
    }

    private function initials(string $name): string
    {
        $r = '';
        foreach (explode(' ', trim($name)) as $p) {
            if ($p !== '') {
                $r .= mb_strtoupper(mb_substr($p, 0, 1));
                if (mb_strlen($r) >= 2) {
                    break;
                }
            }
        }

        return $r;
    }

    private function drawMemberInfo(\GdImage $img, Member $member, array $d): void
    {
        $serifBold = $this->serifBoldFont();
        $sans = $this->sansFont();
        $bold = $this->boldFont();
        $nameFont = $serifBold ?: $bold;

        $ox = 36 + ($d['band_side'] === 'left' ? $d['band_sz'] + 14 : 0);
        $nameY = ($d['quote_pos'] === 'left-top') ? 318 : 255;
        $nameSz = ($member->type === 'premium') ? 30 : 27;

        // Name (uppercase)
        $name = mb_strtoupper($member->name);
        if (mb_strlen($name) > 22) {
            $name = mb_substr($name, 0, 21).'…';
        }

        if ($nameFont) {
            $nc = $this->col($img, $d['name_c']);
            @imagettftext($img, $nameSz, 0, $ox, $nameY, $nc, $nameFont, $name);

            // Thin accent line beneath name
            $lw = min(300, $this->ttfw($nameSz, $nameFont, $name));
            $ac = $this->col($img, $d['accent']);
            imagefilledrectangle($img, $ox, $nameY + 7, $ox + $lw, $nameY + 8, $ac);
        }

        // Profession
        $prof = mb_strtoupper($member->profession ?? '');
        if (mb_strlen($prof) > 32) {
            $prof = mb_substr($prof, 0, 31).'…';
        }
        if ($sans && $prof) {
            $this->text($img, $prof, $ox, $nameY + 44, 13, $sans, $d['prof_c']);
        }

        // Location
        $loc = trim(($member->city ?? '').', '.($member->country ?? ''), ', ');
        if ($sans && $loc) {
            $this->text($img, $loc, $ox, $nameY + 68, 12, $sans, $d['loc_c']);
        }
    }

    private function drawQuote(\GdImage $img, array $d): void
    {
        $qFont = $this->serifItalicFont() ?: $this->serifFont() ?: $this->sansFont();
        $sans = $this->sansFont();
        $sFont = $this->serifFont();
        if (! $qFont) {
            return;
        }

        $lines = explode("\n", $d['quote']);
        $qc = $this->col($img, $d['quote_c']);
        $ac = $this->col($img, $d['author_c']);
        $sz = 12;
        $lh = 20;

        if ($d['quote_pos'] === 'right') {
            // Right-aligned, bottom-right
            $baseY = 368;
            $rightX = $this->w - 36;

            if ($sFont) {
                $qmc = $this->col($img, $d['quote_c']);
                $qmx = $rightX - $this->ttfw(12, $qFont, implode(' ', $lines));
                imagettftext($img, 26, 0, max($rightX - 240, $qmx), $baseY - 6, $qmc, $sFont, '"');
            }

            foreach ($lines as $i => $line) {
                $tw = $this->ttfw($sz, $qFont, $line);
                @imagettftext($img, $sz, 0, $rightX - $tw, $baseY + 22 + $i * $lh, $qc, $qFont, $line);
            }

            $author = '— La Fondatrice';
            $aw = $this->ttfw(10, $sans ?: $qFont, $author);
            @imagettftext($img, 10, 0, $rightX - $aw, $baseY + 22 + count($lines) * $lh + 14, $ac, $sans ?: $qFont, $author);

        } else {
            // Premium: left-top (below logo, above member info)
            $ox = 36 + 9 + 14;
            $qy = 105;

            if ($sFont) {
                $qmc = $this->col($img, $d['accent']);
                @imagettftext($img, 26, 0, $ox, $qy, $qmc, $sFont, '"');
            }

            foreach ($lines as $i => $line) {
                @imagettftext($img, $sz, 0, $ox, $qy + 26 + $i * $lh, $qc, $qFont, $line);
            }

            $author = '— La Fondatrice';
            @imagettftext($img, 10, 0, $ox, $qy + 26 + count($lines) * $lh + 14, $ac, $sans ?: $qFont, $author);
        }
    }

    /**
     * Dessine un QR code (vers la page publique de vérification) en bas à gauche,
     * sur fond blanc pour rester scannable quelle que soit la couleur de la carte.
     * Rendu 100 % GD à partir de la matrice BaconQrCode (pas de dépendance imagick).
     */
    private function drawVerificationQr(\GdImage $img, Member $member, array $d): void
    {
        $url = $member->verification_url;

        try {
            $matrix = Encoder::encode($url, ErrorCorrectionLevel::valueOf('M'))->getMatrix();
        } catch (\Throwable) {
            return;
        }

        $n = $matrix->getWidth();
        if ($n < 1) {
            return;
        }

        $size = 96;                       // taille totale du carré blanc
        $quiet = 7;                       // marge silencieuse
        $module = intdiv($size - 2 * $quiet, $n);
        if ($module < 1) {
            return;
        }
        $drawn = $module * $n;
        $pad = intdiv($size - $drawn, 2);

        $bandOffset = $d['band_side'] === 'left' ? $d['band_sz'] + 10 : 0;
        $qx = 36 + $bandOffset;
        $qy = $this->h - 56 - $size - 14; // au-dessus du footer

        // Fond blanc (scannabilité).
        $white = $this->col($img, [255, 255, 255]);
        imagefilledrectangle($img, $qx, $qy, $qx + $size, $qy + $size, $white);

        // Modules noirs.
        $black = $this->col($img, [17, 10, 12]);
        for ($r = 0; $r < $n; $r++) {
            for ($c = 0; $c < $n; $c++) {
                if ($matrix->get($c, $r) === 1) {
                    $mx = $qx + $pad + $c * $module;
                    $my = $qy + $pad + $r * $module;
                    imagefilledrectangle($img, $mx, $my, $mx + $module - 1, $my + $module - 1, $black);
                }
            }
        }
    }

    private function drawFooter(\GdImage $img, Member $member, array $d): void
    {
        $footH = 56;
        $footY = $this->h - $footH;

        // Semi-transparent dark overlay
        $fc = $this->colA($img, [0, 0, 0], $d['footer_a']);
        imagefilledrectangle($img, 0, $footY, $this->w, $this->h, $fc);

        // Top border line
        $bc = $this->col($img, $d['footer_bc']);
        imagefilledrectangle($img, 0, $footY, $this->w, $footY + 1, $bc);

        $sans = $this->sansFont();
        if (! $sans) {
            return;
        }

        $ty = $footY + 34;
        $ox = 36 + ($d['band_side'] === 'left' ? $d['band_sz'] + 10 : 0);

        // Member number — left
        $this->text($img, $member->member_number, $ox, $ty, 11, $sans, $d['num_c']);

        // Validité — right (à défaut, date d'adhésion)
        $joined = $member->joined_at ?? $member->created_at;
        if ($member->expires_at) {
            $right = 'Valable jusqu\'au '.$member->expires_at->format('m/Y');
        } else {
            $right = 'Membre depuis '.($joined ? $joined->format('M Y') : date('M Y'));
        }
        $this->text($img, $right, $this->w - 36, $ty, 10, $sans, $d['since_c'], 'right');
    }
}
