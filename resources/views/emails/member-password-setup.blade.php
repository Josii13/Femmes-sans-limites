<x-mail::message>
# Bonjour {{ $member->first_name ?? explode(' ', $member->name)[0] }},

@if($isFirstTime)
Votre adhésion à **Femme Sans Limites** est désormais active. Votre espace membre
vous attend : carte de membre, inscriptions aux événements, ebooks achetés et
annuaire de la communauté.

Choisissez votre mot de passe pour y accéder :
@else
Vous avez demandé à réinitialiser le mot de passe de votre espace membre.
Cliquez ci-dessous pour en choisir un nouveau :
@endif

<x-mail::button :url="$url" color="primary">
{{ $isFirstTime ? 'Activer mon espace' : 'Choisir un nouveau mot de passe' }}
</x-mail::button>

Ce lien est personnel et valable **7 jours**.

@if(! $isFirstTime)
Si vous n'êtes pas à l'origine de cette demande, ignorez ce message : votre mot
de passe actuel reste inchangé.
@endif

À très vite,
**L'équipe Femme Sans Limites**
</x-mail::message>
