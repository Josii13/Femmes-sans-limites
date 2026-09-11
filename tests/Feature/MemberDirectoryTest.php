<?php

namespace Tests\Feature;

use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Annuaire de la communauté.
 *
 * La première raison d'adhérer à un réseau est d'y rencontrer les autres, or
 * aucune membre ne pouvait en voir une seule. L'annuaire est réservé aux
 * adhérentes connectées et ne publie aucune coordonnée directe : il sert à
 * savoir qui compose la communauté, pas à en extraire un fichier de contacts.
 */
class MemberDirectoryTest extends TestCase
{
    use RefreshDatabase;

    private function connected(): Member
    {
        $member = Member::factory()->create([
            'status' => 'active', 'expires_at' => now()->addYear(),
            'name' => 'Moi Même', 'country' => 'Côte d’Ivoire', 'profession' => 'Coach',
        ]);
        $member->forceFill(['password' => Hash::make('motdepasse-solide')])->save();

        return $member->fresh();
    }

    public function test_the_directory_requires_a_member_session(): void
    {
        $this->get(route('member.directory'))->assertRedirect(route('member.login'));
    }

    public function test_only_active_members_are_listed(): void
    {
        $me = $this->connected();
        $active = Member::factory()->create(['status' => 'active', 'name' => 'Awa Active']);
        $pending = Member::factory()->create(['status' => 'pending', 'name' => 'Bea Attente']);
        $rejected = Member::factory()->create(['status' => 'rejected', 'name' => 'Cle Refusee']);

        $html = $this->actingAs($me, 'member')->get(route('member.directory'))->assertOk()->getContent();

        $this->assertStringContainsString($active->name, $html);
        $this->assertStringNotContainsString($pending->name, $html);
        $this->assertStringNotContainsString($rejected->name, $html);
    }

    public function test_no_contact_detail_is_exposed(): void
    {
        $me = $this->connected();
        Member::factory()->create([
            'status' => 'active', 'name' => 'Awa Traoré',
            'email' => 'awa.secrete@example.com', 'phone' => '+225 07 11 22 33 44',
        ]);

        $html = $this->actingAs($me, 'member')->get(route('member.directory'))->assertOk()->getContent();

        $this->assertStringContainsString('Awa Traoré', $html);
        $this->assertStringNotContainsString('awa.secrete@example.com', $html);
        $this->assertStringNotContainsString('07 11 22 33 44', $html);
    }

    public function test_the_directory_can_be_searched(): void
    {
        $me = $this->connected();
        Member::factory()->create(['status' => 'active', 'name' => 'Awa Architecte', 'profession' => 'Architecte']);
        Member::factory()->create(['status' => 'active', 'name' => 'Bea Avocate', 'profession' => 'Avocate']);

        $html = $this->actingAs($me, 'member')
            ->get(route('member.directory', ['q' => 'Architecte']))->assertOk()->getContent();

        $this->assertStringContainsString('Awa Architecte', $html);
        $this->assertStringNotContainsString('Bea Avocate', $html);
    }

    public function test_the_directory_can_be_filtered_by_country(): void
    {
        $me = $this->connected();
        Member::factory()->create(['status' => 'active', 'name' => 'Awa Dakar', 'country' => 'Sénégal']);
        Member::factory()->create(['status' => 'active', 'name' => 'Bea Abidjan', 'country' => 'Côte d’Ivoire']);

        $html = $this->actingAs($me, 'member')
            ->get(route('member.directory', ['pays' => 'Sénégal']))->assertOk()->getContent();

        $this->assertStringContainsString('Awa Dakar', $html);
        $this->assertStringNotContainsString('Bea Abidjan', $html);
    }

    public function test_a_member_sees_herself_marked_in_the_directory(): void
    {
        $me = $this->connected();

        $html = $this->actingAs($me, 'member')->get(route('member.directory'))->assertOk()->getContent();

        $this->assertStringContainsString('C’est vous', $html);
    }
}
