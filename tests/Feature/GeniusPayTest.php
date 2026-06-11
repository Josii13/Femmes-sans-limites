<?php

namespace Tests\Feature;

use App\Mail\EbookDeliveryMail;
use App\Mail\QrCodeMail;
use App\Models\Ebook;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class GeniusPayTest extends TestCase
{
    use RefreshDatabase;

    private string $secret = 'sk_test_secret';

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.geniuspay.secret' => $this->secret,
            'services.geniuspay.key' => 'pk_test',
            'services.geniuspay.base_url' => 'https://geniuspay.test/api/v1/merchant',
        ]);
    }

    private function fakeCheckout(): void
    {
        Http::fake([
            '*/payments' => Http::response([
                'success' => true,
                'data' => ['id' => 1, 'reference' => 'MTX-TEST123', 'amount' => 5000, 'checkout_url' => 'https://geniuspay.test/checkout/MTX-TEST123', 'status' => 'pending'],
            ], 201),
        ]);
    }

    private function postWebhook(array $body): TestResponse
    {
        $payload = json_encode($body);
        $ts = '1700000000';
        $sig = hash_hmac('sha256', $ts.'.'.$payload, $this->secret);

        return $this->call('POST', '/webhooks/geniuspay', [], [], [], [
            'HTTP_X_WEBHOOK_SIGNATURE' => $sig,
            'HTTP_X_WEBHOOK_TIMESTAMP' => $ts,
            'CONTENT_TYPE' => 'application/json',
        ], $payload);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $this->withHeaders(['X-Webhook-Signature' => 'bad', 'X-Webhook-Timestamp' => '123'])
            ->postJson('/webhooks/geniuspay', ['event' => 'payment.success', 'data' => ['reference' => 'x', 'status' => 'completed']])
            ->assertStatus(401);
    }

    public function test_paid_event_registration_creates_payment_and_redirects_to_checkout(): void
    {
        $this->fakeCheckout();
        $event = Event::factory()->create(['is_paid' => true, 'price' => 5000, 'currency' => 'XOF']);

        $response = $this->post(route('events.register', $event->slug), [
            'first_name' => 'Awa', 'last_name' => 'Traoré', 'email' => 'awa@example.com',
        ]);

        $response->assertRedirect('https://geniuspay.test/checkout/MTX-TEST123');
        $this->assertDatabaseHas('payments', [
            'provider_reference' => 'MTX-TEST123', 'payable_type' => (new Registration)->getMorphClass(),
        ]);
    }

    public function test_currency_fcfa_is_normalised_to_xof_for_geniuspay(): void
    {
        $this->fakeCheckout();
        $event = Event::factory()->create(['is_paid' => true, 'price' => 5000, 'currency' => 'FCFA']);

        $this->post(route('events.register', $event->slug), [
            'first_name' => 'A', 'last_name' => 'B', 'email' => 'a@example.com',
        ]);

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/payments') && $request['currency'] === 'XOF');
    }

    public function test_webhook_confirms_event_registration_and_sends_qr(): void
    {
        Mail::fake();
        Storage::fake('local');

        $event = Event::factory()->create(['is_paid' => true, 'price' => 5000]);
        $registration = Registration::create([
            'event_id' => $event->id, 'first_name' => 'Awa', 'last_name' => 'T',
            'email' => 'awa@example.com', 'status' => 'pending',
        ]);
        $payment = Payment::create([
            'reference' => 'local-uuid-1', 'provider_reference' => 'MTX-EVT', 'status' => 'pending',
            'amount' => 5000, 'currency' => 'XOF', 'customer_email' => 'awa@example.com',
            'payable_type' => $registration->getMorphClass(), 'payable_id' => $registration->id,
        ]);

        $this->postWebhook(['event' => 'payment.success', 'data' => ['reference' => 'MTX-EVT', 'status' => 'completed']])
            ->assertOk();

        $this->assertSame('paid', $registration->refresh()->status);
        $this->assertNotNull($registration->qr_token);
        Mail::assertQueued(QrCodeMail::class);
        $this->assertSame('completed', $payment->refresh()->status);
    }

    public function test_webhook_is_idempotent(): void
    {
        Mail::fake();
        Storage::fake('local');

        $event = Event::factory()->create(['is_paid' => true, 'price' => 5000]);
        $registration = Registration::create([
            'event_id' => $event->id, 'first_name' => 'A', 'last_name' => 'B',
            'email' => 'a@example.com', 'status' => 'pending',
        ]);
        $payment = Payment::create([
            'reference' => 'r2', 'provider_reference' => 'MTX-IDEM', 'status' => 'pending', 'amount' => 5000,
            'payable_type' => $registration->getMorphClass(), 'payable_id' => $registration->id,
        ]);

        $body = ['event' => 'payment.success', 'data' => ['reference' => 'MTX-IDEM', 'status' => 'completed']];
        $this->postWebhook($body)->assertOk();
        $this->postWebhook($body)->assertOk(); // 2e fois : ne doit rien refaire

        Mail::assertQueued(QrCodeMail::class, 1);
    }

    public function test_free_event_registration_generates_qr_and_receipt_immediately(): void
    {
        Mail::fake();
        Storage::fake('local');

        $event = Event::factory()->create(['is_paid' => false, 'price' => null]);

        $this->post(route('events.register', $event->slug), [
            'first_name' => 'Awa', 'last_name' => 'T', 'email' => 'free@example.com',
        ])->assertSessionHas('success');

        $reg = Registration::where('email', 'free@example.com')->firstOrFail();
        $this->assertSame('paid', $reg->status);
        $this->assertNotNull($reg->qr_token);
        Mail::assertQueued(QrCodeMail::class);
    }

    public function test_payment_init_failure_cancels_registration(): void
    {
        Http::fake(['*/payments' => Http::response(['success' => false], 500)]);
        $event = Event::factory()->create(['is_paid' => true, 'price' => 5000]);

        $this->post(route('events.register', $event->slug), [
            'first_name' => 'A', 'last_name' => 'B', 'email' => 'fail@example.com',
        ])->assertRedirect(route('events.show', $event->slug));

        $this->assertSame('cancelled', Registration::where('email', 'fail@example.com')->first()->status);
    }

    public function test_webhook_rejects_amount_mismatch(): void
    {
        Mail::fake();
        $event = Event::factory()->create(['is_paid' => true, 'price' => 5000]);
        $registration = Registration::create([
            'event_id' => $event->id, 'first_name' => 'A', 'last_name' => 'B',
            'email' => 'm@example.com', 'status' => 'pending',
        ]);
        Payment::create([
            'reference' => 'r-mm', 'provider_reference' => 'MTX-MM', 'status' => 'pending', 'amount' => 5000,
            'payable_type' => $registration->getMorphClass(), 'payable_id' => $registration->id,
        ]);

        // Montant reçu (1000) ≠ attendu (5000) → refusé.
        $this->postWebhook(['event' => 'payment.success', 'data' => ['reference' => 'MTX-MM', 'status' => 'completed', 'amount' => 1000]])
            ->assertStatus(422);

        $this->assertSame('pending', $registration->refresh()->status);
        Mail::assertNothingQueued();
    }

    public function test_reconcile_confirms_pending_payment(): void
    {
        Mail::fake();
        Storage::fake('local');
        Http::fake(['*/payments/*' => Http::response(['success' => true, 'data' => ['reference' => 'MTX-REC', 'status' => 'completed', 'amount' => 5000]], 200)]);

        $event = Event::factory()->create(['is_paid' => true, 'price' => 5000]);
        $registration = Registration::create([
            'event_id' => $event->id, 'first_name' => 'A', 'last_name' => 'B',
            'email' => 'rec@example.com', 'status' => 'pending',
        ]);
        $payment = Payment::create([
            'reference' => 'r-rec', 'provider_reference' => 'MTX-REC', 'status' => 'pending', 'amount' => 5000,
            'payable_type' => $registration->getMorphClass(), 'payable_id' => $registration->id,
        ]);

        $this->artisan('payments:reconcile')->assertExitCode(0);

        $this->assertTrue($payment->refresh()->isPaid());
        $this->assertSame('paid', $registration->refresh()->status);
        Mail::assertQueued(QrCodeMail::class);
    }

    public function test_expire_unpaid_cancels_old_pending_paid_registrations(): void
    {
        $event = Event::factory()->create(['is_paid' => true, 'price' => 5000]);
        $old = Registration::create([
            'event_id' => $event->id, 'first_name' => 'Old', 'last_name' => 'P',
            'email' => 'old@example.com', 'status' => 'pending',
        ]);
        \DB::table('registrations')->where('id', $old->id)->update(['created_at' => now()->subMinutes(40)]);

        $recent = Registration::create([
            'event_id' => $event->id, 'first_name' => 'New', 'last_name' => 'P',
            'email' => 'new@example.com', 'status' => 'pending',
        ]);

        $this->artisan('registrations:expire-unpaid')->assertExitCode(0);

        $this->assertSame('cancelled', $old->refresh()->status);
        $this->assertSame('pending', $recent->refresh()->status);
    }

    public function test_abandoned_paid_registration_can_resume_payment(): void
    {
        $this->fakeCheckout();
        $event = Event::factory()->create(['is_paid' => true, 'price' => 5000]);
        Registration::create([
            'event_id' => $event->id, 'first_name' => 'A', 'last_name' => 'B',
            'email' => 'resume@example.com', 'status' => 'pending',
        ]);

        // Réessayer avec le même email ne bloque plus : on relance le paiement.
        $this->post(route('events.register', $event->slug), [
            'first_name' => 'A', 'last_name' => 'B', 'email' => 'resume@example.com',
        ])->assertRedirect('https://geniuspay.test/checkout/MTX-TEST123');
    }

    public function test_resend_ebook_link_requeues_delivery(): void
    {
        Mail::fake();
        $ebook = Ebook::factory()->create(['price' => 5000, 'file_path' => 'ebooks/files/x.pdf', 'status' => 'published']);
        Payment::create([
            'reference' => 'r-rs', 'provider_reference' => 'MTX-RS', 'status' => 'completed', 'amount' => 5000,
            'customer_email' => 'buyer@example.com', 'payable_type' => (new Ebook)->getMorphClass(), 'payable_id' => $ebook->id,
        ]);

        $this->post(route('ebooks.resend.store'), ['email' => 'buyer@example.com'])->assertRedirect();
        Mail::assertQueued(EbookDeliveryMail::class);
    }

    public function test_admin_can_view_sales_dashboard(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get(route('admin.sales.index'))->assertOk();
    }

    public function test_ebook_purchase_creates_payment_and_webhook_delivers_ebook(): void
    {
        Mail::fake();
        $this->fakeCheckout();

        $ebook = Ebook::factory()->create(['price' => 5000, 'currency' => 'XOF', 'file_path' => 'ebooks/files/test.pdf', 'status' => 'published']);

        // Achat
        $this->post(route('ebooks.buy.store', $ebook->slug), [
            'name' => 'Marie', 'email' => 'marie@example.com',
        ])->assertRedirect('https://geniuspay.test/checkout/MTX-TEST123');

        $payment = Payment::where('provider_reference', 'MTX-TEST123')->firstOrFail();
        $this->assertSame((new Ebook)->getMorphClass(), $payment->payable_type);

        // Webhook de succès → livraison
        $this->postWebhook(['event' => 'payment.success', 'data' => ['reference' => 'MTX-TEST123', 'status' => 'completed']])
            ->assertOk();

        Mail::assertQueued(EbookDeliveryMail::class);
        $this->assertTrue($payment->refresh()->isPaid());
    }
}
