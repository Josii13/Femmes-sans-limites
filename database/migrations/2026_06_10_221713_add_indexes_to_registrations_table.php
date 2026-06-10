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
        Schema::table('registrations', function (Blueprint $table) {
            // La relation Member↔Registration et l'anti-double-inscription filtrent par email.
            $table->index('email');
            // Comptages par statut au sein d'un événement (scanner, stats, liste).
            $table->index(['event_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['event_id', 'status']);
        });
    }
};
