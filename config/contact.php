<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Coordonnées publiques de l'association
    |--------------------------------------------------------------------------
    |
    | Le numéro vivait en dur dans deux vues, dont une avec un faux numéro
    | (2250000000000) jamais remplacé. Un seul endroit, et App\Support\Contact
    | se charge des trois formes qu'il doit prendre : affichage, lien tel: et
    | lien wa.me.
    |
    | Format attendu : international, sans espace ni séparateur.
    |
    */

    'phone' => env('FSL_PHONE', '+2250705282518'),

];
