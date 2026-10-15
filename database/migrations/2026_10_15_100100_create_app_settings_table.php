<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Réglages persistants modifiables depuis le back-office.
 *
 * Créée pour le jeton du lien de collecte des anniversaires : le mettre dans
 * `.env` l'aurait rendu irrévocable sans accès SSH, et le cacher aurait suffi
 * à le perdre. Ici, l'équipe peut régénérer le lien d'un clic quand il a trop
 * circulé.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
