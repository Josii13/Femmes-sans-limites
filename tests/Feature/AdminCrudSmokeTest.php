<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\CampaignTemplate;
use App\Models\Ebook;
use App\Models\Event;
use App\Models\Member;
use App\Models\SiteImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Balaye les créations/éditions du back-office avec le strict minimum de champs :
 * détecte les colonnes NOT NULL sans valeur par défaut face à un champ facultatif
 * (la cause de la page d'erreur à la création d'un ebook en production).
 */
class AdminCrudSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Storage::fake('public');
        Storage::fake('local');
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_minimal_ebook_creation(): void
    {
        $this->actingAs($this->admin())->post(route('admin.ebooks.store'), [
            'title' => 'Ebook minimal', 'category' => 'Business',
            'description' => 'Description.', 'status' => 'draft',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertDatabaseHas('ebooks', ['title' => 'Ebook minimal']);
    }

    public function test_minimal_event_creation_and_update(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.events.store'), [
            'title' => 'Event minimal',
            'description' => 'Description.',
            'event_date' => now()->addMonth()->format('Y-m-d H:i'),
            'location' => 'Abidjan Plateau',
            'status' => 'draft',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $event = Event::where('title', 'Event minimal')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.events.update', $event), [
            'title' => 'Event minimal modifie',
            'description' => 'Description.',
            'event_date' => now()->addMonth()->format('Y-m-d H:i'),
            'location' => 'Abidjan Plateau',
            'status' => 'published',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame('Event minimal modifie', $event->refresh()->title);
    }

    public function test_minimal_member_creation_and_update(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.members.store'), [
            'name' => 'Membre Minimal', 'email' => 'minimal@example.com',
            'profession' => 'Coach', 'country' => 'Cote Ivoire', 'city' => 'Abidjan',
            'type' => 'standard',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $member = Member::where('email', 'minimal@example.com')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.members.update', $member), [
            'name' => 'Membre Modifie', 'email' => 'minimal@example.com',
            'profession' => 'Coach', 'country' => 'Cote Ivoire', 'city' => 'Abidjan',
            'type' => 'gold', 'status' => 'active',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame('gold', $member->refresh()->type);
    }

    public function test_minimal_campaign_creation_and_update(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.communication.store'), [
            'title' => 'Campagne minimale', 'subject' => 'Sujet', 'body' => 'Corps du message.',
            'type' => 'text', 'target_type' => 'all', 'send_mode' => 'draft',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $campaign = Campaign::where('title', 'Campagne minimale')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.communication.update', $campaign), [
            'title' => 'Campagne modifiee', 'subject' => 'Sujet', 'body' => 'Corps du message.',
            'type' => 'text', 'target_type' => 'all', 'send_mode' => 'draft',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame('Campagne modifiee', $campaign->refresh()->title);
    }

    public function test_minimal_template_creation_and_update(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.communication.templates.store'), [
            'name' => 'Modele minimal', 'subject' => 'Sujet', 'type' => 'text', 'body' => 'Corps.',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $template = CampaignTemplate::where('name', 'Modele minimal')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.communication.templates.update', $template), [
            'name' => 'Modele modifie', 'subject' => 'Sujet', 'type' => 'text', 'body' => 'Corps.',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame('Modele modifie', $template->refresh()->name);
    }

    public function test_site_image_update_and_reset(): void
    {
        $admin = $this->admin();
        $image = SiteImage::create([
            'key' => 'home_hero', 'label' => 'Photo hero', 'page' => 'home', 'default_path' => 'hero.jpg',
        ]);

        $this->actingAs($admin)->put(route('admin.site-images.update', $image), [
            'image' => UploadedFile::fake()->image('hero.jpg'),
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertNotNull($image->refresh()->custom_path);
        Storage::disk('public')->assertExists($image->custom_path);

        // Le message de confirmation est rendu via {{ }} : une entité HTML brute
        // s'afficherait littéralement à l'écran.
        $this->assertStringNotContainsString('&nbsp;', (string) session('success'));

        $this->actingAs($admin)->delete(route('admin.site-images.reset', $image))->assertRedirect();
        $this->assertNull($image->refresh()->custom_path);
        $this->assertStringNotContainsString('&nbsp;', (string) session('success'));
    }

    public function test_event_duplication_and_deletion(): void
    {
        $admin = $this->admin();
        $event = Event::factory()->create();

        $this->actingAs($admin)->post(route('admin.events.duplicate', $event))->assertRedirect();
        $this->assertSame(2, Event::count());

        $this->actingAs($admin)->delete(route('admin.events.destroy', $event))->assertRedirect();
    }

    public function test_ebook_deletion(): void
    {
        $ebook = Ebook::factory()->create();

        $this->actingAs($this->admin())->delete(route('admin.ebooks.destroy', $ebook))->assertRedirect();
        $this->assertSoftDeleted($ebook);
    }
}
