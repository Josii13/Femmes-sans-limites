<?php

namespace Tests\Feature;

use App\Support\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Coordonnées publiques.
 *
 * Le numéro vivait en dur dans deux vues, dont une avec un faux numéro
 * (2250000000000) resté en place. Ces tests garantissent qu'il n'existe
 * désormais qu'une source, et que chaque page le présente sous la forme
 * attendue par le navigateur.
 */
class ContactDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_number_is_formatted_for_each_use(): void
    {
        config(['contact.phone' => '+2250705282518']);

        $this->assertSame('+225 07 05 28 25 18', Contact::phoneDisplay());
        $this->assertSame('tel:+2250705282518', Contact::tel());
        // wa.me refuse le « + » et les espaces.
        $this->assertSame('https://wa.me/2250705282518', Contact::whatsapp());
    }

    public function test_a_whatsapp_link_can_carry_a_message(): void
    {
        config(['contact.phone' => '+2250705282518']);

        $this->assertStringContainsString('?text=Bonjour', Contact::whatsapp('Bonjour'));
    }

    public function test_changing_the_configured_number_changes_every_page(): void
    {
        config(['contact.phone' => '+2250102030405']);

        $html = $this->get(route('contact'))->assertOk()->getContent();

        $this->assertStringContainsString('https://wa.me/2250102030405', $html);
        $this->assertStringContainsString('+225 01 02 03 04 05', $html);
    }

    public function test_the_footer_carries_the_number(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString(Contact::whatsapp(), $html);
        $this->assertStringContainsString(Contact::phoneDisplay(), $html);
        $this->assertStringContainsString('"telephone": "'.Contact::phone().'"', $html);
    }

    public function test_no_placeholder_number_survives_anywhere(): void
    {
        foreach ([route('home'), route('contact'), route('legal.mentions')] as $url) {
            $html = $this->get($url)->getContent();

            $this->assertStringNotContainsString('2250000000000', $html, "Numéro fictif sur {$url}");
            $this->assertStringNotContainsString('href="#"', $html, "Lien mort sur {$url}");
        }
    }

    public function test_the_legal_notice_states_the_number(): void
    {
        $html = $this->get(route('legal.mentions'))->assertOk()->getContent();

        $this->assertStringContainsString(Contact::phoneDisplay(), $html);
    }
}
