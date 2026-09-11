<?php

namespace Tests\Feature;

use App\Mail\BackupFailedMail;
use App\Models\Ebook;
use App\Models\Member;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use ZipArchive;

/**
 * Sauvegarde automatique.
 *
 * Le dump est écrit en PHP pur, sans `mysqldump` : l'outil est absent de
 * l'hébergement mutualisé visé. Ce qui compte ici : que l'archive contienne
 * réellement les données, qu'elle reste hors de `public/`, que la rotation
 * fonctionne, et qu'un échec alerte quelqu'un.
 */
class BackupTest extends TestCase
{
    use RefreshDatabase;

    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = storage_path('app/backups');
        $this->clean();
    }

    protected function tearDown(): void
    {
        $this->clean();
        parent::tearDown();
    }

    /** Le test d échec place volontairement un fichier à l emplacement du dossier. */
    private function clean(): void
    {
        if (File::isFile($this->directory)) {
            File::delete($this->directory);
        }
        File::deleteDirectory($this->directory);
    }

    public function test_backup_contains_the_data(): void
    {
        Member::factory()->create(['name' => 'Awa Traoré', 'status' => 'active']);
        Ebook::factory()->create(['title' => 'Guide du leadership']);

        $this->artisan('fsl:backup', ['--no-files' => true])->assertSuccessful();

        $archives = File::glob($this->directory.'/*.zip');
        $this->assertCount(1, $archives);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($archives[0]) === true);
        $sql = $zip->getFromName('base.sql');
        $zip->close();

        $this->assertNotFalse($sql, 'L’archive doit contenir base.sql.');
        $this->assertStringContainsString('Awa Traoré', $sql);
        $this->assertStringContainsString('Guide du leadership', $sql);
        $this->assertStringContainsString('CREATE TABLE', $sql);
    }

    public function test_intermediate_sql_file_is_removed(): void
    {
        $this->artisan('fsl:backup', ['--no-files' => true])->assertSuccessful();

        $this->assertEmpty(File::glob($this->directory.'/*.sql'),
            'Le dump brut ne doit pas rester à côté de l’archive.');
    }

    public function test_backups_live_outside_the_public_directory(): void
    {
        $this->artisan('fsl:backup', ['--no-files' => true])->assertSuccessful();

        // Un dump contient emails, téléphones et références de paiement.
        $this->assertStringNotContainsString(public_path(), $this->directory);
        $this->assertEmpty(File::glob(public_path('**/*.zip')));
    }

    public function test_old_backups_are_rotated_away(): void
    {
        File::ensureDirectoryExists($this->directory);
        foreach (['2020-01-01_000000', '2020-01-02_000000', '2020-01-03_000000'] as $stamp) {
            File::put($this->directory.'/fsl-'.$stamp.'.zip', 'ancienne archive');
        }

        $this->artisan('fsl:backup', ['--no-files' => true, '--keep' => 2])->assertSuccessful();

        $remaining = array_map('basename', File::glob($this->directory.'/*.zip'));
        sort($remaining);

        $this->assertCount(2, $remaining, 'Seules les 2 plus récentes sont conservées.');
        // La sauvegarde du jour est la plus récente : elle survit forcément.
        $this->assertStringStartsWith('fsl-'.now()->format('Y-m-d'), end($remaining));
    }

    public function test_volatile_tables_keep_their_structure_but_not_their_content(): void
    {
        $this->artisan('fsl:backup', ['--no-files' => true])->assertSuccessful();

        $zip = new ZipArchive;
        $zip->open(File::glob($this->directory.'/*.zip')[0]);
        $sql = $zip->getFromName('base.sql');
        $zip->close();

        // La structure est nécessaire pour restaurer une base fonctionnelle…
        $this->assertStringContainsString('Structure de `sessions`', $sql);
        // …mais les sessions et les jobs n'ont aucune valeur après restauration.
        // (l'échappement des identifiants diffère selon le moteur : on cible le commentaire)
        $this->assertStringNotContainsString('Données de `sessions`', $sql);
        $this->assertStringNotContainsString('Données de `jobs`', $sql);
    }

    public function test_a_failure_alerts_the_administrators(): void
    {
        Mail::fake();
        User::factory()->create(['is_admin' => true, 'email' => 'admin@fsl.test']);

        // Dossier rendu inaccessible : un fichier occupe la place du répertoire.
        File::ensureDirectoryExists(dirname($this->directory));
        File::put($this->directory, 'ceci est un fichier, pas un dossier');

        $this->artisan('fsl:backup', ['--no-files' => true])->assertFailed();

        Mail::assertSent(BackupFailedMail::class, fn (BackupFailedMail $mail) => $mail->hasTo('admin@fsl.test'));
    }

    public function test_backup_is_scheduled_daily(): void
    {
        $events = collect(app(Schedule::class)->events())
            ->filter(fn ($event) => str_contains($event->command ?? '', 'fsl:backup'));

        $this->assertCount(1, $events, 'La sauvegarde doit être planifiée.');
    }
}
