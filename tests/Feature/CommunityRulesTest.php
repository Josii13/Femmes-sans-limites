<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Support\CommunityRules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Charte de la communauté.
 *
 * Elle se termine par un engagement : « chaque membre reconnaît avoir pris
 * connaissance de ces règles et s'engage à les respecter ». La publier sans
 * recueillir ni conserver cet engagement le rendrait décoratif — or la règle 7
 * prévoit jusqu'à l'exclusion en cas de manquement répété.
 */
class CommunityRulesTest extends TestCase
{
    use RefreshDatabase;

    private function application(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Awa Traoré',
            'email' => 'awa@example.com',
            'profession' => 'Ingénieure',
            'country' => 'Côte d’Ivoire',
            'city' => 'Abidjan',
            'motivation' => 'Je souhaite rejoindre cette communauté de femmes inspirantes et engagées.',
            'photo' => UploadedFile::fake()->image('awa.jpg'),
            'accept_rules' => '1',
        ], $overrides);
    }

    // ── Page publique ───────────────────────────────────────────

    public function test_the_charter_is_public(): void
    {
        $html = $this->get(route('legal.charte'))->assertOk()->getContent();

        foreach (['Respect', 'Confidentialité', 'Participation', 'Communication',
            'Partage', 'Intégrité', 'Responsabilité'] as $rule) {
            $this->assertStringContainsString($rule, $html);
        }

        $this->assertStringContainsString('Notre engagement', $html);
    }

    public function test_the_charter_holds_exactly_seven_rules(): void
    {
        $this->assertCount(7, CommunityRules::all());

        foreach (CommunityRules::all() as $rule) {
            $this->assertNotEmpty($rule['title']);
            $this->assertNotEmpty($rule['summary']);
            $this->assertNotEmpty($rule['body']);
        }
    }

    public function test_the_charter_is_linked_from_the_footer(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString(route('legal.charte'), $html);
    }

    /**
     * La page À propos présente les sept intitulés et renvoie à la charte
     * complète. Elle n'en reproduit pas le texte : une visiteuse qui lit les
     * règles doit les lire à leur source, pas dans une copie qui pourrait dériver.
     */
    public function test_the_about_page_summarises_the_charter_and_links_to_it(): void
    {
        $html = $this->get(route('about'))->assertOk()->getContent();

        $this->assertStringContainsString('Les règles de la communauté', $html);
        $this->assertStringContainsString(route('legal.charte'), $html);

        foreach (CommunityRules::all() as $rule) {
            $this->assertStringContainsString($rule['title'], $html);
        }

        // Variante compacte : les intitulés, pas les paragraphes.
        $this->assertStringNotContainsString(CommunityRules::all()[0]['body'][0], $html);
    }

    /**
     * Une classe fournie au composant remplace sa disposition par défaut :
     * fusionner « space-y-* » avec une grille cumulerait marges et gouttières.
     */
    public function test_the_component_layout_can_be_overridden(): void
    {
        $html = $this->get(route('about'))->assertOk()->getContent();
        $section = substr($html, (int) strpos($html, 'Les règles de la communauté'));

        $this->assertMatchesRegularExpression('/<ol class="grid[^"]*"/', $section);
        $this->assertDoesNotMatchRegularExpression('/<ol class="[^"]*space-y-[^"]*"/', $section);
    }

    // ── Acceptation à l'adhésion ────────────────────────────────

    public function test_an_application_records_the_acceptance(): void
    {
        Mail::fake();
        Storage::fake('public');

        $this->post(route('membership.store'), $this->application())
            ->assertRedirect(route('membership.success'));

        $member = Member::where('email', 'awa@example.com')->firstOrFail();

        $this->assertNotNull($member->rules_accepted_at, 'La date d’acceptation doit être conservée.');
        $this->assertSame(CommunityRules::VERSION, $member->rules_version);
        $this->assertTrue($member->hasAcceptedCurrentRules());
    }

    public function test_an_application_without_acceptance_is_refused(): void
    {
        Mail::fake();
        Storage::fake('public');

        $this->post(route('membership.store'), $this->application(['accept_rules' => null]))
            ->assertSessionHasErrors('accept_rules');

        $this->assertDatabaseCount('members', 0);
    }

    public function test_the_join_form_carries_the_checkbox_and_the_link(): void
    {
        $html = $this->get(route('membership.join'))->assertOk()->getContent();

        $this->assertStringContainsString('name="accept_rules"', $html);
        $this->assertStringContainsString(route('legal.charte'), $html);
    }

    // ── Espace membre ───────────────────────────────────────────

    private function member(array $overrides = []): Member
    {
        $member = Member::factory()->create(array_merge([
            'status' => 'active', 'expires_at' => now()->addYear(),
        ], $overrides));

        $member->forceFill(['password' => Hash::make('motdepasse-solide')])->save();

        return $member->fresh();
    }

    public function test_a_member_who_joined_before_the_charter_is_asked_to_accept(): void
    {
        $member = $this->member(['rules_accepted_at' => null, 'rules_version' => null]);

        $html = $this->actingAs($member, 'member')->get(route('member.rules'))->assertOk()->getContent();

        // On le lui signale plutôt que de supposer son accord.
        $this->assertStringContainsString('n’a pas encore été enregistré', $html);
        // L'URL d'acceptation est la même que celle de la page (POST) et figure
        // dans le menu : c'est la présence du bouton qui fait foi.
        $this->assertStringContainsString('Je reconnais avoir pris connaissance', $html);
    }

    public function test_a_member_can_accept_from_her_portal(): void
    {
        $member = $this->member(['rules_accepted_at' => null, 'rules_version' => null]);

        $this->actingAs($member, 'member')->post(route('member.rules.accept'))->assertRedirect();

        $member->refresh();
        $this->assertTrue($member->hasAcceptedCurrentRules());
        $this->assertSame(CommunityRules::VERSION, $member->rules_version);
    }

    public function test_an_acceptance_of_an_older_version_does_not_count(): void
    {
        $member = $this->member([
            'rules_accepted_at' => now()->subYear(),
            'rules_version' => '0.9',
        ]);

        // Une acceptation portant sur une version antérieure ne vaut pas pour l'actuelle.
        $this->assertFalse($member->hasAcceptedCurrentRules());

        $html = $this->actingAs($member, 'member')->get(route('member.rules'))->assertOk()->getContent();
        $this->assertStringContainsString('ont évolué depuis votre acceptation', $html);
    }

    public function test_an_up_to_date_member_sees_her_acceptance_date(): void
    {
        $member = $this->member([
            'rules_accepted_at' => now()->subMonth(),
            'rules_version' => CommunityRules::VERSION,
        ]);

        $html = $this->actingAs($member, 'member')->get(route('member.rules'))->assertOk()->getContent();

        $this->assertStringContainsString('Vous avez accepté ces règles', $html);
        $this->assertStringContainsString($member->rules_accepted_at->translatedFormat('d F Y'), $html);
        // Plus rien à accepter : le bouton disparaît.
        $this->assertStringNotContainsString('Je reconnais avoir pris connaissance', $html);
    }

    public function test_the_charter_page_requires_a_member_session(): void
    {
        $this->get(route('member.rules'))->assertRedirect(route('member.login'));
    }
}
