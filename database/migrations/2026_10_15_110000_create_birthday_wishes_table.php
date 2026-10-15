<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mur de vœux, et ce qu'il faut sur la membre pour l'ouvrir.
 *
 * `birthday_token` identifie sa page : le lien circule dans le groupe le jour
 * J, il doit être impossible à deviner sans pour autant demander un compte à
 * celles qui veulent laisser un mot.
 *
 * `birthday_greeted_at` empêche le double envoi si l'ordonnanceur passe deux
 * fois — même rôle que `renewal_reminded_at` pour les relances d'adhésion.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('birthday_token', 40)->nullable()->unique()->after('birth_date_filled_at');
            $table->timestamp('birthday_greeted_at')->nullable()->after('birthday_token');
            $table->string('birthday_card_path')->nullable()->after('birthday_greeted_at');
            // Le récapitulatif des vœux part le lendemain, une seule fois.
            $table->timestamp('birthday_digest_sent_at')->nullable()->after('birthday_card_path');
        });

        Schema::create('birthday_wishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->string('author_name', 80);
            $table->text('message');
            // Modération a posteriori : on masque sans détruire, pour pouvoir
            // revenir sur une décision prise vite un jour de fête.
            $table->timestamp('hidden_at')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['member_id', 'hidden_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('birthday_wishes');

        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn([
                'birthday_token', 'birthday_greeted_at',
                'birthday_card_path', 'birthday_digest_sent_at',
            ]);
        });
    }
};
