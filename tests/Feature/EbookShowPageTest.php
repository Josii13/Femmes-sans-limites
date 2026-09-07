<?php

namespace Tests\Feature;

use App\Models\Ebook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * La fiche d'un ebook est la page de conversion : sur mobile, le prix et le
 * bouton d'achat doivent rester atteignables sans traverser toute la page.
 *
 * L'ordre du DOM EST l'ordre mobile (la mise en page desktop est rétablie par
 * un placement explicite en grille), donc ces positions sont ce que voit
 * réellement un visiteur sur téléphone.
 */
class EbookShowPageTest extends TestCase
{
    use RefreshDatabase;

    private function sellable(): Ebook
    {
        Storage::fake('local');
        $ebook = Ebook::factory()->create([
            'status' => 'published', 'price' => 5000, 'currency' => 'XOF',
            'file_path' => 'ebooks/files/a.pdf', 'author_note' => 'Une note de l’autrice.',
        ]);
        Storage::disk('local')->put($ebook->file_path, '%PDF-1.4');

        return $ebook;
    }

    public function test_purchase_block_comes_before_the_description_on_mobile(): void
    {
        $ebook = $this->sellable();
        $html = $this->get(route('ebooks.show', $ebook->slug))->assertOk()->getContent();

        // La description figure aussi dans la balise meta du <head> : on ne compare
        // que les positions dans le corps de la page.
        $body = substr($html, (int) strpos($html, '</head>'));

        $cta = strpos($body, 'id="ebook-cta"');
        $description = strpos($body, e($ebook->description));
        $note = strpos($body, e($ebook->author_note));

        $this->assertNotFalse($cta);
        $this->assertNotFalse($description);
        $this->assertLessThan($description, $cta, 'Sur mobile, le bloc d’achat doit précéder la description.');
        $this->assertLessThan($note, $cta, 'Sur mobile, le bloc d’achat doit précéder le mot de l’autrice.');
    }

    public function test_desktop_layout_is_restored_by_explicit_grid_placement(): void
    {
        $html = $this->get(route('ebooks.show', $this->sellable()->slug))->assertOk()->getContent();

        // Couverture à gauche sur toute la hauteur, texte puis achat à droite.
        $this->assertStringContainsString('lg:col-start-1 lg:row-start-1 lg:row-span-4', $html);
        $this->assertStringContainsString('lg:col-start-2 lg:row-start-1', $html); // titre
        $this->assertStringContainsString('lg:col-start-2 lg:row-start-2', $html); // description
        $this->assertStringContainsString('lg:col-start-2 lg:row-start-3', $html); // note
        $this->assertStringContainsString('lg:col-start-2 lg:row-start-4', $html); // achat
    }

    public function test_floating_bar_is_shown_for_a_sellable_ebook(): void
    {
        $ebook = $this->sellable();
        $html = $this->get(route('ebooks.show', $ebook->slug))->assertOk()->getContent();

        $this->assertStringContainsString('fixed inset-x-0 bottom-0', $html);
        $this->assertStringContainsString('Acheter maintenant', $html);

        // La barre pointe vers le tunnel de paiement, comme le bouton de la page.
        $this->assertSame(2, substr_count($html, route('ebooks.buy', $ebook->slug)));

        // Elle vise le bloc d'achat pour ne s'afficher que lorsqu'il est hors écran.
        $this->assertStringContainsString("getElementById('ebook-cta')", $html);
    }

    public function test_floating_bar_points_to_the_partner_link_when_there_is_no_direct_sale(): void
    {
        $ebook = Ebook::factory()->create([
            'status' => 'published', 'price' => null, 'file_path' => null,
            'cta_url' => 'https://charriow.com/produit/x', 'cta_label' => 'Commander sur Charriow',
        ]);

        $html = $this->get(route('ebooks.show', $ebook->slug))->assertOk()->getContent();

        $this->assertStringContainsString('fixed inset-x-0 bottom-0', $html);
        $this->assertSame(2, substr_count($html, 'https://charriow.com/produit/x'));
        $this->assertStringNotContainsString('Acheter maintenant', $html);
    }

    public function test_no_floating_bar_when_there_is_nothing_to_buy(): void
    {
        $ebook = Ebook::factory()->create([
            'status' => 'published', 'price' => null, 'file_path' => null, 'cta_url' => null, 'cta_label' => null,
        ]);

        $html = $this->get(route('ebooks.show', $ebook->slug))->assertOk()->getContent();

        $this->assertStringNotContainsString('fixed inset-x-0 bottom-0', $html);
        $this->assertStringContainsString('Bientôt disponible', $html);
    }
}
