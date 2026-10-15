<?php

namespace Tests\Feature;

use App\Http\Controllers\Concerns\Honeypot;
use App\Mail\EbookDeliveryMail;
use App\Models\Ebook;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Intégrité du module ebooks, revue avant un lancement.
 *
 * L'enjeu est double : qu'un PDF ne parte jamais sans paiement, et qu'un
 * paiement qui échoue ne laisse pas de trace trompeuse dans les ventes.
 */
class EbookIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Storage::fake('local');

        config([
            'services.geniuspay.key' => 'pk_test',
            'services.geniuspay.secret' => 'sk_test',
            'services.geniuspay.base_url' => 'https://geniuspay.test/api/v1/merchant',
        ]);
    }

    private function sellable(int $price = 5000): Ebook
    {
        $ebook = Ebook::factory()->sellable($price)->create();
        Storage::disk('local')->put($ebook->file_path, '%PDF-1.4 contenu');

        return $ebook;
    }

    private function buyer(array $overrides = []): array
    {
        return array_merge(['name' => 'Awa', 'email' => 'awa@example.com'], $overrides);
    }

    // ── Le PDF ne part pas sans paiement ────────────────────────

    public function test_an_unpaid_payment_cannot_download_the_pdf(): void
    {
        $ebook = $this->sellable();

        $payment = Payment::create([
            'provider' => 'geniuspay', 'reference' => 'ref-pending', 'status' => 'pending',
            'amount' => 5000, 'currency' => 'XOF',
            'customer_name' => 'Awa', 'customer_email' => 'awa@example.com',
            'payable_type' => $ebook->getMorphClass(), 'payable_id' => $ebook->id,
        ]);

        // Même avec un lien correctement signé, un paiement non abouti est refusé.
        $url = URL::temporarySignedRoute('ebooks.download', now()->addDay(), ['payment' => $payment->reference]);

        $this->get($url)->assertForbidden();
    }

    public function test_an_unsigned_download_link_is_refused(): void
    {
        $ebook = $this->sellable();
        $payment = Payment::create([
            'provider' => 'geniuspay', 'reference' => 'ref-paid', 'status' => 'completed',
            'amount' => 5000, 'currency' => 'XOF',
            'customer_name' => 'Awa', 'customer_email' => 'awa@example.com',
            'payable_type' => $ebook->getMorphClass(), 'payable_id' => $ebook->id,
        ]);

        // Sans signature, l'URL serait devinable à partir d'une seule référence.
        $this->get(route('ebooks.download', $payment->reference))->assertForbidden();
    }

    public function test_a_paid_and_signed_link_delivers_the_pdf(): void
    {
        $ebook = $this->sellable();
        $payment = Payment::create([
            'provider' => 'geniuspay', 'reference' => 'ref-ok', 'status' => 'completed',
            'amount' => 5000, 'currency' => 'XOF',
            'customer_name' => 'Awa', 'customer_email' => 'awa@example.com',
            'payable_type' => $ebook->getMorphClass(), 'payable_id' => $ebook->id,
        ]);

        $url = URL::temporarySignedRoute('ebooks.download', now()->addDay(), ['payment' => $payment->reference]);

        $this->get($url)->assertOk()->assertDownload();
    }

    // ── Un paiement avorté ne doit pas polluer les ventes ───────

    public function test_a_failed_checkout_marks_the_payment_as_failed(): void
    {
        $ebook = $this->sellable();
        Http::fake(['*/payments' => Http::response(['message' => 'indisponible'], 500)]);

        $this->post(route('ebooks.buy.store', $ebook->slug), $this->buyer())
            ->assertRedirect()->assertSessionHas('error');

        // « pending » laisserait un paiement fantôme dans le tableau de bord et
        // la réconciliation le reprendrait indéfiniment.
        $this->assertSame('failed', Payment::firstOrFail()->status);
    }

    public function test_an_unpublished_ebook_cannot_be_bought(): void
    {
        $ebook = Ebook::factory()->sellable()->draft()->create();
        Storage::disk('local')->put($ebook->file_path, '%PDF');

        $this->get(route('ebooks.buy', $ebook->slug))->assertRedirect(route('ebooks.show', $ebook->slug));
        $this->post(route('ebooks.buy.store', $ebook->slug), $this->buyer())->assertRedirect();

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_an_unpublished_ebook_page_is_not_reachable(): void
    {
        $ebook = Ebook::factory()->draft()->create();

        $this->get(route('ebooks.show', $ebook->slug))->assertNotFound();
    }

    // ── Robots ──────────────────────────────────────────────────

    public function test_the_resend_form_carries_a_trap(): void
    {
        $html = $this->get(route('ebooks.resend'))->assertOk()->getContent();

        $this->assertStringContainsString('name="'.Honeypot::FIELD.'"', $html);
    }

    public function test_a_bot_cannot_trigger_resend_emails(): void
    {
        $ebook = $this->sellable();
        Payment::create([
            'provider' => 'geniuspay', 'reference' => 'ref-1', 'status' => 'completed',
            'amount' => 5000, 'currency' => 'XOF',
            'customer_name' => 'Awa', 'customer_email' => 'awa@example.com',
            'payable_type' => $ebook->getMorphClass(), 'payable_id' => $ebook->id,
        ]);

        $this->post(route('ebooks.resend.store'), [
            'email' => 'awa@example.com', Honeypot::FIELD => 'https://spam.test',
        ])->assertRedirect();

        Mail::assertNothingQueued();
    }

    public function test_a_human_still_receives_her_link(): void
    {
        $ebook = $this->sellable();
        Payment::create([
            'provider' => 'geniuspay', 'reference' => 'ref-2', 'status' => 'completed',
            'amount' => 5000, 'currency' => 'XOF',
            'customer_name' => 'Awa', 'customer_email' => 'awa@example.com',
            'payable_type' => $ebook->getMorphClass(), 'payable_id' => $ebook->id,
        ]);

        $this->post(route('ebooks.resend.store'), ['email' => 'awa@example.com'])->assertRedirect();

        Mail::assertQueued(EbookDeliveryMail::class);
    }
}
