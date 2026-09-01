<?php

namespace Tests\Feature;

use App\Mail\MemberCardMail;
use App\Mail\MembershipRejectedMail;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemberLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_member_number_is_unique_and_well_formatted(): void
    {
        $number = Member::generateNumber('premium');

        $this->assertMatchesRegularExpression('/^FSL-PRM-\d{4}-\d{5}$/', $number);
    }

    public function test_activation_sets_status_and_validity_dates_and_sends_card(): void
    {
        Mail::fake();
        Storage::fake('public');

        $member = Member::factory()->create(['status' => 'pending']);

        $this->actingAs($this->admin())
            ->post(route('admin.members.activate', $member))
            ->assertRedirect();

        $member->refresh();
        $this->assertSame('active', $member->status);
        $this->assertNotNull($member->joined_at);
        $this->assertNotNull($member->expires_at);
        Mail::assertQueued(MemberCardMail::class);
    }

    public function test_rejection_sets_status_and_notifies_applicant(): void
    {
        Mail::fake();

        $member = Member::factory()->create(['status' => 'pending']);

        $this->actingAs($this->admin())
            ->post(route('admin.members.reject', $member))
            ->assertRedirect();

        $this->assertSame('rejected', $member->refresh()->status);
        Mail::assertQueued(MembershipRejectedMail::class);
    }

    public function test_public_application_creates_pending_member_without_card(): void
    {
        Mail::fake();
        Storage::fake('public');

        $this->post(route('membership.store'), [
            'photo' => UploadedFile::fake()->image('awa.jpg'),
            'name' => 'Awa Traoré',
            'email' => 'awa@example.com',
            'profession' => 'Ingénieure',
            'country' => 'Côte d\'Ivoire',
            'city' => 'Abidjan',
            'motivation' => 'Je souhaite rejoindre cette belle communauté de femmes inspirantes.',
        ])->assertRedirect(route('membership.success'));

        $member = Member::where('email', 'awa@example.com')->first();
        $this->assertNotNull($member);
        $this->assertSame('pending', $member->status);
        $this->assertSame('standard', $member->type);
        $this->assertNull($member->card_path);
        $this->assertNotNull($member->verification_token);
    }

    public function test_email_becomes_reusable_after_member_deletion(): void
    {
        Mail::fake();
        Storage::fake('public');

        $member = Member::factory()->active()->create(['email' => 'reuse@example.com']);

        // Suppression par l'admin (soft delete + libération de l'email).
        $this->actingAs($this->admin())
            ->delete(route('admin.members.destroy', $member))
            ->assertRedirect();

        // Une nouvelle candidature peut réutiliser l'email sans erreur « déjà occupé ».
        $this->post(route('membership.store'), [
            'photo' => UploadedFile::fake()->image('p.jpg'),
            'name' => 'Nouvelle Candidate',
            'email' => 'reuse@example.com',
            'profession' => 'Avocate',
            'country' => 'Côte d\'Ivoire',
            'city' => 'Abidjan',
            'motivation' => 'Je souhaite sincèrement contribuer à cette communauté inspirante de femmes.',
        ])->assertRedirect(route('membership.success'));

        $this->assertDatabaseHas('members', ['email' => 'reuse@example.com', 'deleted_at' => null]);
    }

    public function test_application_succeeds_even_with_legacy_soft_deleted_email(): void
    {
        Mail::fake();
        Storage::fake('public');

        // Simule une donnée d'avant le correctif : membre supprimé dont l'email reste intact en base.
        $old = Member::factory()->active()->create(['email' => 'legacy@example.com']);
        $old->delete(); // soft delete SANS libération de l'email

        $this->post(route('membership.store'), [
            'photo' => UploadedFile::fake()->image('p.jpg'),
            'name' => 'Nouvelle Candidate',
            'email' => 'legacy@example.com',
            'profession' => 'Coach',
            'country' => 'Côte d\'Ivoire',
            'city' => 'Abidjan',
            'motivation' => 'Je souhaite réellement intégrer cette communauté de femmes ambitieuses.',
        ])->assertRedirect(route('membership.success'));

        $this->assertDatabaseHas('members', ['email' => 'legacy@example.com', 'deleted_at' => null]);
    }

    public function test_application_stores_uploaded_photo(): void
    {
        Mail::fake();
        Storage::fake('public');

        $this->post(route('membership.store'), [
            'name' => 'Photo Candidate',
            'email' => 'photo@example.com',
            'profession' => 'Designer',
            'country' => 'Côte d\'Ivoire',
            'city' => 'Abidjan',
            'motivation' => 'Je veux rejoindre cette communauté pour partager et grandir ensemble.',
            'photo' => UploadedFile::fake()->image('moi.jpg', 600, 600),
        ])->assertRedirect(route('membership.success'));

        $member = Member::where('email', 'photo@example.com')->first();
        $this->assertNotNull($member->photo);
        Storage::disk('public')->assertExists($member->photo);
    }

    public function test_public_application_requires_a_photo(): void
    {
        Mail::fake();

        $this->post(route('membership.store'), [
            'name' => 'Sans Photo',
            'email' => 'sansphoto@example.com',
            'profession' => 'Coach',
            'country' => 'Côte d\'Ivoire',
            'city' => 'Abidjan',
            'motivation' => 'Je souhaite rejoindre cette communauté de femmes inspirantes et engagées.',
        ])->assertSessionHasErrors('photo');

        $this->assertDatabaseMissing('members', ['email' => 'sansphoto@example.com']);
    }

    public function test_motivation_requires_at_least_30_characters(): void
    {
        $this->post(route('membership.store'), [
            'name' => 'Test', 'email' => 'court@example.com',
            'profession' => 'Coach', 'country' => 'CI', 'city' => 'Abidjan',
            'motivation' => 'Trop court.',
        ])->assertSessionHasErrors('motivation');
    }

    public function test_honeypot_blocks_bot_submissions(): void
    {
        $this->post(route('membership.store'), [
            'name' => 'Bot', 'email' => 'bot@example.com', 'website' => 'http://spam',
        ])->assertRedirect(route('membership.success'));

        $this->assertDatabaseMissing('members', ['email' => 'bot@example.com']);
    }
}
