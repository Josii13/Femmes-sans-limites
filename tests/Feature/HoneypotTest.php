<?php

namespace Tests\Feature;

use App\Http\Controllers\Concerns\Honeypot;
use App\Models\Ebook;
use App\Models\Member;
use App\Models\NewsletterSubscriber;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Régression : le champ piège s'appelait « website » et était placé en premier
 * dans chaque formulaire. Navigateurs et gestionnaires de mots de passe le
 * remplissaient automatiquement, si bien que de vrais visiteurs étaient traités
 * comme des robots — achat d'ebook renvoyé sur la fiche sans explication, et
 * candidature d'adhésion perdue en silence derrière un message de succès.
 *
 * Ces tests verrouillent les deux côtés : une soumission humaine aboutit, une
 * soumission qui remplit le piège est bloquée.
 */
class HoneypotTest extends TestCase
{
    use RefreshDatabase;

    /** Le nom du piège ne doit correspondre à aucune heuristique de remplissage. */
    public function test_honeypot_field_name_is_not_autofill_bait(): void
    {
        $bait = ['website', 'url', 'homepage', 'email', 'name', 'phone', 'tel',
            'address', 'city', 'zip', 'postal', 'country', 'organization', 'company', 'username'];

        $this->assertNotContains(Honeypot::FIELD, $bait,
            'Le champ piège porte un nom que les navigateurs remplissent automatiquement.');
    }

    /** Le piège est rendu dans chaque formulaire public, et en dernière position. */
    public function test_honeypot_is_rendered_after_the_real_fields(): void
    {
        $html = $this->get(route('membership.join'))->assertOk()->getContent();

        $this->assertStringNotContainsString('name="website"', $html);

        // Trois formulaires sur cette page : newsletter (pied de page), adhésion, contact.
        $this->assertSame(3, substr_count($html, 'name="'.Honeypot::FIELD.'"'),
            'Chaque formulaire public doit porter le piège.');

        // Beaucoup d'outils ciblent le PREMIER champ texte : on vérifie, formulaire par
        // formulaire, que le piège arrive après le dernier champ réel.
        foreach ($this->forms($html) as $action => $form) {
            $trap = strpos($form, 'name="'.Honeypot::FIELD.'"');
            $this->assertNotFalse($trap, "Piège absent du formulaire {$action}.");

            preg_match_all('/name="(?!'.Honeypot::FIELD.'|_token)([a-z_]+)"/', $form, $m, PREG_OFFSET_CAPTURE);
            $lastRealField = end($m[0]);
            $this->assertNotFalse($lastRealField, "Aucun champ réel dans le formulaire {$action}.");

            $this->assertGreaterThan($lastRealField[1], $trap,
                "Dans {$action}, le piège doit venir après le dernier champ réel.");
        }
    }

    /**
     * Découpe le HTML en formulaires, indexés par leur attribut action.
     *
     * @return array<string, string>
     */
    private function forms(string $html): array
    {
        preg_match_all('/<form\b[^>]*action="([^"]*)"[^>]*>(.*?)<\/form>/s', $html, $matches, PREG_SET_ORDER);

        $forms = [];
        foreach ($matches as $match) {
            $forms[$match[1]] = $match[2];
        }

        $this->assertNotEmpty($forms, 'Aucun formulaire trouvé dans la page.');

        return $forms;
    }

    public function test_human_can_buy_an_ebook_while_bot_is_redirected(): void
    {
        Mail::fake();
        Storage::fake('local');
        Http::fake(['*/payments' => Http::response([
            'success' => true,
            'data' => ['id' => 1, 'reference' => 'MTX-9', 'checkout_url' => 'https://gp.test/checkout/9', 'status' => 'pending'],
        ], 201)]);

        $ebook = Ebook::factory()->create([
            'status' => 'published', 'price' => 5000, 'currency' => 'XOF',
            'file_path' => 'ebooks/files/ok.pdf',
        ]);
        Storage::disk('local')->put($ebook->file_path, '%PDF-1.4');

        $buyer = ['name' => 'Marie', 'email' => 'marie@example.com'];

        // Humain : le piège reste vide → redirection vers le paiement.
        $this->post(route('ebooks.buy.store', $ebook->slug), $buyer)
            ->assertRedirect('https://gp.test/checkout/9');
        $this->assertSame(1, Payment::count());

        // Bot : le piège est rempli → renvoyé sur la fiche, aucun paiement de plus.
        $this->post(route('ebooks.buy.store', $ebook->slug), $buyer + [Honeypot::FIELD => 'https://spam.test'])
            ->assertRedirect(route('ebooks.show', $ebook->slug));
        $this->assertSame(1, Payment::count());
    }

    public function test_human_application_is_saved_while_bot_is_discarded(): void
    {
        Mail::fake();
        Storage::fake('public');

        $payload = [
            'name' => 'Awa Traoré',
            'email' => 'awa@example.com',
            'profession' => 'Ingénieure',
            'country' => 'Côte d\'Ivoire',
            'city' => 'Abidjan',
            'motivation' => 'Je souhaite rejoindre cette communauté de femmes inspirantes et engagées.',
            'photo' => UploadedFile::fake()->image('awa.jpg'),
        ];

        $this->post(route('membership.store'), $payload)
            ->assertRedirect(route('membership.success'));
        $this->assertSame(1, Member::count());

        $this->post(route('membership.store'), array_merge($payload, [
            'email' => 'bot@example.com',
            'photo' => UploadedFile::fake()->image('bot.jpg'),
            Honeypot::FIELD => 'https://spam.test',
        ]))->assertRedirect(route('membership.success'));

        $this->assertSame(1, Member::count());
        $this->assertDatabaseMissing('members', ['email' => 'bot@example.com']);
    }

    public function test_human_newsletter_subscription_is_saved_while_bot_is_discarded(): void
    {
        Mail::fake();

        $this->post(route('newsletter.subscribe'), ['email' => 'lea@example.com'])
            ->assertSessionHasNoErrors();
        $this->assertSame(1, NewsletterSubscriber::count());

        $this->post(route('newsletter.subscribe'), [
            'email' => 'bot@example.com', Honeypot::FIELD => 'https://spam.test',
        ]);
        $this->assertSame(1, NewsletterSubscriber::count());
    }
}
