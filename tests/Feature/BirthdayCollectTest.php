<?php

namespace Tests\Feature;

use App\Http\Controllers\Concerns\Honeypot;
use App\Models\Member;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Collecte des dates de naissance par un lien partagé dans le groupe WhatsApp.
 *
 * Deux exigences se tiennent : la page doit être assez simple pour qu'on y
 * réponde depuis un téléphone sans compte ni mot de passe, et assez fermée
 * pour qu'un lien ressorti du groupe ne livre pas l'annuaire des membres.
 */
class BirthdayCollectTest extends TestCase
{
    use RefreshDatabase;

    private function token(): string
    {
        return Setting::birthdayToken();
    }

    private function member(array $overrides = []): Member
    {
        return Member::factory()->create(array_merge(['status' => 'active'], $overrides));
    }

    // ── Le jeton ferme la porte ─────────────────────────────────

    public function test_a_wrong_token_reveals_nothing(): void
    {
        $this->member(['name' => 'Awa Traoré']);

        $this->get(route('birthdays.collect', 'mauvais-jeton'))->assertNotFound();
    }

    public function test_the_page_is_never_indexed(): void
    {
        $this->member();

        $html = $this->get(route('birthdays.collect', $this->token()))->assertOk()->getContent();

        $this->assertStringContainsString('noindex', $html);
    }

    public function test_a_submission_with_a_wrong_token_is_refused(): void
    {
        $member = $this->member();

        $this->post(route('birthdays.collect.store', 'mauvais-jeton'), [
            'member_id' => $member->id, 'day' => 12, 'month' => 3,
        ])->assertNotFound();

        $this->assertNull($member->fresh()->birth_day);
    }

    // ── Le mur se vide à mesure des réponses ────────────────────

    public function test_a_member_disappears_once_her_date_is_known(): void
    {
        $member = $this->member(['name' => 'Awa Traoré']);

        $this->assertStringContainsString('Awa Traoré',
            $this->get(route('birthdays.collect', $this->token()))->getContent());

        $this->post(route('birthdays.collect.store', $this->token()), [
            'member_id' => $member->id, 'day' => 12, 'month' => 3, 'year' => 1990,
        ])->assertRedirect()->assertSessionHas('saved');

        // On repart d'une session vierge : sinon le bandeau « merci Awa Traoré »
        // ferait passer le test pour la mauvaise raison.
        $this->flushSession();

        // C'est le ressort de la page : elle se vide, donc l'avancement se voit.
        $html = $this->get(route('birthdays.collect', $this->token()))->getContent();
        $this->assertStringNotContainsString('Awa Traoré', $html);
        $this->assertStringContainsString('1 sur 1 ont répondu', $html);
    }

    public function test_the_date_is_stored_in_three_parts(): void
    {
        $member = $this->member();

        $this->post(route('birthdays.collect.store', $this->token()), [
            'member_id' => $member->id, 'day' => 12, 'month' => 3, 'year' => 1990,
        ]);

        $member->refresh();
        $this->assertSame(12, $member->birth_day);
        $this->assertSame(3, $member->birth_month);
        $this->assertSame(1990, $member->birth_year);
        $this->assertNotNull($member->birth_date_filled_at);
    }

    public function test_the_year_can_be_withheld(): void
    {
        $member = $this->member();

        $this->post(route('birthdays.collect.store', $this->token()), [
            'member_id' => $member->id, 'day' => 12, 'month' => 3, 'year' => '',
        ])->assertSessionHasNoErrors();

        $member->refresh();
        // Sans année, on fête quand même — on ne devine pas d'âge.
        $this->assertNull($member->birth_year);
        $this->assertTrue($member->hasBirthday());
        $this->assertNull($member->ageOn());
    }

    // ── Garde-fous ──────────────────────────────────────────────

    public function test_an_impossible_date_is_refused(): void
    {
        $member = $this->member();

        $this->post(route('birthdays.collect.store', $this->token()), [
            'member_id' => $member->id, 'day' => 31, 'month' => 2,
        ])->assertSessionHasErrors('day');

        $this->assertNull($member->fresh()->birth_day);
    }

    public function test_the_29th_of_february_is_accepted_without_a_year(): void
    {
        $member = $this->member();

        $this->post(route('birthdays.collect.store', $this->token()), [
            'member_id' => $member->id, 'day' => 29, 'month' => 2,
        ])->assertSessionHasNoErrors();

        $this->assertSame(29, $member->fresh()->birth_day);
    }

    public function test_an_existing_date_is_not_overwritten(): void
    {
        $member = $this->member(['birth_day' => 12, 'birth_month' => 3]);

        $this->post(route('birthdays.collect.store', $this->token()), [
            'member_id' => $member->id, 'day' => 1, 'month' => 1,
        ])->assertSessionHas('error');

        // Sinon n'importe qui pourrait changer la date d'une autre après coup.
        $this->assertSame(12, $member->fresh()->birth_day);
    }

    public function test_an_inactive_member_is_neither_listed_nor_writable(): void
    {
        $member = $this->member(['status' => 'pending', 'name' => 'Pas Encore Membre']);

        $this->assertStringNotContainsString('Pas Encore Membre',
            $this->get(route('birthdays.collect', $this->token()))->getContent());

        $this->post(route('birthdays.collect.store', $this->token()), [
            'member_id' => $member->id, 'day' => 12, 'month' => 3,
        ])->assertSessionHas('error');

        $this->assertNull($member->fresh()->birth_day);
    }

    public function test_a_bot_cannot_fill_the_wall(): void
    {
        $member = $this->member();

        $this->post(route('birthdays.collect.store', $this->token()), [
            'member_id' => $member->id, 'day' => 12, 'month' => 3,
            Honeypot::FIELD => 'https://spam.test',
        ])->assertRedirect();

        $this->assertNull($member->fresh()->birth_day);
    }

    // ── Le choix de ne pas paraître est respecté ────────────────

    public function test_a_member_who_refused_the_gallery_shows_initials_not_her_photo(): void
    {
        $this->member([
            'name' => 'Awa Traoré', 'photo' => 'members/awa.jpg', 'show_in_gallery' => false,
        ]);

        $html = $this->get(route('birthdays.collect', $this->token()))->assertOk()->getContent();

        // Elle doit pouvoir donner sa date sans que son retrait soit contourné.
        $this->assertStringContainsString('Awa Traoré', $html);
        $this->assertStringNotContainsString('members/awa.jpg', $html);
        $this->assertStringContainsString('AT', $html);
    }

    // ── Côté back-office ────────────────────────────────────────

    public function test_rotating_the_link_kills_the_previous_one(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => User::ROLE_OWNER]);
        $old = $this->token();

        $this->actingAs($admin)->post(route('admin.birthdays.rotate'))->assertRedirect();

        $this->get(route('birthdays.collect', $old))->assertNotFound();
        $this->get(route('birthdays.collect', Setting::birthdayToken()))->assertOk();
    }

    public function test_the_admin_page_lists_who_is_still_missing(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => User::ROLE_OWNER]);
        $this->member(['name' => 'Sans Date']);
        $this->member(['name' => 'Avec Date', 'birth_day' => 12, 'birth_month' => 3]);

        $html = $this->actingAs($admin)->get(route('admin.birthdays.index'))->assertOk()->getContent();

        $this->assertStringContainsString('Sans Date', $html);
        $this->assertStringContainsString('Avec Date', $html);
    }

    // ── Le 29 février trouve quand même son jour ────────────────

    public function test_a_29_february_birthday_is_celebrated_on_the_28th_in_common_years(): void
    {
        $leapling = $this->member(['birth_day' => 29, 'birth_month' => 2]);

        // 2027 n'est pas bissextile : sans rattrapage, elle ne serait fêtée
        // qu'une année sur quatre.
        $found = Member::birthdayOn(new \DateTimeImmutable('2027-02-28'))->pluck('id');

        $this->assertTrue($found->contains($leapling->id));
    }

    public function test_the_28th_of_february_is_not_doubled_in_a_leap_year(): void
    {
        $this->member(['birth_day' => 29, 'birth_month' => 2, 'name' => 'Née un 29']);
        $onThe28 = $this->member(['birth_day' => 28, 'birth_month' => 2, 'name' => 'Née un 28']);

        // 2028 est bissextile : chacune a son vrai jour.
        $found = Member::birthdayOn(new \DateTimeImmutable('2028-02-28'))->pluck('id');

        $this->assertSame([$onThe28->id], $found->all());
    }
}
