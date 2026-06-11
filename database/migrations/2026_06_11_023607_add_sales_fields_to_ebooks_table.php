<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ebooks', function (Blueprint $table) {
            // Prix de vente (null ou 0 = gratuit / lien externe via cta_url).
            $table->decimal('price', 12, 2)->nullable()->after('category');
            $table->string('currency', 10)->default('XOF')->after('price');
            // Fichier PDF livré après achat (disque privé).
            $table->string('file_path')->nullable()->after('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ebooks', function (Blueprint $table) {
            $table->dropColumn(['price', 'currency', 'file_path']);
        });
    }
};
