<?php

namespace Tests\Feature;

use App\Mail\NewEbookMail;
use App\Models\Ebook;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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

    public function test_javascript_cta_url_is_rejected(): void
    {
        $this->actingAs($this->admin())->post(route('admin.ebooks.store'), [
            'title' => 'Test', 'category' => 'Business', 'description' => 'desc',
            'cta_label' => 'Lire', 'cta_url' => 'javascript:alert(1)', 'status' => 'draft',
        ])->assertSessionHasErrors('cta_url');
    }
}
