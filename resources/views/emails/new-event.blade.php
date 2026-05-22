<x-mail::message>
<div style="text-align:center;margin-bottom:24px;">
<img src="{{ asset('logo_FSL.png') }}" alt="FSL" style="height:48px;width:auto;">
</div>

# Nouvel événement FSL 🎉

Bonjour {{ $subscriber->name ?? 'chère membre' }},

Un nouvel événement vient d'être publié sur la plateforme **Femme Sans Limites** !

---

## {{ $event->title }}

@if($event->short_description)
{{ $event->short_description }}
@endif

**📅 Date :** {{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('l d F Y à H\hi') }}

**📍 Lieu :** {{ $event->location }}{{ $event->city ? ', ' . $event->city : '' }}

@if($event->is_paid && $event->price)
**💰 Tarif :** {{ number_format($event->price, 0, ',', ' ') }} {{ $event->currency ?? 'FCFA' }}
@else
**🎁 Entrée :** Gratuite
@endif

<x-mail::button :url="route('events.show', $event->slug)" color="success">
Voir l'événement →
</x-mail::button>

---

*Tu reçois cet email car tu es abonnée à la newsletter FSL.*
*[Se désabonner]({{ route('newsletter.unsubscribe', $subscriber->token) }})*

Avec amour,
**L'équipe Femme Sans Limites**
</x-mail::message>
