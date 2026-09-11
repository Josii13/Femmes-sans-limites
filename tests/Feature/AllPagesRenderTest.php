<?php

namespace Tests\Feature;

use App\Models\Ebook;
use App\Models\Event;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\SiteImage;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Filet de sécurité : toutes les pages accessibles en GET doivent répondre.
 *
 * Ce balayage a déjà attrapé une page de campagnes qui reposait sur YEAR() et
 * MONTH(), propres à MySQL — invisible tant que rien ne la rendait.
 *
 * Les routes exclues renvoient un binaire (carte, QR, CSV) ou exigent une
 * signature : elles sont couvertes par leurs propres tests.
 */
class AllPagesRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_page_responds(): void
    {
        $owner = User::factory()->create(['is_admin' => true, 'role' => User::ROLE_OWNER]);
        $ebook = Ebook::factory()->sellable()->create();
        $event = Event::factory()->create(['status' => 'published']);
        $member = Member::factory()->create(['status' => 'active', 'expires_at' => now()->addYear()]);
        $member->forceFill(['password' => Hash::make('x')])->save();
        Testimonial::factory()->create();
        SiteImage::create(['key' => 'about_gallery_1', 'label' => 'G', 'page' => 'about', 'default_path' => 'a.png']);
        $plan = MembershipPlan::create(['type' => 'gold', 'name' => 'Gold', 'price' => 15000, 'currency' => 'XOF', 'duration_months' => 12, 'is_active' => true]);

        $params = [
            'slug' => $ebook->slug, 'ebook' => $ebook->slug, 'event' => $event->id,
            'member' => $member->id, 'siteImage' => SiteImage::first()->id,
            'testimonial' => Testimonial::first()->id, 'user' => $owner->id,
            'membershipPlan' => $plan->id, 'token' => $member->verification_token,
        ];

        $fails = [];
        $ok = 0;

        foreach (Route::getRoutes() as $route) {
            if (! in_array('GET', $route->methods(), true)) {
                continue;
            }
            $uri = $route->uri();
            $name = (string) $route->getName();

            if (str_starts_with($uri, '_') || str_contains($uri, 'telechargement')
                || str_contains($uri, '{token}.gif') || str_contains($uri, 'ical')
                || str_contains($uri, 'download-card') || str_contains($uri, '/qr')
                || str_contains($uri, 'ma-carte') || str_contains($uri, 'export')
                || str_contains($uri, 'storage/') || str_contains($uri, 'verify-email')) {
                continue;
            }

            $missing = false;
            $final = preg_replace_callback('/\{([a-zA-Z0-9_]+)(:[a-zA-Z0-9_]+)?\??\}/', function ($m) use ($params, &$missing) {
                $v = $params[$m[1]] ?? null;
                if ($v === null) {
                    $missing = true;
                }

                return (string) $v;
            }, $uri);
            if ($missing) {
                continue;
            }

            $url = '/'.ltrim($final, '/');

            // Espace membre = guard member ; back-office = guard web.
            $res = str_starts_with($final, 'espace-membre') && ! str_contains($final, 'connexion') && ! str_contains($final, 'mot-de-passe')
                ? $this->actingAs($member, 'member')->get($url)
                : $this->actingAs($owner)->get($url);

            if ($res->getStatusCode() >= 500) {
                $fails[] = sprintf('%-46s %s -> %d', $url, $name, $res->getStatusCode());
            } else {
                $ok++;
            }
        }

        fwrite(STDERR, "\nOK: $ok   ERREURS 5xx: ".count($fails)."\n");
        foreach ($fails as $f) {
            fwrite(STDERR, "  X $f\n");
        }

        $this->assertSame([], $fails);
    }
}
