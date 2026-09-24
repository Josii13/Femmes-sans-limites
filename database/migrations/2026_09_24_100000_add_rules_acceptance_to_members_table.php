<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Acceptation de la charte de la communauté.
     *
     * La charte se termine par un engagement : « chaque membre reconnaît avoir pris
     * connaissance de ces règles et s'engage à les respecter ». Sans trace, cet
     * engagement n'existe pas — et la règle 7, qui prévoit jusqu'à l'exclusion en
     * cas de manquement répété, ne s'appuierait sur rien.
     *
     * La version est conservée à côté de la date : en cas de révision, on sait qui
     * a accepté quoi, et qui doit être réinvitée à l'accepter.
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->timestamp('rules_accepted_at')->nullable()->after('motivation');
            $table->string('rules_version', 10)->nullable()->after('rules_accepted_at');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['rules_accepted_at', 'rules_version']);
        });
    }
};
