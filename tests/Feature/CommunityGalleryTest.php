<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\SiteImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * La galerie « Notre communauté » (page À propos) et les avatars de la page de
 * connexion sont alimentés par les photos des membres actives, sans manipulation
 * en back-office : une adhésion activée entre dans la rotation d'elle-même.
 *
 * Les deux garde-fous à tenir : ne publier que des membres réellement actives, et
 * ne jamais laisser la section vide si aucune photo n'est disponible.
 */
class CommunityGalleryTest extends TestCase
{
    use RefreshDatabase;

    private function activeMemberWithPhoto(string $name, string $file): Member
    {
        return Member::factory()->create([
            'name' => $name,
            'status' => 'active',
            'photo' => 'members/photos/'.$file,
            'joined_at' => now()->subDay(),
        ]);
    }

    public function test_active_member_photos_appear_in_the_gallery(): void
    {
        $member = $this->activeMemberWithPhoto('Awa', 'awa.jpg');

        $html = $this->get(route('about'))->assertOk()->getContent();

        $this->assertStringContainsString(Storage::disk('public')->url($member->photo), $html);
    }

    public function test_only_active_members_are_published(): void
    {
        $active = $this->activeMemberWithPhoto('Active', 'active.jpg');

        $pending = Member::factory()->create(['status' => 'pending', 'photo' => 'members/photos/pending.jpg']);
        $rejected = Member::factory()->create(['status' => 'rejected', 'photo' => 'members/photos/rejected.jpg']);
        $expired = Member::factory()->create(['status' => 'expired', 'photo' => 'members/photos/expired.jpg']);
        $noPhoto = Member::factory()->create(['status' => 'active', 'photo' => null]);

        $html = $this->get(route('about'))->assertOk()->getContent();

        $this->assertStringContainsString(Storage::disk('public')->url($active->photo), $html);

        foreach ([$pending, $rejected, $expired] as $hidden) {
            $this->assertStringNotContainsString(Storage::disk('public')->url($hidden->photo), $html);
        }
        $this->assertNotNull($noPhoto); // une membre sans photo ne casse pas le rendu
    }

    public function test_a_member_can_be_withdrawn_from_the_public_site(): void
    {
        $shown = $this->activeMemberWithPhoto('Visible', 'visible.jpg');
        $hidden = $this->activeMemberWithPhoto('Retirée', 'retiree.jpg');
        $hidden->update(['show_in_gallery' => false]);

        $html = $this->get(route('about'))->assertOk()->getContent();

        $this->assertStringContainsString(Storage::disk('public')->url($shown->photo), $html);
        $this->assertStringNotContainsString(Storage::disk('public')->url($hidden->photo), $html);
    }

    public function test_newest_members_come_first(): void
    {
        $old = Member::factory()->create([
            'status' => 'active', 'photo' => 'members/photos/ancienne.jpg', 'joined_at' => now()->subYear(),
        ]);
        $recent = Member::factory()->create([
            'status' => 'active', 'photo' => 'members/photos/recente.jpg', 'joined_at' => now()->subHour(),
        ]);

        $html = $this->get(route('about'))->assertOk()->getContent();

        $this->assertLessThan(
            strpos($html, Storage::disk('public')->url($old->photo)),
            strpos($html, Storage::disk('public')->url($recent->photo)),
            'La galerie doit montrer les arrivées les plus récentes en premier.'
        );
    }

    public function test_gallery_falls_back_to_editorial_images_without_members(): void
    {
        SiteImage::create([
            'key' => 'about_gallery_1', 'label' => 'À propos — Galerie, photo 1',
            'page' => 'about', 'default_path' => 'photo_02_2.png',
        ]);

        $html = $this->get(route('about'))->assertOk()->getContent();

        $this->assertStringContainsString('photo_02_2.png', $html);
    }

    public function test_editorial_images_complete_the_member_photos(): void
    {
        $member = $this->activeMemberWithPhoto('Awa', 'awa.jpg');
        SiteImage::create([
            'key' => 'about_gallery_1', 'label' => 'À propos — Galerie, photo 1',
            'page' => 'about', 'default_path' => 'photo_02_2.png',
        ]);

        $html = $this->get(route('about'))->assertOk()->getContent();

        $this->assertStringContainsString(Storage::disk('public')->url($member->photo), $html);
        $this->assertStringContainsString('photo_02_2.png', $html);
    }

    // ── Page de connexion ────────────────────────────────────────

    public function test_login_page_shows_member_avatars(): void
    {
        $member = $this->activeMemberWithPhoto('Awa', 'awa.jpg');

        $html = $this->get(route('login'))->assertOk()->getContent();

        $this->assertStringContainsString(Storage::disk('public')->url($member->photo), $html);
    }

    public function test_login_page_keeps_its_default_avatars_without_members(): void
    {
        $html = $this->get(route('login'))->assertOk()->getContent();

        $this->assertStringContainsString('images/photo_01_2.png', $html);
    }

    // ── Back-office ──────────────────────────────────────────────

    public function test_admin_can_withdraw_a_photo_without_touching_the_membership(): void
    {
        Mail::fake();
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);
        $member = $this->activeMemberWithPhoto('Awa', 'awa.jpg');

        $this->actingAs($admin)->put(route('admin.members.update', $member), [
            'name' => $member->name, 'email' => $member->email,
            'profession' => 'Coach', 'country' => 'Cote Ivoire', 'city' => 'Abidjan',
            'type' => $member->type, 'status' => 'active',
            // Case décochée : le champ caché envoie 0.
            'show_in_gallery' => '0',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $member->refresh();
        $this->assertFalse($member->show_in_gallery);
        $this->assertSame('active', $member->status, 'Retirer la photo ne doit pas modifier l’adhésion.');
        $this->assertNotNull($member->photo, 'La photo reste sur la carte de membre.');
    }

    public function test_photo_is_published_by_default_for_a_new_application(): void
    {
        Mail::fake();
        Storage::fake('public');

        $this->post(route('membership.store'), [
            'name' => 'Awa Traoré',
            'email' => 'awa@example.com',
            'profession' => 'Ingénieure',
            'country' => 'Cote Ivoire',
            'city' => 'Abidjan',
            'motivation' => 'Je souhaite rejoindre cette communauté de femmes inspirantes et engagées.',
            'photo' => UploadedFile::fake()->image('awa.jpg'),
        ])->assertRedirect(route('membership.success'));

        $member = Member::where('email', 'awa@example.com')->firstOrFail();
        $this->assertTrue($member->show_in_gallery);

        // Mais tant que la candidature n'est pas activée, rien n'est publié.
        $html = $this->get(route('about'))->assertOk()->getContent();
        $this->assertStringNotContainsString(Storage::disk('public')->url($member->photo), $html);
    }
}
