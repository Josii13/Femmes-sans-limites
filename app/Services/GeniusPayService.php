<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class GeniusPayService
{
    private function client(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.geniuspay.base_url'), '/'))
            ->withHeaders([
                'X-API-Key' => (string) config('services.geniuspay.key'),
                'X-API-Secret' => (string) config('services.geniuspay.secret'),
                'Accept' => 'application/json',
            ])
            ->acceptJson()
            ->timeout(30);
    }

    /**
     * Crée un paiement en mode Checkout et retourne les données (dont checkout_url + reference).
     *
     * @return array{reference:string, checkout_url:string, status:string, id:mixed}
     *
     * @throws \RuntimeException si l'API renvoie une erreur
     */
    public function createPayment(array $payload): array
    {
        $body = array_merge([
            'currency' => config('services.geniuspay.currency', 'XOF'),
            'payment_method' => null, // null = le client choisit sur la page GeniusPay
        ], $payload);

        $response = $this->client()->post('/payments', $body);

        if (! $response->successful() || ! data_get($response->json(), 'data.checkout_url')) {
            throw new \RuntimeException(
                'GeniusPay: création du paiement échouée — '.$response->status().' '.$response->body()
            );
        }

        $data = $response->json('data');

        return [
            'id' => $data['id'] ?? null,
            'reference' => $data['reference'],
            'checkout_url' => $data['checkout_url'],
            'status' => $data['status'] ?? 'pending',
        ];
    }

    /**
     * Récupère le statut d'une transaction (vérification/synchronisation).
     */
    public function getPayment(string $reference): ?array
    {
        $response = $this->client()->get('/payments/'.$reference);

        if (! $response->successful()) {
            return null;
        }

        return $response->json('data');
    }

    /**
     * Vérifie la signature HMAC-SHA256 d'un webhook.
     * Schéma documenté : HMAC_SHA256(timestamp + "." + payload_brut, secret).
     */
    public function verifyWebhookSignature(string $rawPayload, ?string $signature, ?string $timestamp): bool
    {
        if (blank($signature) || blank($timestamp)) {
            return false;
        }

        $expected = hash_hmac(
            'sha256',
            $timestamp.'.'.$rawPayload,
            (string) config('services.geniuspay.secret')
        );

        return hash_equals($expected, $signature);
    }
}
