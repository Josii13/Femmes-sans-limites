<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'member_number', 'name', 'email', 'phone', 'motivation', 'profession',
        'country', 'city', 'photo', 'type', 'status', 'card_path',
    ];

    public function registrations()
    {
        return $this->hasMany(\App\Models\Registration::class, 'email', 'email');
    }

    public function getBadgeColorAttribute(): string
    {
        return match($this->type) {
            'premium' => '#D91E6E',
            'gold'    => '#C9A84C',
            default   => '#6B7280',
        };
    }

    public function getPrivilegesListAttribute(): array
    {
        return match($this->type) {
            'premium' => [
                'Accès VIP à tous les événements',
                'Réduction de 30% sur tous les events payants',
                'Ressources exclusives & formations premium',
                'Accès prioritaire aux panels et ateliers',
                'Badge Premium + carte personnalisée',
            ],
            'gold' => [
                'Réduction de 15% sur les events payants',
                'Accès prioritaire aux inscriptions',
                'Ressources formations avancées',
                'Badge Gold + carte personnalisée',
            ],
            default => [
                'Accès aux événements publics',
                'Newsletter & actualités FSL',
                'Badge Standard + carte personnalisée',
            ],
        };
    }
}
