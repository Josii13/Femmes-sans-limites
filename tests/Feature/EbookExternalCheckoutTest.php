<?php

namespace Tests\Feature;

use App\Models\Ebook;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Vente d'un ebook déléguée à une plateforme externe (Chariow…).
 *
 * Le lien existait déjà mais n'était lu que si l'ebook n'avait ni prix ni
 * PDF : impossible de vendre ailleurs sans renoncer à afficher le prix sur le
 * site. Désormais le lien prime, et le circuit interne s'efface entièrement —
 * route d'achat comprise, qu'on ne doit pas pouvoir atteindre à la main.
 */
class EbookExternalCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private const LIEN = 'https://chariow.com/produit/guerir-ses-blessures';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
    }

    private function external(array $overrides = []): Ebook
    {
        $ebook = Ebook::factory()->sellable(5000)->soldExternally(self::LIEN)
            ->create($overrides);

        Storage::disk('local')->put($ebook->file_path, '%PDF');

        return $ebook;
    }

    // ── Le lien prime ───────────────────────────────────────────

    public function test_an_external_link_takes_over_the_internal_checkout(): void
    {
        $ebook = $this->external();

        $this->assertTrue($ebook->usesExternalCheckout());
        // Même avec un prix et un PDF : c'est le lien qui décide.
        $this->assertFalse($ebook->isPurchasable());
        $this->assertTrue($ebook->hasPrice());
    }

    public function test_without_a_link_the_site_sells_as_before(): void
    {
        $ebook = $this->external(['cta_url' => null]);

        $this->assertFalse($ebook->usesExternalCheckout());
        $this->assertTrue($ebook->isPurchasable());
    }

    public function test_the_buy_button_points_to_the_external_link(): void
    {
        $ebook = $this->external();

        $html = $this->get(route('ebooks.show', $ebook->slug))->assertOk()->getContent();

        $this->assertStringContainsString(e(self::LIEN), $html);
        $this->assertStringNotContainsString(route('ebooks.buy', $ebook->slug), $html);
    }

    public function test_the_price_is_still_shown(): void
    {
        $ebook = $this->external();

        $html = $this->get(route('ebooks.show', $ebook->slug))->assertOk()->getContent();

        // C'est le prix qui décide l'achat : il reste sur notre fiche, seul
        // l'encaissement part ailleurs.
        $this->assertStringContainsString('5 000', $html);
    }

    public function test_the_fsl_payment_logos_are_not_shown(): void
    {
        $ebook = $this->external();

        $html = $this->get(route('ebooks.show', $ebook->slug))->assertOk()->getContent();

        // Promettre « paiement sécurisé Wave/Orange Money » serait mentir :
        // le paiement se déroule chez le partenaire, avec ses moyens à lui.
        $this->assertStringNotContainsString('images/payments/wave.png', $html);
    }

    // ── Le circuit interne est fermé ────────────────────────────

    public function test_the_internal_buy_page_is_refused(): void
    {
        $ebook = $this->external();

        $this->get(route('ebooks.buy', $ebook->slug))
            ->assertRedirect(route('ebooks.show', $ebook->slug));
    }

    public function test_the_internal_checkout_cannot_be_forced(): void
    {
        $ebook = $this->external();

        $this->post(route('ebooks.buy.store', $ebook->slug), [
            'name' => 'Awa', 'email' => 'awa@example.com',
        ])->assertRedirect();

        // Aucun paiement FSL ne doit naître d'un ebook vendu ailleurs.
        $this->assertDatabaseCount('payments', 0);
        $this->assertSame(0, Payment::count());
    }

    // ── Côté administration ─────────────────────────────────────

    public function test_a_priced_ebook_sold_elsewhere_needs_no_pdf(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => User::ROLE_OWNER]);

        $this->actingAs($admin)->post(route('admin.ebooks.store'), [
            'title' => 'Guérir ses blessures',
            'category' => 'Développement personnel',
            'description' => 'Un chemin pour nommer ce qui fait mal.',
            'price' => 5000,
            'cta_url' => self::LIEN,
            'status' => 'published',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('ebooks', ['cta_url' => self::LIEN, 'file_path' => null]);
    }

    public function test_a_priced_ebook_sold_here_still_needs_its_pdf(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => User::ROLE_OWNER]);

        $this->actingAs($admin)->post(route('admin.ebooks.store'), [
            'title' => 'Sans PDF',
            'category' => 'Test',
            'description' => 'Description.',
            'price' => 5000,
            'status' => 'published',
        ])->assertSessionHasErrors('pdf');

        // Sans lien externe, un prix sans PDF donne une fiche invendable.
        $this->assertDatabaseCount('ebooks', 0);
    }

    public function test_the_link_must_be_a_real_url(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => User::ROLE_OWNER]);

        $this->actingAs($admin)->post(route('admin.ebooks.store'), [
            'title' => 'Lien douteux',
            'category' => 'Test',
            'description' => 'Description.',
            'price' => 5000,
            'cta_url' => 'javascript:alert(1)',
            'status' => 'published',
        ])->assertSessionHasErrors('cta_url');
    }

    public function test_the_admin_form_explains_the_precedence(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => User::ROLE_OWNER]);

        $html = $this->actingAs($admin)->get(route('admin.ebooks.create'))->assertOk()->getContent();

        // Le texte disait l'inverse : « utilisé si l'ebook n'est PAS en vente directe ».
        $this->assertStringContainsString('remplace le paiement FSL', $html);
    }

    // ── Diagnostic ──────────────────────────────────────────────

    public function test_the_sales_diagnostic_does_not_flag_an_external_ebook(): void
    {
        $ebook = Ebook::factory()->create([
            'price' => 5000, 'file_path' => null, 'status' => 'published',
            'cta_url' => self::LIEN,
        ]);

        // Sans ce cas, le diagnostic criait « AUCUN PDF » sur un ebook dont le
        // fichier vit légitimement chez le partenaire.
        $this->artisan('fsl:diagnose-sales')
            ->expectsOutputToContain('vente externe')
            ->assertSuccessful();

        $this->assertFalse($ebook->isPurchasable());
    }
}
