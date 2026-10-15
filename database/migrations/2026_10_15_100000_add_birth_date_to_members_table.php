<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Date de naissance des membres, en trois colonnes plutôt qu'une date.
 *
 * L'année est facultative : beaucoup de femmes ne souhaitent pas donner leur
 * âge, et une colonne `date` obligeait à inventer une année — on se retrouvait
 * avec des 1900 partout, impossibles à distinguer d'une vraie date. Ici,
 * l'absence d'année se lit telle quelle.
 *
 * L'index (mois, jour) sert la requête quotidienne « qui fête son anniversaire
 * aujourd'hui », appelée par l'ordonnanceur tous les matins.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->unsignedTinyInteger('birth_day')->nullable()->after('profession');
            $table->unsignedTinyInteger('birth_month')->nullable()->after('birth_day');
            $table->unsignedSmallInteger('birth_year')->nullable()->after('birth_month');
            // Trace la saisie même si la date est effacée plus tard : sans elle,
            // impossible de distinguer « n'a pas répondu » de « a demandé le retrait ».
            $table->timestamp('birth_date_filled_at')->nullable()->after('birth_year');

            $table->index(['birth_month', 'birth_day'], 'members_birthday_index');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropIndex('members_birthday_index');
            $table->dropColumn(['birth_day', 'birth_month', 'birth_year', 'birth_date_filled_at']);
        });
    }
};
