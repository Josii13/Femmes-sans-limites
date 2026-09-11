<?php

namespace App\Http\Controllers;

use App\Models\Ebook;
use App\Models\Event;
use Illuminate\Http\Response;

/**
 * Plan du site, généré à la volée.
 *
 * Déclaré dans robots.txt, il indique aux moteurs les pages d'événements et
 * d'ebooks — celles qui apparaissent et disparaissent au fil de l'activité et
 * qu'un fichier statique laisserait périmé.
 *
 * Seul le contenu réellement public y figure : un brouillon ou un événement
 * annulé renverrait un 404 au robot.
 */
class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [
            ...$this->staticPages(),
            ...$this->events(),
            ...$this->ebooks(),
        ];

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /** @return array<int, array{loc: string, lastmod: ?string, priority: string, changefreq: string}> */
    private function staticPages(): array
    {
        return [
            $this->url(route('home'), priority: '1.0', changefreq: 'weekly'),
            $this->url(route('about'), priority: '0.8', changefreq: 'monthly'),
            $this->url(route('events.index'), priority: '0.9', changefreq: 'weekly'),
            $this->url(route('ebooks.index'), priority: '0.9', changefreq: 'weekly'),
            $this->url(route('membership.join'), priority: '0.9', changefreq: 'monthly'),
            $this->url(route('contact'), priority: '0.5', changefreq: 'yearly'),
            $this->url(route('legal.mentions'), priority: '0.2', changefreq: 'yearly'),
            $this->url(route('legal.cgu'), priority: '0.2', changefreq: 'yearly'),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function events(): array
    {
        return Event::where('status', 'published')
            ->orderByDesc('event_date')
            ->get()
            ->map(fn (Event $event) => $this->url(
                route('events.show', $event->slug),
                // Un événement passé garde sa page, mais cesse d'être prioritaire.
                lastmod: $event->updated_at?->toAtomString(),
                priority: $event->event_date->isFuture() ? '0.8' : '0.4',
                changefreq: $event->event_date->isFuture() ? 'weekly' : 'yearly',
            ))
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function ebooks(): array
    {
        return Ebook::where('status', 'published')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Ebook $ebook) => $this->url(
                route('ebooks.show', $ebook->slug),
                lastmod: $ebook->updated_at?->toAtomString(),
                priority: '0.7',
                changefreq: 'monthly',
            ))
            ->all();
    }

    /** @return array<string, mixed> */
    private function url(string $loc, ?string $lastmod = null, string $priority = '0.5', string $changefreq = 'monthly'): array
    {
        return compact('loc', 'lastmod', 'priority', 'changefreq');
    }
}
