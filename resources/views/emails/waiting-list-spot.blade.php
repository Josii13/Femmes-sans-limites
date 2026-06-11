<x-mail::message>
<div style="text-align:center;margin-bottom:24px;">
<img src="{{ asset('logo-email.png') }}" alt="FSL" style="height:48px;width:auto;">
</div>

# Une place s'est libérée ! 🎉

Bonjour {{ $entry->first_name }},

Bonne nouvelle ! Une place vient de se libérer pour l'événement auquel tu souhaitais participer :

## {{ $event->title }}

**📅 Date :** {{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('l d F Y à H\hi') }}

**📍 Lieu :** {{ $event->location }}{{ $event->city ? ', '.$event->city : '' }}

Les places étant limitées, nous t'invitons à confirmer ton inscription au plus vite — premier arrivé, première servie !

<x-mail::button :url="route('events.show', $event->slug)" color="primary">
Je confirme ma place →
</x-mail::button>

À très bientôt,
**L'équipe Femme Sans Limites**
</x-mail::message>
