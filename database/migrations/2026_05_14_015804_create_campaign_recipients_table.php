<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('member_id')->nullable();
            $table->string('email');
            $table->string('name');
            $table->string('token', 64)->unique();    // tracking pixel unique
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable(); // première ouverture
            $table->unsignedSmallInteger('open_count')->default(0); // total ouvertures
            $table->timestamps();

            $table->foreign('member_id')->references('id')->on('members')->nullOnDelete();
            $table->index(['campaign_id', 'sent_at']);
            $table->index('token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_recipients');
    }
};
