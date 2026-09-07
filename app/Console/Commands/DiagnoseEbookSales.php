<?php

namespace App\Console\Commands;

use App\Models\Ebook;
use App\Models\Payment;
use App\Services\GeniusPayService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Diagnostic en LECTURE SEULE de la chaîne de vente d'ebooks.
 * Ne crée, ne modifie et ne supprime rien : conçu pour être lancé en production
 * quand un achat échoue, là où les logs sont peu accessibles.
 */
class DiagnoseEbookSales extends Command
{
    protected $signature = 'fsl:diagnose-sales {--api : Teste aussi la connexion à l\'API GeniusPay (appel en lecture seule)}';

    protected $description = 'Vérifie pourquoi un ebook payant ne peut pas être acheté (config, fichier PDF, API de paiement)';

    private int $problems = 0;

    public function handle(GeniusPayService $genius): int
    {
        $this->newLine();
        $this->line('<options=bold>═══ Diagnostic de la vente d\'ebooks ═══</>');

        $this->checkEnvironment();
        $this->checkGeniusPayConfig();
        $this->checkEbooks();
        $this->checkRecentPayments();

        if ($this->option('api')) {
            $this->checkApiReachable();
        } else {
            $this->newLine();
            $this->line('  Ajoutez <options=bold>--api</> pour tester en plus la connexion à GeniusPay.');
        }

        $this->newLine();
        if ($this->problems === 0) {
            $this->info('✔ Aucun problème détecté sur la chaîne de vente.');
        } else {
            $this->error($this->problems.' problème(s) détecté(s) — voir les lignes ✗ ci-dessus.');
        }
        $this->newLine();

        return $this->problems === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function checkEnvironment(): void
    {
        $this->section('Environnement');

        $this->assert(config('app.key') !== null && config('app.key') !== '',
            'APP_KEY définie', 'APP_KEY absente : les liens de téléchargement signés seront invalides');

        $url = (string) config('app.url');
        $this->assert($url !== '' && $url !== 'http://localhost',
            'APP_URL = '.$url, 'APP_URL vaut « '.$url.' » : les liens de paiement et de téléchargement pointeront au mauvais endroit');

        // En local, une URL en http est normale : on ne l'exige qu'en production.
        if (app()->environment('production')) {
            $this->assert(str_starts_with($url, 'https://'),
                'APP_URL en HTTPS', 'APP_URL n\'est pas en HTTPS : GeniusPay peut refuser les URL de retour');
        }

        $this->line('  · Environnement : '.config('app.env').' | debug : '.(config('app.debug') ? 'activé' : 'désactivé'));
        $this->line('  · File d\'attente : '.config('queue.default').' | mailer : '.config('mail.default'));

        if (config('queue.default') !== 'sync') {
            $this->warn('  ! QUEUE_CONNECTION='.config('queue.default').' : la livraison de l\'ebook par email exige un worker actif.');
        }
    }

    private function checkGeniusPayConfig(): void
    {
        $this->section('Configuration GeniusPay');

        $base = (string) config('services.geniuspay.base_url');
        $key = (string) config('services.geniuspay.key');
        $secret = (string) config('services.geniuspay.secret');

        $this->assert($base !== '', 'base_url = '.$base, 'GENIUSPAY_BASE_URL vide');
        $this->assert($key !== '', 'GENIUSPAY_API_KEY définie ('.strlen($key).' caractères)',
            'GENIUSPAY_API_KEY absente : aucun paiement ne peut être initié');
        $this->assert($secret !== '', 'GENIUSPAY_API_SECRET définie ('.strlen($secret).' caractères)',
            'GENIUSPAY_API_SECRET absente : aucun paiement ne peut être initié');
    }

    private function checkEbooks(): void
    {
        $this->section('Ebooks payants');

        $priced = Ebook::whereNotNull('price')->where('price', '>', 0)->get();

        if ($priced->isEmpty()) {
            $this->warn('  ! Aucun ebook avec un prix > 0 : aucun n\'est vendable sur le site.');
            $this->line('    Un ebook sans prix affiche le lien externe (cta_url) s\'il en a un.');

            return;
        }

        foreach ($priced as $ebook) {
            $this->line('  <options=bold>'.$ebook->title.'</> (#'.$ebook->id.')');
            $this->line('    prix : '.$ebook->price.' '.$ebook->currency.' | statut : '.$ebook->status);

            $this->assert($ebook->status === 'published',
                'publié', 'en brouillon : la fiche est introuvable pour le public', 4);

            $hasPath = ! empty($ebook->file_path);
            $this->assert($hasPath, 'fichier PDF renseigné',
                'AUCUN PDF : le bouton « Acheter » ne s\'affiche pas (isPurchasable = false)', 4);

            if ($hasPath) {
                $this->assert(Storage::disk('local')->exists($ebook->file_path),
                    'PDF présent sur le disque ('.$ebook->file_path.')',
                    'PDF introuvable sur le disque : '.$ebook->file_path, 4);
            }

            $this->assert($ebook->isPurchasable(),
                'ACHETABLE — le bouton de paiement s\'affiche',
                'NON ACHETABLE — la fiche publique n\'affichera pas de bouton de paiement', 4);

            $this->line('    URL : '.route('ebooks.show', $ebook->slug));
        }
    }

    private function checkRecentPayments(): void
    {
        $this->section('Derniers paiements d\'ebooks');

        $payments = Payment::where('payable_type', (new Ebook)->getMorphClass())
            ->latest()->limit(5)->get();

        if ($payments->isEmpty()) {
            $this->line('  · Aucun paiement d\'ebook enregistré.');
            $this->line('    Si un client a cliqué « Payer » sans qu\'une ligne apparaisse ici,');
            $this->line('    l\'échec est survenu AVANT l\'appel à GeniusPay (validation ou redirection).');

            return;
        }

        foreach ($payments as $p) {
            $this->line(sprintf('  · %s | %s %s | statut : %-12s | checkout : %s',
                $p->created_at->format('d/m/Y H:i'),
                $p->amount, $p->currency,
                $p->status,
                $p->checkout_url ? 'oui' : 'NON généré'
            ));
            if (! $p->checkout_url) {
                $this->problems++;
                $this->line('      <fg=red>✗ pas d\'URL de checkout : l\'appel à GeniusPay a échoué (voir storage/logs)</>');
            }
        }
    }

    private function checkApiReachable(): void
    {
        $this->section('Connexion à l\'API GeniusPay (lecture seule)');

        $base = rtrim((string) config('services.geniuspay.base_url'), '/');

        try {
            // Référence volontairement inexistante : rien n'est créé ni modifié.
            $response = Http::baseUrl($base)
                ->withHeaders([
                    'X-API-Key' => (string) config('services.geniuspay.key'),
                    'X-API-Secret' => (string) config('services.geniuspay.secret'),
                    'Accept' => 'application/json',
                ])
                ->acceptJson()->timeout(20)
                ->get('/payments/diagnostic-'.bin2hex(random_bytes(4)));
        } catch (\Throwable $e) {
            $this->problems++;
            $this->line('  <fg=red>✗ Serveur injoignable : '.$e->getMessage().'</>');
            if (str_contains($e->getMessage(), 'SSL certificate')) {
                $this->line('    Le serveur n\'a pas de bundle de certificats CA valide (curl.cainfo).');
            }

            return;
        }

        $status = $response->status();
        $this->line('  · Réponse HTTP : '.$status);

        if (in_array($status, [401, 403], true)) {
            $this->problems++;
            $this->line('  <fg=red>✗ Authentification refusée : clé ou secret GeniusPay invalide pour ce serveur.</>');
            $this->line('    Réponse : '.mb_substr($response->body(), 0, 300));

            return;
        }

        if ($status === 404) {
            $this->line('  <fg=green>✔ Authentification acceptée (404 attendu sur une référence inexistante).</>');

            return;
        }

        $this->line('  · Réponse inattendue : '.mb_substr($response->body(), 0, 300));
    }

    private function section(string $title): void
    {
        $this->newLine();
        $this->line('<options=bold;fg=cyan>── '.$title.'</>');
    }

    private function assert(bool $ok, string $okMessage, string $failMessage, int $indent = 2): void
    {
        $pad = str_repeat(' ', $indent);

        if ($ok) {
            $this->line($pad.'<fg=green>✔</> '.$okMessage);

            return;
        }

        $this->problems++;
        $this->line($pad.'<fg=red>✗ '.$failMessage.'</>');
    }
}
