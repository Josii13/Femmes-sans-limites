<x-mail::message>
<div style="text-align:center;margin-bottom:24px;">
<img src="{{ asset('logo_FSL.png') }}" alt="FSL" style="height:48px;width:auto;">
</div>

# Merci pour ta candidature

Bonjour {{ $member->name }},

Nous te remercions sincèrement de l'intérêt que tu portes à **Femme Sans Limites** et du temps consacré à ta candidature.

Après étude, nous ne sommes malheureusement pas en mesure de donner une suite favorable à ta demande pour le moment. Ce choix ne remet nullement en cause ta valeur ou ton parcours.

Tu restes la bienvenue pour suivre nos actualités, participer à nos événements ouverts et, si tu le souhaites, soumettre une nouvelle candidature à l'avenir.

<x-mail::button :url="route('events.index')" color="primary">
Découvrir nos événements →
</x-mail::button>

Avec tout notre respect,
**L'équipe Femme Sans Limites**
</x-mail::message>
