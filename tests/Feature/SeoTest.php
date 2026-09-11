<?php

namespace Tests\Feature;

use App\Models\Ebook;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Plan du site, robots et mesure d'audience.
 *
 * Le plan est généré à la volée : événements et ebooks apparaissent et
 * disparaissent au fil de l'activité, un fichier statique resterait périmé.
 * Le contenu non publié ne doit jamais y figurer — un robot y récolterait des 404.
 */
class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_lists_the_public_pages(): void
    {
        $response = $this->get('/sitemap.xml')->assertOk();

        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $xml = $response->getContent();
        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', trim($xml));

        foreach ([route('home'), route('about'), route('events.index'), route('ebooks.index'), route('membership.join')] as $url) {
            $this->assertStringContainsString('<loc>'.$url.'</loc>', $xml);
        }
    }

    public function test_sitemap_is_valid_xml(): void
    {
        Event::factory()->create(['status' => 'published', 'title' => 'Forum & Leadership <2026>']);

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        // Un titre contenant & ou < casserait le document s'il n'était pas échappé.
        $document = simplexml_load_string($xml);
        $this->assertNotFalse($document, 'Le plan du site doit être un XML valide.');
    }

    public function test_sitemap_includes_published_content_only(): void
    {
        $publishedEvent = Event::factory()->create(['status' => 'published']);
        $draftEvent = Event::factory()->create(['status' => 'draft']);
        $cancelledEvent = Event::factory()->create(['status' => 'cancelled']);

        $publishedEbook = Ebook::factory()->create(['status' => 'published']);
        $draftEbook = Ebook::factory()->draft()->create();

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        $this->assertStringContainsString(route('events.show', $publishedEvent->slug), $xml);
        $this->assertStringContainsString(route('ebooks.show', $publishedEbook->slug), $xml);

        $this->assertStringNotContainsString(route('events.show', $draftEvent->slug), $xml);
        $this->assertStringNotContainsString(route('events.show', $cancelledEvent->slug), $xml);
        $this->assertStringNotContainsString(route('ebooks.show', $draftEbook->slug), $xml);
    }

    public function test_robots_declares_the_sitemap_and_protects_private_areas(): void
    {
        $response = $this->get('/robots.txt')->assertOk();
        $body = $response->getContent();

        $this->assertStringContainsString('Sitemap: '.route('sitemap'), $body);

        foreach (['/admin', '/login', '/espace-membre', '/ebooks/telechargement/', '/membre/'] as $private) {
            $this->assertStringContainsString('Disallow: '.$private, $body);
        }
    }

    // ── Mesure d'audience ────────────────────────────────────────

    public function test_no_tracker_is_loaded_without_configuration(): void
    {
        config(['services.analytics.provider' => null]);

        $html = $this->get(route('home'))->assertOk()->getContent();

        foreach (['plausible.io', 'googletagmanager', 'matomo.js'] as $tracker) {
            $this->assertStringNotContainsString($tracker, $html);
        }
    }

    public function test_tracker_stays_off_outside_production(): void
    {
        // Configuré, mais l'environnement de test ne doit pas polluer les chiffres.
        config([
            'services.analytics.provider' => 'plausible',
            'services.analytics.domain' => 'femmesanslimites.com',
        ]);

        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringNotContainsString('plausible.io', $html);
    }
}
