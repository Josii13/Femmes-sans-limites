<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Journal des envois d'emails.
 *
 * Rien ne disait si les emails partaient. Les envois sont enveloppés dans des
 * try/catch qui se contentent de journaliser, et personne ne lit
 * storage/logs sur un hébergement mutualisé : une carte d'anniversaire qui
 * cesse de partir passait inaperçue jusqu'à ce qu'une membre s'en plaigne.
 *
 * Le corps n'est jamais stocké : il contient des liens signés valant accès,
 * et le volume ferait gonfler la base pour rien. Le sujet suffit à reconnaître
 * un envoi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_logs', function (Blueprint $table) {
            $table->id();
            $table->string('mailable')->nullable()->index();
            $table->string('subject', 300)->nullable();
            $table->string('recipient')->nullable();
            $table->unsignedSmallInteger('recipients_count')->default(1);
            // sending → sent, ou sending → failed quand aucune confirmation
            // n'arrive : le transport a levé une exception quelque part.
            $table->string('status', 20)->default('sending');
            $table->text('error')->nullable();
            // Les envois du banc d'essai ne disent rien de la santé du système.
            $table->boolean('is_test')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['mailable', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_logs');
    }
};
