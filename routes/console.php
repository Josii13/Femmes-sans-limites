<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('campaigns:dispatch')
    ->everyMinute()
    // Idem : sans expiration, un arrêt brutal gèle les campagnes 24 h.
    ->withoutOverlapping(5);

Schedule::command('members:expire')
    ->dailyAt('07:00')
    ->withoutOverlapping();

// Libère les places des inscriptions payantes restées impayées.
Schedule::command('registrations:expire-unpaid')
    ->everyTenMinutes()
    ->withoutOverlapping();

// Filet de sécurité : confirme les paiements dont le webhook aurait été manqué.
Schedule::command('payments:reconcile')
    ->everyTenMinutes()
    ->withoutOverlapping();

// Sauvegarde quotidienne de la base et des fichiers téléversés (14 jours conservés).
Schedule::command('fsl:backup')
    ->dailyAt('02:30')
    ->withoutOverlapping();

// Vide la file d'attente des emails.
//
// Les confirmations partent avec ->queue(). En `QUEUE_CONNECTION=database`
// sans worker — le cas de l'hébergement mutualisé — elles restaient dans la
// table `jobs` : le back-office affichait « Confirmée » et l'inscrite ne
// recevait jamais son QR. L'ordonnanceur s'en charge, donc le cron
// `schedule:run` déjà en place suffit : aucune tâche supplémentaire à créer.
//
// `--stop-when-empty` rend la main dès la file vidée, `--max-time` borne la
// durée pour ne jamais chevaucher la minute suivante.
Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=3 --quiet')
    ->everyMinute()
    // Expiration courte du verrou : un processus tué par l'hébergeur laisse
    // sinon un verrou de 24 h, et plus aucun email ne part jusqu'au lendemain.
    ->withoutOverlapping(2)
    ->when(fn () => config('queue.default') !== 'sync');
