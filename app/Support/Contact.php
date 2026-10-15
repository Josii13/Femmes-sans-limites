<?php

namespace App\Support;

/**
 * Coordonnées publiques, dans les formes attendues par le HTML.
 *
 * Un numéro de téléphone s'écrit de trois façons selon l'endroit : lisible
 * pour l'œil, compact pour `tel:`, sans le « + » pour wa.me. Les recopier à la
 * main garantissait qu'une des trois finirait par diverger — c'est déjà arrivé,
 * le pied de page pointait vers un numéro fictif.
 */
final class Contact
{
    /** Format international compact : +2250705282518 */
    public static function phone(): string
    {
        return (string) config('contact.phone');
    }

    /** Lisible : +225 07 05 28 25 18 */
    public static function phoneDisplay(): string
    {
        $raw = preg_replace('/\D+/', '', self::phone());

        // L'indicatif ivoirien se détache, le reste se lit deux par deux.
        if (str_starts_with($raw, '225')) {
            return '+225 '.trim(chunk_split(substr($raw, 3), 2, ' '));
        }

        return self::phone();
    }

    public static function tel(): string
    {
        return 'tel:'.self::phone();
    }

    /** wa.me n'accepte que les chiffres, sans le « + ». */
    public static function whatsapp(?string $message = null): string
    {
        $url = 'https://wa.me/'.preg_replace('/\D+/', '', self::phone());

        return $message ? $url.'?text='.rawurlencode($message) : $url;
    }
}
