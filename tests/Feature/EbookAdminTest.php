<?php

namespace Tests\Feature;

use App\Mail\NewEbookMail;
use App\Models\Ebook;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EbookAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_search_combined_with_status_filter_does_not_leak_drafts(): void
    {
        Ebook::factory()->create(['title' => 'Leadership au féminin', 'status' => 'published']);
        $draft = Ebook::factory()->draft()->create(['title' => 'Leadership brouillon']);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.ebooks.index', ['search' => 'Leadership', 'status' => 'published']));

        $response->assertOk();
        // Le brouillon ne doit PAS apparaître malgré le titre qui matche la recherche.
        $response->assertDontSee('Leadership brouillon');
    }

    public function test_newsletter_is_sent_once_and_not_resent_on_republish(): void
    {
        Mail::fake();
        NewsletterSubscriber::create(['email' => 'sub@example.com', 'name' => 'Sub', 'confirmed_at' => now()]);

        $ebook = Ebook::factory()->draft()->create();

        // 1re publication → newsletter envoyée
        $this->actingAs($this->admin())->put(route('admin.ebooks.update', $ebook), [
            'title' => $ebook->title, 'category' => 'Business', 'description' => 'desc',
            'cta_label' => 'Télécharger', 'cta_url' => 'https://charriow.com/x', 'status' => 'published',
        ])->assertRedirect();

        Mail::assertQueued(NewEbookMail::class, 1);
        $this->assertNotNull($ebook->refresh()->newsletter_sent_at);

        // Dépublication puis republication → AUCUN renvoi
        $this->actingAs($this->admin())->put(route('admin.ebooks.update', $ebook), [
            'title' => $ebook->title, 'category' => 'Business', 'description' => 'desc',
            'cta_label' => 'Télécharger', 'cta_url' => 'https://charriow.com/x', 'status' => 'draft',
        ]);
        $this->actingAs($this->admin())->put(route('admin.ebooks.update', $ebook), [
            'title' => $ebook->title, 'category' => 'Business', 'description' => 'desc',
            'cta_label' => 'Télécharger', 'cta_url' => 'https://charriow.com/x', 'status' => 'published',
        ]);

        Mail::assertQueued(NewEbookMail::class, 1); // toujours 1 seul envoi
    }

    /**
     * Régression : la colonne `cta_url` était NOT NULL sans valeur par défaut alors
     * que le champ est facultatif → toute création sans lien externe renvoyait une
     * page d erreur (SQLSTATE 23000 / 1364) en production.
     */
    public function test_ebook_can_be_created_without_external_cta_url(): void
    {
        Mail::fake();

        $this->actingAs($this->admin())->post(route('admin.ebooks.store'), [
            'title' => 'Guide du leadership',
            'category' => 'Business',
            'description' => 'Un guide complet pour les femmes leaders.',
            'status' => 'published',
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.ebooks.index'));

        $this->assertDatabaseHas('ebooks', [
            'title' => 'Guide du leadership',
            'cta_url' => null,
        ]);
    }

    /** Un ebook vendu sur le site (prix + PDF) n a pas besoin de lien externe. */
    public function test_paid_ebook_with_pdf_can_be_created_and_is_purchasable(): void
    {
        Mail::fake();
        Storage::fake('local');
        Storage::fake('public');

        $this->actingAs($this->admin())->post(route('admin.ebooks.store'), [
            'title' => 'Ebook payant',
            'category' => 'Business',
            'description' => 'Description de l ebook payant.',
            'price' => 5000,
            'currency' => 'XOF',
            'pdf' => UploadedFile::fake()->create('ebook.pdf', 120, 'application/pdf'),
            'status' => 'published',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $ebook = Ebook::where('title', 'Ebook payant')->firstOrFail();
        $this->assertTrue($ebook->isPurchasable());
        Storage::disk('local')->assertExists($ebook->file_path);
    }

    /** La fiche publique reste affichable même sans lien externe ni vente. */
    public function test_public_ebook_page_renders_without_cta_url(): void
    {
        $ebook = Ebook::factory()->create(['status' => 'published', 'cta_url' => null, 'price' => null, 'file_path' => null]);

        $this->get(route('ebooks.show', $ebook->slug))
            ->assertOk()
            ->assertSee('Bientôt disponible', false);
    }

    /** Un prix sans PDF produirait une fiche publique invendable : refusé à la saisie. */
    public function test_price_without_pdf_is_rejected(): void
    {
        $this->actingAs($this->admin())->post(route('admin.ebooks.store'), [
            'title' => 'Ebook payant',
            'category' => 'Business',
            'description' => 'Description.',
            'price' => 5000,
            'status' => 'published',
        ])->assertSessionHasErrors('pdf');

        $this->assertDatabaseMissing('ebooks', ['title' => 'Ebook payant']);
    }

    /** Un prix à zéro reste un ebook gratuit : aucun PDF exigé. */
    public function test_zero_price_does_not_require_a_pdf(): void
    {
        Mail::fake();

        $this->actingAs($this->admin())->post(route('admin.ebooks.store'), [
            'title' => 'Ebook gratuit',
            'category' => 'Business',
            'description' => 'Description.',
            'price' => 0,
            'cta_label' => 'Lire maintenant',
            'cta_url' => 'https://charriow.com/x',
            'status' => 'published',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertDatabaseHas('ebooks', ['title' => 'Ebook gratuit']);
    }

    /** À l’édition, un PDF déjà en place suffit : pas besoin de le renvoyer. */
    public function test_update_keeps_existing_pdf_without_reupload(): void
    {
        Mail::fake();
        $ebook = Ebook::factory()->create(['file_path' => 'ebooks/files/deja.pdf', 'price' => 3000]);

        $this->actingAs($this->admin())->put(route('admin.ebooks.update', $ebook), [
            'title' => 'Titre modifié',
            'category' => 'Business',
            'description' => 'Description.',
            'price' => 4000,
            'status' => 'published',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame('ebooks/files/deja.pdf', $ebook->refresh()->file_path);
    }

    public function test_javascript_cta_url_is_rejected(): void
    {
        $this->actingAs($this->admin())->post(route('admin.ebooks.store'), [
            'title' => 'Test', 'category' => 'Business', 'description' => 'desc',
            'cta_label' => 'Lire', 'cta_url' => 'javascript:alert(1)', 'status' => 'draft',
        ])->assertSessionHasErrors('cta_url');
    }
}
