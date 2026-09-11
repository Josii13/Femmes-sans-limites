<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Témoignages de membres, saisis en back-office.
     *
     * La section « La parole aux membres » de l'accueil vivait avec trois
     * témoignages écrits en dur dans la vue — noms, métiers et citations
     * inventés. Elle avait d'ailleurs été désactivée. Ces contenus se gèrent
     * désormais comme le reste, avec de vraies personnes.
     */
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();

            // Rattachement facultatif à une membre : permet de reprendre sa photo
            // et de garder le lien, sans empêcher de citer une intervenante externe.
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->string('role')->nullable();        // « Entrepreneur · Abidjan »
            $table->text('quote');
            $table->string('photo')->nullable();       // sinon, photo de la membre liée
            $table->boolean('is_published')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
