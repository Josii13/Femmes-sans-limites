<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->softDeletes();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dateTime('registration_closes_at')->nullable()->after('capacity');
            $table->softDeletes();
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('phone');
            $table->dropSoftDeletes();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('registration_closes_at');
            $table->dropSoftDeletes();
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
