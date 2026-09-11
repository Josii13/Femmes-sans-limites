<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Les photos des membres actives alimentent désormais la galerie « Notre
     * communauté » et la page de connexion. Elles sont publiées par défaut, mais
     * l'administration doit pouvoir en retirer une (photo floue, demande de la
     * personne) sans avoir à supprimer le membre.
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->boolean('show_in_gallery')->default(true)->after('photo');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('show_in_gallery');
        });
    }
};
