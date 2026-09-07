{{--
    Piège anti-bot partagé par tous les formulaires publics.

    Le champ s'appelait « website » et se trouvait en PREMIER dans le formulaire :
    deux aimants à remplissage automatique. Les navigateurs et gestionnaires de
    mots de passe y injectaient une URL (autocomplete="off" est largement ignoré),
    et le visiteur légitime était traité comme un robot — achat impossible,
    candidature perdue en silence.

    Trois précautions désormais :
      1. un nom opaque, qui ne correspond à aucune heuristique de remplissage ;
      2. autocomplete="new-password", qui décourage la réutilisation de données ;
      3. à placer EN DERNIER dans le formulaire, car beaucoup d'outils ciblent le
         premier champ texte rencontré.
--}}
@props(['uid' => uniqid()])

<div aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;">
    <label for="hp-{{ $uid }}">Ne pas remplir</label>
    <input type="text"
           id="hp-{{ $uid }}"
           name="{{ \App\Http\Controllers\Concerns\Honeypot::FIELD }}"
           value=""
           tabindex="-1"
           autocomplete="new-password"
           data-form-type="other">
</div>
