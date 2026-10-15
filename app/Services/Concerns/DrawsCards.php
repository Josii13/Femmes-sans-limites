<?php

namespace App\Services\Concerns;

/**
 * Primitives de dessin GD partagées par les images générées (carte de membre,
 * carte d'anniversaire) : polices, couleurs, dégradés, texte, photo ronde.
 *
 * Extraites de MemberCardService quand la carte d'anniversaire est arrivée :
 * les recopier aurait signifié corriger deux fois le moindre défaut de rendu.
 */
trait DrawsCards
{
    protected function findFont(array $paths): string
    {
        foreach ($paths as $p) {
            if ($p && file_exists($p)) {
                return $p;
            }
        }

        return '';
    }

    protected function bundledFont(string $name): string
    {
        return resource_path('fonts/'.$name);
    }

    protected function sansFont(): string
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

    protected function boldFont(): string
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

    protected function serifFont(): string
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

    protected function serifBoldFont(): string
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

    protected function serifItalicFont(): string
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

    protected function col(\GdImage $img, array $rgb): int
    {
        return imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
    }

    /** alpha: 0=opaque … 127=transparent */
    protected function colA(\GdImage $img, array $rgb, int $alpha): int
    {
        return imagecolorallocatealpha($img, $rgb[0], $rgb[1], $rgb[2], $alpha);
    }

    // ── Gradient helpers ────────────────────────────────────────────────────────

    protected function gradV(\GdImage $img, array $c1, array $c2, int $x, int $y, int $w, int $h): void
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
    protected function gradBand(\GdImage $img, array $stops, bool $vertical, int $x, int $y, int $w, int $h): void
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

    protected function ttfw(int $size, string $font, string $text): int
    {
        if (! $font || ! $text) {
            return 0;
        }
        $box = @imagettfbbox($size, 0, $font, $text);

        return $box ? abs($box[4] - $box[0]) : (int) (strlen($text) * $size * 0.55);
    }

    protected function ttfh(int $size, string $font): int
    {
        if (! $font) {
            return $size;
        }
        $box = @imagettfbbox($size, 0, $font, 'Ag');

        return $box ? abs($box[5] - $box[1]) : $size;
    }

    protected function text(\GdImage $img, string $txt, int $x, int $y, int $sz, string $font, array $rgb, string $align = 'left'): void
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

    protected function insertCircularPhoto(\GdImage $card, string $path, int $cx, int $cy, int $r): void
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

    protected function initials(string $name): string
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
}
