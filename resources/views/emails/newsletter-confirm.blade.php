<x-mail::message>
<div style="text-align:center;margin-bottom:24px;">
<img src="{{ asset('logo-email.png') }}" alt="FSL" style="height:48px;width:auto;">
</div>

# Plus qu'une étape ✨

Bonjour {{ $subscriber->name ?? 'chère future membre' }},

Merci de ton intérêt pour **Femme Sans Limites** ! Pour finaliser ton abonnement à notre newsletter et commencer à recevoir nos actualités, événements et ressources, confirme simplement ton adresse email :

<x-mail::button :url="route('newsletter.confirm', $subscriber->token)" color="primary">
Confirmer mon abonnement →
</x-mail::button>

Si tu n'es pas à l'origine de cette demande, tu peux ignorer cet email — aucun message ne te sera envoyé sans cette confirmation.

Avec amour,
**L'équipe Femme Sans Limites**
</x-mail::message>
