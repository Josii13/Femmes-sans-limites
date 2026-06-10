<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('title');                           // nom interne
            $table->string('subject');                         // objet de l'email
            $table->longText('body');                          // corps du message
            $table->string('image')->nullable();               // fichier uploadé
            $table->string('cta_label', 100)->nullable();
            $table->string('cta_url', 500)->nullable();
            $table->enum('type', ['text', 'text_image', 'text_cta', 'text_image_cta'])->default('text');
            $table->enum('target_type', ['all', 'standard', 'gold', 'premium', 'custom', 'single'])->default('all');
            $table->unsignedBigInteger('target_member_id')->nullable(); // pour single
            $table->json('target_member_ids')->nullable();     // pour custom multi-select
            $table->enum('status', ['draft', 'scheduled', 'sending', 'sent'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('open_count')->default(0);
            $table->timestamps();

            $table->foreign('target_member_id')->references('id')->on('members')->nullOnDelete();
            $table->index('status');
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
