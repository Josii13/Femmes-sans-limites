<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tarifs d'adhésion.
     *
     * Les trois niveaux (Standard, Gold, Premium) existaient déjà mais ne
     * changeaient que la couleur de la carte : aucune cotisation, aucun moyen de
     * monter en gamme. Les tarifs vivent en base plutôt qu'en configuration pour
     * que l'association les ajuste sans intervention technique.
     */
    public function up(): void
    {
        Schema::create('membership_plans', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique();          // standard | gold | premium
            $table->string('name');
            $table->text('description')->nullable();
            // Prix nul ou zéro = niveau gratuit : l'adhésion Standard peut le rester.
            $table->decimal('price', 12, 2)->nullable();
            $table->string('currency', 10)->default('XOF');
            $table->unsignedSmallInteger('duration_months')->default(12);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_plans');
    }
};
