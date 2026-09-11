La sauvegarde automatique du site Femme Sans Limites a échoué.

Étape  : {{ $step }}
Raison : {{ $reason }}
Date   : {{ now()->translatedFormat('d F Y à H:i') }} (UTC)

Aucune sauvegarde n'a été créée. Tant que le problème persiste, une panne ou une
fausse manipulation entraînerait la perte des membres, des adhésions et des
paiements.

À vérifier en priorité :
  - l'espace disque restant sur le serveur
  - les droits d'écriture sur storage/app/backups
  - l'accès à la base de données

Pour relancer une sauvegarde manuellement :
  php artisan fsl:backup

--
Message automatique — Femme Sans Limites
