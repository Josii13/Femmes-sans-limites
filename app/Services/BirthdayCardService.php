<?php

namespace App\Services;

use App\Models\Member;
use App\Services\Concerns\DrawsCards;
use Illuminate\Support\Facades\Storage;

/**
 * Carte d'anniversaire personnalisée.
 *
 * Format carré 1080×1080 : c'est celui d'un statut WhatsApp et d'une
 * publication Instagram. Une carte au format carte de visite serait rognée
 * de travers à la première reprise — or c'est justement le partage qui fait
 * tout l'intérêt du geste.
 *
 * Le fond reprend la palette du niveau d'adhésion, comme la carte de membre,
 * pour que les deux objets se reconnaissent comme venant de la même maison.
 */
class BirthdayCardService
{
    use DrawsCards;

    private int $size = 1080;

    /** @return array{bg1: int[], bg2: int[], accent: int[], name: int[], soft: int[]} */
    private function palette(string $type): array
    {
        return match ($type) {
            'gold' => [
                'bg1' => [12, 8, 5], 'bg2' => [38, 26, 8],
                'accent' => [201, 168, 76], 'name' => [245, 237, 213], 'soft' => [175, 155, 115],
            ],
            'premium' => [
                'bg1' => [16, 6, 12], 'bg2' => [52, 10, 32],
                'accent' => [232, 120, 170], 'name' => [255, 240, 248], 'soft' => [196, 150, 175],
            ],
            default => [
                'bg1' => [26, 10, 16], 'bg2' => [92, 18, 54],
                'accent' => [217, 30, 110], 'name' => [255, 255, 255], 'soft' => [224, 180, 200],
            ],
        };
    }

    public function generate(Member $member): string
    {
        $p = $this->palette((string) $member->type);
        $img = imagecreatetruecolor($this->size, $this->size);
        imagealphablending($img, true);

        $this->gradV($img, $p['bg1'], $p['bg2'], 0, 0, $this->size, $this->size);
        $this->drawConfetti($img, $p);
        $this->drawPhoto($img, $member, $p);
        $this->drawWords($img, $member, $p);
        $this->drawSignature($img, $p);

        $dir = Storage::disk('public')->path('members/birthdays');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Le nom de fichier porte l'année : la carte de l'an prochain ne doit
        // pas écraser celle que la membre a peut-être encore en partage.
        $name = 'members/birthdays/'.$member->member_number.'-'.now()->year.'.png';
        imagepng($img, Storage::disk('public')->path($name), 8);
        imagedestroy($img);

        return $name;
    }

    /**
     * Confettis : des disques et des anneaux semés sur le fond.
     *
     * La graine dérive du numéro de membre, donc le motif est stable d'une
     * génération à l'autre — une carte régénérée reste la même image.
     */
    private function drawConfetti(\GdImage $img, array $p): void
    {
        mt_srand(crc32((string) $p['accent'][0]) % 1000);

        for ($i = 0; $i < 70; $i++) {
            $x = mt_rand(0, $this->size);
            $y = mt_rand(0, $this->size);
            $r = mt_rand(3, 11);
            $alpha = mt_rand(75, 110);
            $c = $this->colA($img, mt_rand(0, 1) ? $p['accent'] : $p['name'], $alpha);

            if (mt_rand(0, 2) === 0) {
                imagesetthickness($img, 2);
                imageellipse($img, $x, $y, $r * 2, $r * 2, $c);
                imagesetthickness($img, 1);
            } else {
                imagefilledellipse($img, $x, $y, $r, $r, $c);
            }
        }

        mt_srand();
    }

    private function drawPhoto(\GdImage $img, Member $member, array $p): void
    {
        $cx = intdiv($this->size, 2);
        $cy = 340;
        $radius = 150;

        $ring = $this->col($img, $p['accent']);
        imagefilledellipse($img, $cx, $cy, ($radius + 8) * 2, ($radius + 8) * 2, $ring);

        if ($member->photo && Storage::disk('public')->exists($member->photo)) {
            $this->insertCircularPhoto($img, Storage::disk('public')->path($member->photo), $cx, $cy, $radius);

            return;
        }

        // Pas de photo : les initiales, dans le ton du fond pour rester lisibles.
        imagefilledellipse($img, $cx, $cy, $radius * 2, $radius * 2, $this->col($img, $p['bg1']));
        $font = $this->serifBoldFont() ?: $this->boldFont();
        $initials = $this->initials($member->name);
        if ($font && $initials) {
            $this->text($img, $initials, $cx, $cy + 34, 84, $font, $p['accent'], 'center');
        }
    }

    private function drawWords(\GdImage $img, Member $member, array $p): void
    {
        $cx = intdiv($this->size, 2);
        $serif = $this->serifBoldFont() ?: $this->boldFont();
        $sans = $this->sansFont();
        $bold = $this->boldFont();

        $this->text($img, 'JOYEUX ANNIVERSAIRE', $cx, 600, 30, $bold ?: $serif, $p['accent'], 'center');

        // Le prénom seul : une carte à partager s'adresse à une personne, pas
        // à une ligne d'annuaire.
        $first = explode(' ', trim($member->name))[0] ?: $member->name;
        $size = mb_strlen($first) > 10 ? 76 : 96;
        $this->text($img, $first, $cx, 710, $size, $serif, $p['name'], 'center');

        $age = $member->ageOn();
        if ($age !== null && $age > 0) {
            $this->text($img, $age.' ans', $cx, 775, 34, $sans ?: $bold, $p['soft'], 'center');
        }

        $lines = [
            'Toute la communauté te souhaite',
            'une année à ta hauteur.',
        ];
        $y = $age !== null && $age > 0 ? 850 : 820;
        foreach ($lines as $i => $line) {
            $this->text($img, $line, $cx, $y + $i * 44, 28, $sans ?: $bold, $p['soft'], 'center');
        }
    }

    private function drawSignature(\GdImage $img, array $p): void
    {
        $cx = intdiv($this->size, 2);
        $serif = $this->serifBoldFont() ?: $this->boldFont();

        $line = $this->colA($img, $p['accent'], 90);
        imagefilledrectangle($img, $cx - 90, 975, $cx + 90, 976, $line);

        $this->text($img, 'Femme Sans Limites', $cx, 1025, 26, $serif, $p['accent'], 'center');
    }
}
