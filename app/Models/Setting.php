<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Réglage persistant, au format clé/valeur.
 *
 * Volontairement minimal : un seul besoin aujourd'hui, le jeton du lien de
 * collecte des anniversaires.
 */
class Setting extends Model
{
    protected $table = 'app_settings';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public const BIRTHDAY_TOKEN = 'birthday_collect_token';

    public static function get(string $key, ?string $default = null): ?string
    {
        return static::find($key)?->value ?? $default;
    }

    public static function put(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Jeton du lien de collecte, créé à la première demande.
     *
     * 32 caractères : le lien circule sur WhatsApp et finira par sortir du
     * groupe. Il ne protège pas un secret d'État, mais il doit empêcher qu'on
     * tombe par hasard sur l'annuaire des membres.
     */
    public static function birthdayToken(): string
    {
        $token = static::get(self::BIRTHDAY_TOKEN);

        if (! $token) {
            $token = Str::lower(Str::random(32));
            static::put(self::BIRTHDAY_TOKEN, $token);
        }

        return $token;
    }

    public static function rotateBirthdayToken(): string
    {
        $token = Str::lower(Str::random(32));
        static::put(self::BIRTHDAY_TOKEN, $token);

        return $token;
    }
}
