<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('verification_token', 32)->nullable()->unique()->after('card_path');
        });

        // Backfill des membres existants.
        foreach (DB::table('members')->whereNull('verification_token')->pluck('id') as $id) {
            DB::table('members')->where('id', $id)->update([
                'verification_token' => Str::lower(Str::random(20)),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('verification_token');
        });
    }
};
