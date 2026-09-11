<?php

namespace App\Services;

/**
 * Double authentification par code temporaire (TOTP, RFC 6238).
 *
 * Implémenté ici plutôt que via une dépendance : l'algorithme tient en quelques
 * dizaines de lignes, il est figé par la norme, et cela évite d'ajouter un paquet
 * à maintenir sur un hébergement mutualisé. Compatible avec Google Authenticator,
 * Authy, 1Password et les autres.
 */
class TotpService
{
    /** Durée de validité d'un code, en secondes (valeur standard). */
    private const PERIOD = 30;

    private const DIGITS = 6;

    /**
     * Tolérance de part et d'autre de la période courante : absorbe le décalage
     * d'horloge entre le téléphone et le serveur, sans ouvrir une fenêtre trop large.
     */
    private const WINDOW = 1;

    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /** Secret partagé, en base32 — le format attendu par les applications d'authentification. */
    public function generateSecret(int $bytes = 20): string
    {
        return $this->base32Encode(random_bytes($bytes));
    }

    /**
     * URI otpauth:// à encoder dans le QR code d'enrôlement.
     */
    public function provisioningUri(string $secret, string $account, string $issuer): string
    {
        return 'otpauth://totp/'.rawurlencode($issuer).':'.rawurlencode($account).'?'.http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => self::DIGITS,
            'period' => self::PERIOD,
        ]);
    }

    /**
     * Vérifie un code saisi par l'utilisateur.
     *
     * La comparaison passe par hash_equals : une comparaison naïve laisserait
     * fuir de l'information par le temps d'exécution.
     */
    public function verify(string $secret, string $code): bool
    {
        $code = preg_replace('/\D/', '', $code) ?? '';

        if (strlen($code) !== self::DIGITS) {
            return false;
        }

        $counter = (int) floor(time() / self::PERIOD);

        for ($offset = -self::WINDOW; $offset <= self::WINDOW; $offset++) {
            if (hash_equals($this->codeAt($secret, $counter + $offset), $code)) {
                return true;
            }
        }

        return false;
    }

    /** Code attendu pour un compteur donné — exposé pour les tests. */
    public function codeAt(string $secret, ?int $counter = null): string
    {
        $counter ??= (int) floor(time() / self::PERIOD);

        $key = $this->base32Decode($secret);
        $hash = hash_hmac('sha1', pack('J', $counter), $key, true);

        // Troncature dynamique : les 4 derniers bits désignent l'octet de départ.
        $offset = ord($hash[19]) & 0x0F;
        $value = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        ) % (10 ** self::DIGITS);

        return str_pad((string) $value, self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Codes de secours, à usage unique.
     *
     * Sans eux, un téléphone perdu ou réinitialisé enfermerait définitivement
     * l'administratrice hors du back-office.
     *
     * @return array<int, string>
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        return collect(range(1, $count))
            ->map(fn () => strtoupper(bin2hex(random_bytes(2)).'-'.bin2hex(random_bytes(2))))
            ->all();
    }

    private function base32Encode(string $binary): string
    {
        $bits = '';
        foreach (str_split($binary) as $char) {
            $bits .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $output = '';
        foreach (str_split($bits, 5) as $chunk) {
            $output .= self::ALPHABET[bindec(str_pad($chunk, 5, '0', STR_PAD_RIGHT))];
        }

        return $output;
    }

    private function base32Decode(string $secret): string
    {
        $secret = strtoupper(rtrim($secret, '='));
        $bits = '';

        foreach (str_split($secret) as $char) {
            $index = strpos(self::ALPHABET, $char);
            if ($index === false) {
                continue; // caractère hors alphabet : ignoré plutôt que de faire échouer
            }
            $bits .= str_pad(decbin($index), 5, '0', STR_PAD_LEFT);
        }

        $binary = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $binary .= chr(bindec($byte));
            }
        }

        return $binary;
    }
}
