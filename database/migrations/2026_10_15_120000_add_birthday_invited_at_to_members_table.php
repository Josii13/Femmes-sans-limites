<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Date du dernier email invitant la membre à donner sa date de naissance.
 *
 * Sert de garde-fou à l'envoi depuis le back-office : un double clic, ou une
 * relance faite le lendemain par une autre administratrice, enverrait sinon
 * le même email deux fois de suite.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->timestamp('birthday_invited_at')->nullable()->after('birth_date_filled_at');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('birthday_invited_at');
        });
    }
};
