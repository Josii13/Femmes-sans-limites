<?php

namespace Tests\Feature;

use App\Mail\MemberCardMail;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Services\PaymentFulfiller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Cotisation et renouvellement en ligne.
 *
 * L'association disposait d'une chaîne de paiement complète pour les ebooks et
 * les événements, mais l'adhésion elle-même ne rapportait rien : l'email de
 * relance renvoyait vers le formulaire de contact.
 *
 * Les points sensibles : le montant réellement encaissé, et le calcul de la
 * nouvelle échéance — un renouvellement anticipé ne doit amputer aucun jour.
 */
class MembershipPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Storage::fake('public');

        config([
            'services.geniuspay.secret' => 'sk_test',
            'services.geniuspay.key' => 'pk_test',
            'services.geniuspay.base_url' => 'https://geniuspay.test/api/v1/merchant',
        ]);

        MembershipPlan::create([
            'type' => 'gold', 'name' => 'Gold', 'price' => 15000,
            'currency' => 'XOF', 'duration_months' => 12, 'is_active' => true, 'sort_order' => 2,
        ]);
        MembershipPlan::create([
            'type' => 'standard', 'name' => 'Standard', 'price' => null,
            'currency' => 'XOF', 'duration_months' => 12, 'is_active' => true, 'sort_order' => 1,
        ]);
    }

    private function member(array $overrides = []): Member
    {
        $member = Member::factory()->create(array_merge([
            'status' => 'active', 'type' => 'standard', 'expires_at' => now()->addMonths(3),
        ], $overrides));

        $member->forceFill(['password' => Hash::make('motdepasse-solide')])->save();

        return $member->fresh();
    }

    private function fakeCheckout(): void
    {
        Http::fake(['*/payments' => Http::response([
            'success' => true,
            'data' => ['id' => 1, 'reference' => 'MTX-ADH', 'checkout_url' => 'https://gp.test/c/1', 'status' => 'pending'],
        ], 201)]);
    }

    // ── Tunnel de paiement ──────────────────────────────────────

    public function test_a_member_can_pay_for_an_upgrade(): void
    {
        $this->fakeCheckout();
        $member = $this->member();

        $this->actingAs($member, 'member')
            ->post(route('member.renewal.pay'), ['plan' => 'gold'])
            ->assertRedirect('https://gp.test/c/1');

        $payment = Payment::firstOrFail();

        $this->assertSame('15000.00', $payment->amount);
        $this->assertSame($member->getMorphClass(), $payment->payable_type);
        $this->assertSame('gold', data_get($payment->metadata, 'plan'));

        Http::assertSent(fn ($request) => (float) $request['amount'] === 15000.0);
    }

    public function test_a_free_plan_does_not_go_through_payment(): void
    {
        $this->fakeCheckout();
        $member = $this->member();

        $this->actingAs($member, 'member')
            ->post(route('member.renewal.pay'), ['plan' => 'standard'])
            ->assertRedirect();

        $this->assertDatabaseCount('payments', 0);
        Http::assertNothingSent();
    }

    public function test_an_inactive_plan_is_refused(): void
    {
        $this->fakeCheckout();
        MembershipPlan::where('type', 'gold')->update(['is_active' => false]);

        $this->actingAs($this->member(), 'member')
            ->post(route('member.renewal.pay'), ['plan' => 'gold'])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_a_visitor_cannot_start_a_membership_payment(): void
    {
        $this->post(route('member.renewal.pay'), ['plan' => 'gold'])->assertRedirect();

        $this->assertDatabaseCount('payments', 0);
    }

    // ── Effet du paiement ───────────────────────────────────────

    private function pay(Member $member, string $plan = 'gold', int $months = 12): Payment
    {
        $payment = Payment::create([
            'provider' => 'geniuspay', 'reference' => 'ref-adh', 'status' => 'completed',
            'amount' => 15000, 'currency' => 'XOF',
            'customer_name' => $member->name, 'customer_email' => $member->email,
            'payable_type' => $member->getMorphClass(), 'payable_id' => $member->id,
            'metadata' => ['type' => 'membership', 'plan' => $plan, 'duration_months' => $months],
        ]);

        app(PaymentFulfiller::class)->fulfill($payment->fresh('payable'));

        return $payment;
    }

    public function test_payment_applies_the_purchased_tier(): void
    {
        $member = $this->member(['type' => 'standard']);

        $this->pay($member);

        $this->assertSame('gold', $member->fresh()->type);
    }

    public function test_an_early_renewal_adds_to_the_remaining_time(): void
    {
        $expiry = now()->addMonths(3)->startOfDay();
        $member = $this->member(['expires_at' => $expiry]);

        $this->pay($member, months: 12);

        // Les 3 mois restants ne doivent pas être perdus : on repart de l'échéance.
        $this->assertSame(
            $expiry->copy()->addMonths(12)->toDateString(),
            $member->fresh()->expires_at->toDateString()
        );
    }

    public function test_an_expired_membership_restarts_from_today(): void
    {
        $member = $this->member(['expires_at' => now()->subMonths(2), 'status' => 'expired']);

        $this->pay($member, months: 12);

        $member->refresh();
        $this->assertSame('active', $member->status);
        $this->assertSame(now()->addMonths(12)->toDateString(), $member->expires_at->toDateString());
    }

    public function test_payment_clears_the_renewal_reminder(): void
    {
        $member = $this->member(['renewal_reminded_at' => now()->subDay()]);

        $this->pay($member);

        // Sans cela, la prochaine échéance ne déclencherait aucune relance.
        $this->assertNull($member->fresh()->renewal_reminded_at);
    }

    public function test_the_card_is_regenerated_after_an_upgrade(): void
    {
        $member = $this->member(['type' => 'standard', 'card_path' => null]);

        $this->pay($member, 'gold');

        // La carte porte le niveau : elle doit suivre la montée en gamme.
        $this->assertNotNull($member->fresh()->card_path);
        Mail::assertQueued(MemberCardMail::class);
    }

    public function test_an_unknown_tier_in_the_metadata_leaves_the_tier_untouched(): void
    {
        $member = $this->member(['type' => 'standard']);

        $this->pay($member, 'niveau-inexistant');

        $this->assertSame('standard', $member->fresh()->type);
    }

    // ── Affichage ───────────────────────────────────────────────

    public function test_the_renewal_page_lists_the_available_plans(): void
    {
        $html = $this->actingAs($this->member(), 'member')
            ->get(route('member.renewal'))->assertOk()->getContent();

        $this->assertStringContainsString('Gold', $html);
        $this->assertStringContainsString('15 000', $html);
        $this->assertStringContainsString('Gratuit', $html);
    }
}
