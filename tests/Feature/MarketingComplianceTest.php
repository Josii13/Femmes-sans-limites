<?php

namespace Tests\Feature;

use App\Mail\NewsletterConfirmMail;
use App\Models\Campaign;
use App\Models\Member;
use App\Models\NewsletterSubscriber;
use App\Services\CampaignDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MarketingComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_recipients_exclude_opted_out_and_inactive_members(): void
    {
        Member::factory()->active()->create();
        Member::factory()->active()->optedOut()->create();   // exclu (RGPD)
        Member::factory()->create(['status' => 'pending']);   // exclu (inactif)

        $campaign = Campaign::create([
            'title' => 'Test', 'subject' => 'Test', 'body' => 'Bonjour',
            'type' => 'text', 'target_type' => 'all', 'status' => 'draft',
        ]);

        $recipients = app(CampaignDispatcher::class)->resolveRecipients($campaign);

        $this->assertCount(1, $recipients);
    }

    public function test_newsletter_uses_double_opt_in(): void
    {
        Mail::fake();

        $this->post(route('newsletter.subscribe'), ['email' => 'lea@example.com', 'name' => 'Léa'])
            ->assertRedirect();

        $sub = NewsletterSubscriber::where('email', 'lea@example.com')->first();
        $this->assertNotNull($sub);
        $this->assertNull($sub->confirmed_at);           // pas encore confirmé
        Mail::assertQueued(NewsletterConfirmMail::class);

        // Confirmation via le lien reçu.
        $this->get(route('newsletter.confirm', $sub->token))->assertOk();
        $this->assertNotNull($sub->refresh()->confirmed_at);
    }

    public function test_unconfirmed_subscribers_are_not_mailable(): void
    {
        NewsletterSubscriber::create(['email' => 'a@example.com', 'name' => 'A']); // non confirmé
        NewsletterSubscriber::create(['email' => 'b@example.com', 'name' => 'B', 'confirmed_at' => now()]);

        $this->assertCount(1, NewsletterSubscriber::mailable()->get());
    }

    public function test_member_can_opt_out_via_campaign_link(): void
    {
        $member = Member::factory()->active()->create();
        $campaign = Campaign::create([
            'title' => 'T', 'subject' => 'T', 'body' => 'x',
            'type' => 'text', 'target_type' => 'all', 'status' => 'sent',
        ]);
        $recipient = $campaign->recipients()->create([
            'member_id' => $member->id, 'email' => $member->email, 'name' => $member->name,
        ]);

        $this->post(route('marketing.unsubscribe.confirm', $recipient->token))->assertOk();

        $this->assertNotNull($member->refresh()->marketing_opt_out_at);
    }
}
