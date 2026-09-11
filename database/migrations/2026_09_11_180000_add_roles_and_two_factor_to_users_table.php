<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rôles et double authentification du back-office.
     *
     * Le back-office ne connaissait qu'un booléen `is_admin` : toute personne y
     * ayant accès pouvait tout faire, y compris consulter les paiements et
     * supprimer des membres. Et aucun second facteur ne protégeait un panneau
     * contenant données personnelles et références de paiement.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // owner : gère aussi les comptes du back-office
            // admin : tout sauf la gestion des comptes
            // editor : contenu éditorial uniquement
            $table->string('role', 20)->default('admin')->after('is_admin');

            // Chiffré au repos : un accès en lecture à la base ne doit pas suffire
            // à reconstituer les codes à usage unique.
            $table->text('two_factor_secret')->nullable()->after('role');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
        });

        // La première administratrice existante devient propriétaire : sans cela
        // plus personne ne pourrait gérer les comptes après la migration.
        $firstAdmin = DB::table('users')->where('is_admin', true)->orderBy('id')->first();
        if ($firstAdmin) {
            DB::table('users')->where('id', $firstAdmin->id)->update(['role' => 'owner']);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at']);
        });
    }
};
