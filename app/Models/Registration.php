<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id', 'first_name', 'last_name', 'email', 'phone',
        'status', 'qr_token', 'qr_code_path',
        'payment_sent_at', 'paid_at', 'attended_at',
    ];

    protected $casts = [
        'payment_sent_at' => 'datetime',
        'paid_at' => 'datetime',
        'attended_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /** Dernier paiement en ligne associé (le plus récent). */
    public function latestPayment()
    {
        return $this->morphOne(Payment::class, 'payable')->latestOfMany();
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }

    /**
     * Libellé du statut, tel qu'il doit être lu par un humain.
     *
     * En interne, « paid » signifie « accès confirmé » : c'est le statut que
     * reçoit aussi une inscription à un événement GRATUIT, puisque c'est lui qui
     * déclenche le QR et autorise le scan. L'afficher « Payé » sur un événement
     * sans tarif laissait croire à un encaissement qui n'a jamais eu lieu.
     *
     * L'événement est passé en argument quand l'appelant l'a déjà sous la main,
     * pour éviter une requête par ligne dans les listes.
     */
    public function statusLabel(?Event $event = null): string
    {
        $event ??= $this->event;
        $isPaidEvent = $event?->isPaidEvent() ?? false;

        return match ($this->status) {
            'pending' => $isPaidEvent ? 'Paiement en attente' : 'En attente',
            'payment_sent' => 'Lien de paiement envoyé',
            'paid' => $isPaidEvent ? 'Payé' : 'Confirmée',
            'attended' => 'Présente',
            'cancelled' => 'Annulée',
            default => $this->status,
        };
    }

    /**
     * Libellés des cartes de filtre, qui comptent des inscriptions et non une
     * seule : « Payées » / « Confirmées » selon que l'événement a un tarif.
     *
     * @return array<string, string>
     */
    public static function statusLabels(?Event $event = null): array
    {
        $isPaidEvent = $event?->isPaidEvent() ?? false;

        return [
            'pending' => $isPaidEvent ? 'Paiement en attente' : 'En attente',
            'payment_sent' => 'Lien envoyé',
            'paid' => $isPaidEvent ? 'Payées' : 'Confirmées',
            'attended' => 'Présentes',
            'cancelled' => 'Annulées',
        ];
    }
}
