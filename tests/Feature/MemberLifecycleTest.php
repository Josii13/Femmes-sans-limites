<?php

namespace Tests\Feature;

use App\Mail\MemberCardMail;
use App\Mail\MembershipRejectedMail;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $this->post(route('membership.store'), [
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

    public function test_honeypot_blocks_bot_submissions(): void
    {
        $this->post(route('membership.store'), [
            'name' => 'Bot', 'email' => 'bot@example.com', 'website' => 'http://spam',
        ])->assertRedirect(route('membership.success'));

        $this->assertDatabaseMissing('members', ['email' => 'bot@example.com']);
    }
}
