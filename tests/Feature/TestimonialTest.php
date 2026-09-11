<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Témoignages gérés en back-office.
 *
 * La section « La parole aux membres » vivait avec trois témoignages écrits en
 * dur dans la vue — noms, métiers et citations inventés — et avait fini par être
 * désactivée. Deux exigences : que l'équipe puisse saisir de vrais témoignages,
 * et que la section disparaisse plutôt que de se remplir de vide.
 */
class TestimonialTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Awa Traoré',
            'role' => 'Entrepreneur · Abidjan',
            'quote' => 'La communauté m’a donné le réseau et la confiance qui me manquaient pour me lancer.',
            'is_published' => '1',
        ], $overrides);
    }

    // ── Affichage public ─────────────────────────────────────────

    public function test_section_is_hidden_without_testimonials(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringNotContainsString('La parole aux membres', $html);
    }

    public function test_published_testimonials_appear_on_the_home_page(): void
    {
        $shown = Testimonial::factory()->create(['name' => 'Awa Traoré', 'quote' => 'Un témoignage bien réel.']);
        $hidden = Testimonial::factory()->unpublished()->create(['name' => 'Brouillon Caché']);

        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('La parole aux membres', $html);
        $this->assertStringContainsString($shown->name, $html);
        $this->assertStringContainsString('Un témoignage bien réel.', $html);
        $this->assertStringNotContainsString($hidden->name, $html);
    }

    public function test_the_invented_testimonials_are_gone(): void
    {
        Testimonial::factory()->create();

        $html = $this->get(route('home'))->assertOk()->getContent();

        foreach (['Khadija Mbaye', 'Aminata Sow', 'Bintou Diarra'] as $invented) {
            $this->assertStringNotContainsString($invented, $html);
        }
    }

    public function test_testimonials_follow_the_configured_order(): void
    {
        Testimonial::factory()->create(['name' => 'Deuxième', 'sort_order' => 2]);
        Testimonial::factory()->create(['name' => 'Première', 'sort_order' => 1]);

        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertLessThan(strpos($html, 'Deuxième'), strpos($html, 'Première'));
    }

    public function test_a_testimonial_without_photo_falls_back_to_an_initial(): void
    {
        $testimonial = Testimonial::factory()->create([
            'name' => 'Awa Traoré', 'photo' => null, 'member_id' => null,
        ]);

        $this->assertNull($testimonial->photoUrl(), 'Sans photo ni membre liée, aucune URL.');
        $this->assertSame('A', $testimonial->initial());

        $html = $this->get(route('home'))->assertOk()->getContent();
        $section = $this->testimonials_section($html);

        // Une image cassée ferait pire que pas d'image du tout.
        $this->assertStringNotContainsString('<img src=""', $section);
        $this->assertStringContainsString($testimonial->name, $section);
    }

    /** Isole la section des témoignages : le reste de la page a ses propres images. */
    private function testimonials_section(string $html): string
    {
        $start = strpos($html, 'La parole aux membres');
        $this->assertNotFalse($start, 'La section des témoignages doit être présente.');

        return substr($html, (int) $start, (int) strpos($html, '</section>', (int) $start) - (int) $start);
    }

    public function test_a_linked_member_lends_her_photo(): void
    {
        $member = Member::factory()->create(['status' => 'active', 'photo' => 'members/photos/awa.jpg']);
        Testimonial::factory()->create(['member_id' => $member->id, 'photo' => null]);

        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString(Storage::disk('public')->url($member->photo), $html);
    }

    // ── Back-office ──────────────────────────────────────────────

    public function test_admin_can_create_a_testimonial(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.testimonials.store'), $this->payload())
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.testimonials.index'));

        $this->assertDatabaseHas('testimonials', ['name' => 'Awa Traoré', 'is_published' => true]);
    }

    public function test_a_too_short_quote_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.testimonials.store'), $this->payload(['quote' => 'Super.']))
            ->assertSessionHasErrors('quote');

        $this->assertDatabaseCount('testimonials', 0);
    }

    public function test_admin_can_upload_a_dedicated_photo(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post(route('admin.testimonials.store'), $this->payload([
                'photo' => UploadedFile::fake()->image('awa.jpg'),
            ]))->assertSessionHasNoErrors();

        $testimonial = Testimonial::firstOrFail();
        $this->assertNotNull($testimonial->photo);
        Storage::disk('public')->assertExists($testimonial->photo);
    }

    public function test_admin_can_unpublish_without_deleting(): void
    {
        $testimonial = Testimonial::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('admin.testimonials.update', $testimonial), $this->payload(['is_published' => '0']))
            ->assertSessionHasNoErrors();

        $this->assertFalse($testimonial->refresh()->is_published);
        $this->assertStringNotContainsString($testimonial->name, $this->get(route('home'))->getContent());
    }

    public function test_admin_can_delete_a_testimonial(): void
    {
        $testimonial = Testimonial::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.testimonials.destroy', $testimonial))
            ->assertRedirect();

        $this->assertDatabaseCount('testimonials', 0);
    }

    public function test_admin_screens_render(): void
    {
        $admin = $this->admin();
        $testimonial = Testimonial::factory()->create();

        $this->actingAs($admin)->get(route('admin.testimonials.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.testimonials.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.testimonials.edit', $testimonial))->assertOk();
    }

    public function test_visitors_cannot_manage_testimonials(): void
    {
        $testimonial = Testimonial::factory()->create();

        $this->get(route('admin.testimonials.index'))->assertRedirect();
        $this->post(route('admin.testimonials.store'), $this->payload())->assertRedirect();
        $this->delete(route('admin.testimonials.destroy', $testimonial))->assertRedirect();

        $this->assertDatabaseCount('testimonials', 1);
    }
}
