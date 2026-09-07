<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Promotion à durée limitée sur un ebook : `price` reste le prix normal (celui
     * qui est barré à l'affichage et qui reprend effet à l'échéance), `promo_price`
     * le prix réellement encaissé pendant la fenêtre.
     */
    public function up(): void
    {
        Schema::table('ebooks', function (Blueprint $table) {
            $table->decimal('promo_price', 12, 2)->nullable()->after('price');
            $table->timestamp('promo_starts_at')->nullable()->after('promo_price');
            $table->timestamp('promo_ends_at')->nullable()->after('promo_starts_at');

            // Sert à lister les promotions en cours et à repérer celles qui expirent.
            $table->index(['promo_ends_at']);
        });
    }

    public function down(): void
    {
        Schema::table('ebooks', function (Blueprint $table) {
            $table->dropIndex(['promo_ends_at']);
            $table->dropColumn(['promo_price', 'promo_starts_at', 'promo_ends_at']);
        });
    }
};
