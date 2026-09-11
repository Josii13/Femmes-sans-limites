<?php

namespace Tests\Feature;

use App\Models\Ebook;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tableau de bord du back-office.
 *
 * Il ne renvoyait que des comptages : aucune recette, aucune tendance, aucune
 * alerte — alors que la plateforme encaisse désormais des adhésions, des ebooks
 * et des inscriptions.
 */
class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = User::ROLE_OWNER): User
    {
        return User::factory()->create(['is_admin' => true, 'role' => $role]);
    }

    private function payment(string $payableType, float $amount, array $overrides = []): Payment
    {
        static $n = 0;
        $n++;

        return Payment::create(array_merge([
            'provider' => 'geniuspay', 'reference' => 'ref-'.$n, 'status' => 'completed',
            'amount' => $amount, 'currency' => 'XOF',
            'customer_name' => 'Cliente', 'customer_email' => 'c'.$n.'@example.com',
            'payable_type' => $payableType, 'payable_id' => 1,
            'paid_at' => now(),
        ], $overrides));
    }

    public function test_revenue_counts_only_completed_payments(): void
    {
        $this->payment((new Ebook)->getMorphClass(), 5000);
        $this->payment((new Member)->getMorphClass(), 15000);
        // Un paiement abandonné n'est pas une recette.
        $this->payment((new Ebook)->getMorphClass(), 99000, ['status' => 'pending', 'paid_at' => null]);

        $html = $this->actingAs($this->user())->get(route('admin.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('20 000', $html);   // total réel
        $this->assertStringNotContainsString('119 000', $html); // total gonflé par l'abandon
    }

    public function test_revenue_is_split_by_source(): void
    {
        $this->payment((new Ebook)->getMorphClass(), 5000);
        $this->payment((new Member)->getMorphClass(), 15000);
        $this->payment((new Registration)->getMorphClass(), 2000);

        $html = $this->actingAs($this->user())->get(route('admin.dashboard'))->assertOk()->getContent();

        foreach (['Adhésions', 'Ebooks', 'Événements'] as $label) {
            $this->assertStringContainsString($label, $html);
        }
    }

    /**
     * Le cloisonnement des routes ne sert à rien si le tableau de bord affiche
     * les mêmes données à tout le monde.
     */
    public function test_an_editor_sees_no_financial_data(): void
    {
        $this->payment((new Ebook)->getMorphClass(), 5000);

        $html = $this->actingAs($this->user(User::ROLE_EDITOR))
            ->get(route('admin.dashboard'))->assertOk()->getContent();

        $this->assertStringNotContainsString('Recettes encaissées', $html);
        $this->assertStringNotContainsString('Nouvelles adhésions', $html);
    }

    public function test_an_admin_sees_the_financial_data(): void
    {
        $this->payment((new Ebook)->getMorphClass(), 5000);

        $html = $this->actingAs($this->user(User::ROLE_ADMIN))
            ->get(route('admin.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('Recettes encaissées', $html);
    }

    public function test_pending_applications_are_flagged(): void
    {
        Member::factory()->count(3)->create(['status' => 'pending']);

        $html = $this->actingAs($this->user())->get(route('admin.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('3 candidatures à traiter', $html);
    }

    public function test_memberships_about_to_expire_are_flagged(): void
    {
        Member::factory()->create(['status' => 'active', 'expires_at' => now()->addDays(10)]);
        Member::factory()->create(['status' => 'active', 'expires_at' => now()->addYear()]);

        $html = $this->actingAs($this->user())->get(route('admin.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('1 adhésion à renouveler sous 30 jours', $html);
    }

    public function test_an_unsellable_ebook_is_flagged(): void
    {
        // Un prix sans fichier : ni vendable, ni livrable.
        Ebook::factory()->create(['status' => 'published', 'price' => 5000, 'file_path' => null]);

        $html = $this->actingAs($this->user())->get(route('admin.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('1 ebook avec un prix mais sans fichier', $html);
    }

    public function test_nothing_is_flagged_when_everything_is_in_order(): void
    {
        $html = $this->actingAs($this->user())->get(route('admin.dashboard'))->assertOk()->getContent();

        $this->assertStringNotContainsString('à traiter', $html);
        $this->assertStringNotContainsString('sans fichier', $html);
    }

    public function test_the_dashboard_survives_an_empty_database(): void
    {
        $this->actingAs($this->user())->get(route('admin.dashboard'))->assertOk();
    }
}
