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
        Schema::table('ebooks', function (Blueprint $table) {
            $table->timestamp('newsletter_sent_at')->nullable()->after('status');
        });

        // Les ebooks déjà publiés sont considérés comme déjà notifiés (évite un envoi rétroactif).
        DB::table('ebooks')->where('status', 'published')->whereNull('newsletter_sent_at')
            ->update(['newsletter_sent_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ebooks', function (Blueprint $table) {
            $table->dropColumn('newsletter_sent_at');
        });
    }
};
