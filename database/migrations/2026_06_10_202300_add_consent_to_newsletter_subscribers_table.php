<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            // Double opt-in + preuve de consentement (RGPD).
            $table->timestamp('confirmed_at')->nullable()->after('token');
            $table->timestamp('unsubscribed_at')->nullable()->after('confirmed_at');
            $table->string('consent_ip', 45)->nullable()->after('unsubscribed_at');
            $table->timestamp('consent_at')->nullable()->after('consent_ip');
        });

        // Les abonnés déjà présents avant le double opt-in sont considérés confirmés
        // (consentement historique), pour ne pas casser les envois en cours.
        DB::table('newsletter_subscribers')
            ->whereNull('confirmed_at')
            ->update(['confirmed_at' => now(), 'consent_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->dropColumn(['confirmed_at', 'unsubscribed_at', 'consent_ip', 'consent_at']);
        });
    }
};
