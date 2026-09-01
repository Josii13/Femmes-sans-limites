<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Depuis la vente d'ebooks (prix + PDF livré par lien signé), le lien externe
     * `cta_url` est facultatif. La colonne restait NOT NULL sans valeur par défaut :
     * toute création d'ebook sans lien externe échouait en base (SQLSTATE 1364).
     */
    public function up(): void
    {
        Schema::table('ebooks', function (Blueprint $table) {
            $table->string('cta_url')->nullable()->change();
            $table->string('cta_label')->nullable()->default('Télécharger maintenant')->change();
        });
    }

    public function down(): void
    {
        Schema::table('ebooks', function (Blueprint $table) {
            $table->string('cta_url')->nullable(false)->change();
            $table->string('cta_label')->nullable(false)->default('Télécharger maintenant')->change();
        });
    }
};
