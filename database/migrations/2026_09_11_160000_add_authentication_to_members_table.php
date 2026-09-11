<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ouverture de l'espace membre.
     *
     * Jusqu'ici seules les administratrices pouvaient se connecter : une membre
     * recevait sa carte par email et n'avait plus aucun accès. Le mot de passe
     * est nullable — il n'est défini qu'au premier accès, par lien envoyé à
     * l'activation, pour ne jamais transmettre de mot de passe par email.
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('password')->nullable()->after('email');
            $table->rememberToken()->after('password');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['password', 'remember_token', 'last_login_at']);
        });
    }
};
