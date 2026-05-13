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
        Schema::create('ebooks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category');
            $table->text('description');
            $table->text('author_note')->nullable();
            $table->string('image')->nullable();
            $table->string('cta_label')->default('Télécharger maintenant');
            $table->string('cta_url');
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ebooks');
    }
};
