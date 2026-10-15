<?php

/**
 * Régénère public/images/payments à partir des fichiers de marque voisins.
 * Lancer depuis la racine du projet : php resources/images/payments/build-logos.php
 *
 * Prépare les logos de paiement : on rogne la marge inutile autour du dessin,
 * on garde la couleur de fond de la marque (le lettrage d'Orange Money est
 * blanc : sans son carré noir il disparaîtrait) et on ramène le tout à une
 * hauteur commune. 64 px pour rester net sur les écrans 2x et 3x.
 */
const H = 64;

function load(string $p)
{
    $im = str_ends_with(strtolower($p), '.jpg') ? imagecreatefromjpeg($p) : imagecreatefrompng($p);
    imagepalettetotruecolor($im);
    imagealphablending($im, false);
    imagesavealpha($im, true);

    return $im;
}

/** Boîte englobante des pixels qui diffèrent du coin haut-gauche. */
function bbox($im, int $tol = 26): array
{
    $w = imagesx($im);
    $h = imagesy($im);
    $bg = imagecolorat($im, 0, 0);
    [$br, $bgr, $bb, $ba] = [($bg >> 16) & 0xFF, ($bg >> 8) & 0xFF, $bg & 0xFF, ($bg >> 24) & 0x7F];
    $x0 = $w;
    $y0 = $h;
    $x1 = -1;
    $y1 = -1;
    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            $c = imagecolorat($im, $x, $y);
            $a = ($c >> 24) & 0x7F;
            $diff = abs((($c >> 16) & 0xFF) - $br) + abs((($c >> 8) & 0xFF) - $bgr) + abs(($c & 0xFF) - $bb) + abs($a - $ba) * 4;
            if ($diff > $tol) {
                if ($x < $x0) {
                    $x0 = $x;
                } if ($x > $x1) {
                    $x1 = $x;
                }
                if ($y < $y0) {
                    $y0 = $y;
                } if ($y > $y1) {
                    $y1 = $y;
                }
            }
        }
    }

    return $x1 < 0 ? [0, 0, $w - 1, $h - 1] : [$x0, $y0, $x1, $y1];
}

function process(string $src, string $dst, bool $trim, int $pad): void
{
    $im = load($src);
    $w = imagesx($im);
    $h = imagesy($im);

    if ($trim) {
        [$x0, $y0, $x1, $y1] = bbox($im);
        $x0 = max(0, $x0 - $pad);
        $y0 = max(0, $y0 - $pad);
        $x1 = min($w - 1, $x1 + $pad);
        $y1 = min($h - 1, $y1 + $pad);
    } else {
        [$x0, $y0, $x1, $y1] = [0, 0, $w - 1, $h - 1];
    }
    $cw = $x1 - $x0 + 1;
    $ch = $y1 - $y0 + 1;

    $ow = max(1, (int) round($cw * H / $ch));
    $out = imagecreatetruecolor($ow, H);
    imagealphablending($out, false);
    imagesavealpha($out, true);
    imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));
    imagecopyresampled($out, $im, 0, 0, $x0, $y0, $ow, H, $cw, $ch);
    imagepng($out, $dst, 9);

    printf("%-34s %4dx%-3d  %5.1f Ko\n", basename($dst), $ow, H, filesize($dst) / 1024);
}

// Wave : le carré cyan fait partie du logo, on ne le rogne pas.
process(__DIR__.'/wave.png', __DIR__.'/../../../public/images/payments/wave.png', false, 0);
// Orange Money / MTN : grandes marges autour d'un bloc centré.
process(__DIR__.'/orange-money.png', __DIR__.'/../../../public/images/payments/orange-money.png', true, 16);
process(__DIR__.'/mtn-momo.jpg', __DIR__.'/../../../public/images/payments/mtn-momo.png', true, 20);
// Mastercard : fond transparent, on serre au dessin.
process(__DIR__.'/mastercard.png', __DIR__.'/../../../public/images/payments/mastercard.png', true, 2);
