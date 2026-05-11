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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('member_number')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('profession');
            $table->string('country');
            $table->string('city');
            $table->string('photo')->nullable();
            $table->enum('type', ['standard', 'gold', 'premium'])->default('standard');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('card_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
