<?php

namespace Tests\Feature;

use App\Models\Ebook;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Promotions à durée limitée sur les ebooks.
 *
 * L'exigence tient en une phrase : pendant la fenêtre, le prix promotionnel
 * s'applique et le prix normal s'affiche barré ; à l'échéance, le prix normal
 * reprend effet. Le point sensible est le montant RÉELLEMENT ENCAISSÉ : afficher
 * une promotion puis facturer le prix normal serait une tromperie, et l'inverse
 * une perte sèche.
 */
class EbookPromoTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function sellableOnPromo(): Ebook
    {
        Storage::fake('local');
        $ebook = Ebook::factory()->sellable(5000)->onPromo(3500)->create();
        Storage::disk('local')->put($ebook->file_path, '%PDF-1.4');

        return $ebook;
    }

    // ── Le modèle ────────────────────────────────────────────────

    public function test_active_promo_sets_the_effective_price(): void
    {
        $ebook = Ebook::factory()->sellable(5000)->onPromo(3500)->create();

        $this->assertTrue($ebook->hasActivePromo());
        $this->assertSame(3500.0, $ebook->effectivePrice());
        $this->assertSame(30, $ebook->promoDiscountPercent());
    }

    public function test_normal_price_resumes_when_the_promo_ends(): void
    {
        $ebook = Ebook::factory()->sellable(5000)->onPromo(3500)->create();
        $this->assertSame(3500.0, $ebook->effectivePrice());

        // On avance dans le temps jusqu'après l'échéance : aucune tâche planifiée
        // n'intervient, l'expiration découle des dates.
        Carbon::setTestNow($ebook->promo_ends_at->copy()->addSecond());

        $this->assertFalse($ebook->hasActivePromo());
        $this->assertTrue($ebook->hasExpiredPromo());
        $this->assertSame(5000.0, $ebook->effectivePrice());

        Carbon::setTestNow();
    }

    public function test_scheduled_promo_is_not_applied_before_its_start(): void
    {
        $ebook = Ebook::factory()->sellable(5000)->promoScheduled(3500)->create();

        $this->assertFalse($ebook->hasActivePromo());
        $this->assertTrue($ebook->hasScheduledPromo());
        $this->assertSame(5000.0, $ebook->effectivePrice());

        Carbon::setTestNow($ebook->promo_starts_at->copy()->addSecond());
        $this->assertTrue($ebook->hasActivePromo());
        $this->assertSame(3500.0, $ebook->effectivePrice());

        Carbon::setTestNow();
    }

    public function test_a_promo_price_above_the_normal_price_is_ignored(): void
    {
        $ebook = Ebook::factory()->sellable(5000)->onPromo(6000)->create();

        $this->assertFalse($ebook->hasActivePromo());
        $this->assertSame(5000.0, $ebook->effectivePrice());
    }

    public function test_promo_without_end_date_stays_active(): void
    {
        $ebook = Ebook::factory()->sellable(5000)->create([
            'promo_price' => 3500, 'promo_starts_at' => now()->subDay(), 'promo_ends_at' => null,
        ]);

        $this->assertTrue($ebook->hasActivePromo());
        $this->assertSame(3500.0, $ebook->effectivePrice());
    }

    public function test_on_promo_scope_matches_only_active_promotions(): void
    {
        $active = Ebook::factory()->sellable()->onPromo()->create();
        Ebook::factory()->sellable()->promoExpired()->create();
        Ebook::factory()->sellable()->promoScheduled()->create();
        Ebook::factory()->sellable()->create();
        Ebook::factory()->sellable(5000)->onPromo(6000)->create(); // promo invalide

        $this->assertSame([$active->id], Ebook::onPromo()->pluck('id')->all());
    }

    // ── Le montant encaissé ──────────────────────────────────────

    public function test_purchase_charges_the_promo_price(): void
    {
        Mail::fake();
        $ebook = $this->sellableOnPromo();
        Http::fake(['*/payments' => Http::response([
            'success' => true,
            'data' => ['id' => 1, 'reference' => 'MTX-P', 'checkout_url' => 'https://gp.test/c/1', 'status' => 'pending'],
        ], 201)]);

        $this->post(route('ebooks.buy.store', $ebook->slug), [
            'name' => 'Marie', 'email' => 'marie@example.com',
        ])->assertRedirect('https://gp.test/c/1');

        // Montant enregistré en base…
        $this->assertSame('3500.00', Payment::firstOrFail()->amount);

        // …et montant transmis à GeniusPay.
        Http::assertSent(fn ($request) => (float) $request['amount'] === 3500.0);
    }

    public function test_purchase_charges_the_normal_price_once_the_promo_expired(): void
    {
        Mail::fake();
        Storage::fake('local');
        $ebook = Ebook::factory()->sellable(5000)->promoExpired(3500)->create();
        Storage::disk('local')->put($ebook->file_path, '%PDF-1.4');

        Http::fake(['*/payments' => Http::response([
            'success' => true,
            'data' => ['id' => 1, 'reference' => 'MTX-N', 'checkout_url' => 'https://gp.test/c/2', 'status' => 'pending'],
        ], 201)]);

        $this->post(route('ebooks.buy.store', $ebook->slug), [
            'name' => 'Marie', 'email' => 'marie@example.com',
        ])->assertRedirect('https://gp.test/c/2');

        $this->assertSame('5000.00', Payment::firstOrFail()->amount);
        Http::assertSent(fn ($request) => (float) $request['amount'] === 5000.0);
    }

    public function test_price_paid_stays_honoured_if_the_promo_expires_mid_payment(): void
    {
        Mail::fake();
        $ebook = $this->sellableOnPromo();
        Http::fake(['*/payments' => Http::response([
            'success' => true,
            'data' => ['id' => 1, 'reference' => 'MTX-M', 'checkout_url' => 'https://gp.test/c/3', 'status' => 'pending'],
        ], 201)]);

        $this->post(route('ebooks.buy.store', $ebook->slug), [
            'name' => 'Marie', 'email' => 'marie@example.com',
        ])->assertRedirect();

        // La promo se termine avant que le webhook n'arrive : le montant déjà engagé
        // ne doit pas être requalifié, sinon la vérification du montant échouerait.
        Carbon::setTestNow($ebook->promo_ends_at->copy()->addHour());

        $payment = Payment::firstOrFail();
        $this->assertSame('3500.00', $payment->amount);

        Carbon::setTestNow();
    }

    // ── L'affichage ──────────────────────────────────────────────

    public function test_show_page_displays_struck_price_discount_and_deadline(): void
    {
        $ebook = $this->sellableOnPromo();
        $html = $this->get(route('ebooks.show', $ebook->slug))->assertOk()->getContent();

        $this->assertStringContainsString('3 500', $html);          // prix promotionnel
        $this->assertStringContainsString('line-through', $html);   // prix normal barré
        $this->assertStringContainsString('5 000', $html);
        $this->assertStringContainsString('30', $html);             // remise
        $this->assertStringContainsString('Offre valable jusqu', $html);
    }

    public function test_show_page_hides_promo_markup_once_expired(): void
    {
        Storage::fake('local');
        $ebook = Ebook::factory()->sellable(5000)->promoExpired(3500)->create();
        Storage::disk('local')->put($ebook->file_path, '%PDF-1.4');

        $html = $this->get(route('ebooks.show', $ebook->slug))->assertOk()->getContent();

        $this->assertStringContainsString('5 000', $html);
        $this->assertStringNotContainsString('3 500', $html);
        $this->assertStringNotContainsString('Offre valable jusqu', $html);
    }

    public function test_buy_page_announces_the_promo_price_on_the_button(): void
    {
        $ebook = $this->sellableOnPromo();
        $html = $this->get(route('ebooks.buy', $ebook->slug))->assertOk()->getContent();

        $this->assertStringContainsString('Payer 3 500 XOF', $html);
        $this->assertStringNotContainsString('Payer 5 000 XOF', $html);
    }

    public function test_library_card_shows_the_promo_price_and_discount_badge(): void
    {
        $ebook = $this->sellableOnPromo();
        $html = $this->get(route('ebooks.index'))->assertOk()->getContent();

        $this->assertStringContainsString('3 500', $html);
        $this->assertStringContainsString('−'.$ebook->promoDiscountPercent().'%', $html);
    }

    // ── La saisie en back-office ─────────────────────────────────

    public function test_admin_can_set_a_promotion(): void
    {
        Mail::fake();
        Storage::fake('local');
        $ebook = Ebook::factory()->sellable(5000)->create();
        Storage::disk('local')->put($ebook->file_path, '%PDF-1.4');

        $this->actingAs($this->admin())->put(route('admin.ebooks.update', $ebook), [
            'title' => $ebook->title, 'category' => 'Business', 'description' => 'Desc.',
            'price' => 5000, 'currency' => 'XOF', 'status' => 'published',
            'promo_price' => 3500,
            'promo_ends_at' => now()->addWeek()->format('Y-m-d\TH:i'),
        ])->assertSessionHasNoErrors()->assertRedirect();

        $ebook->refresh();
        $this->assertTrue($ebook->hasActivePromo());
        $this->assertSame(3500.0, $ebook->effectivePrice());
    }

    public function test_promo_requires_an_end_date(): void
    {
        $ebook = Ebook::factory()->sellable(5000)->create();

        $this->actingAs($this->admin())->put(route('admin.ebooks.update', $ebook), [
            'title' => $ebook->title, 'category' => 'Business', 'description' => 'Desc.',
            'price' => 5000, 'status' => 'published', 'promo_price' => 3500,
        ])->assertSessionHasErrors('promo_ends_at');

        $this->assertNull($ebook->refresh()->promo_price);
    }

    public function test_promo_price_must_be_below_the_normal_price(): void
    {
        $ebook = Ebook::factory()->sellable(5000)->create();

        $this->actingAs($this->admin())->put(route('admin.ebooks.update', $ebook), [
            'title' => $ebook->title, 'category' => 'Business', 'description' => 'Desc.',
            'price' => 5000, 'status' => 'published',
            'promo_price' => 5000,
            'promo_ends_at' => now()->addWeek()->format('Y-m-d\TH:i'),
        ])->assertSessionHasErrors('promo_price');
    }

    public function test_end_date_must_follow_the_start_date(): void
    {
        $ebook = Ebook::factory()->sellable(5000)->create();

        $this->actingAs($this->admin())->put(route('admin.ebooks.update', $ebook), [
            'title' => $ebook->title, 'category' => 'Business', 'description' => 'Desc.',
            'price' => 5000, 'status' => 'published', 'promo_price' => 3500,
            'promo_starts_at' => now()->addWeeks(2)->format('Y-m-d\TH:i'),
            'promo_ends_at' => now()->addWeek()->format('Y-m-d\TH:i'),
        ])->assertSessionHasErrors('promo_ends_at');
    }

    /**
     * Une promotion expirée ne doit pas bloquer une modification sans rapport :
     * exiger une date de fin future rendrait l'ebook non éditable.
     */
    public function test_an_expired_promotion_does_not_block_editing(): void
    {
        Mail::fake();
        Storage::fake('local');
        $ebook = Ebook::factory()->sellable(5000)->promoExpired(3500)->create();
        Storage::disk('local')->put($ebook->file_path, '%PDF-1.4');

        $this->actingAs($this->admin())->put(route('admin.ebooks.update', $ebook), [
            'title' => 'Nouveau titre', 'category' => 'Business', 'description' => 'Desc.',
            'price' => 5000, 'currency' => 'XOF', 'status' => 'published',
            'promo_price' => (int) $ebook->promo_price,
            'promo_starts_at' => $ebook->promo_starts_at->format('Y-m-d\TH:i'),
            'promo_ends_at' => $ebook->promo_ends_at->format('Y-m-d\TH:i'),
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame('Nouveau titre', $ebook->refresh()->title);
        $this->assertSame(5000.0, $ebook->effectivePrice());
    }

    public function test_clearing_the_promo_price_removes_the_promotion(): void
    {
        Mail::fake();
        Storage::fake('local');
        $ebook = Ebook::factory()->sellable(5000)->onPromo(3500)->create();
        Storage::disk('local')->put($ebook->file_path, '%PDF-1.4');

        $this->actingAs($this->admin())->put(route('admin.ebooks.update', $ebook), [
            'title' => $ebook->title, 'category' => 'Business', 'description' => 'Desc.',
            'price' => 5000, 'currency' => 'XOF', 'status' => 'published',
            'promo_price' => '', 'promo_starts_at' => '', 'promo_ends_at' => '',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $ebook->refresh();
        $this->assertNull($ebook->promo_price);
        $this->assertFalse($ebook->hasActivePromo());
        $this->assertSame(5000.0, $ebook->effectivePrice());
    }
}
