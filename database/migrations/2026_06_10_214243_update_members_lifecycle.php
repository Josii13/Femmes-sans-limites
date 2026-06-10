<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Élargit le cycle de vie des membres :
     *  - status : enum(active,inactive) → string libre (pending|active|rejected|expired|suspended)
     *  - ajout des dates d'adhésion et d'expiration.
     */
    public function up(): void
    {
        // Colonne status en string libre (validation côté application).
        Schema::table('members', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->change();
        });

        Schema::table('members', function (Blueprint $table) {
            $table->timestamp('joined_at')->nullable()->after('marketing_opt_out_at');
            $table->timestamp('expires_at')->nullable()->after('joined_at');
            $table->timestamp('renewal_reminded_at')->nullable()->after('expires_at');
            $table->index(['status', 'type']);
        });

        // Mapping de l'ancien statut « inactive » → « pending » (candidatures en attente).
        DB::table('members')->where('status', 'inactive')->update(['status' => 'pending']);

        // Backfill des dates pour les membres déjà actifs (adhésion = création, +1 an).
        foreach (DB::table('members')->where('status', 'active')->whereNull('joined_at')->get(['id', 'created_at']) as $m) {
            $created = $m->created_at ? Carbon::parse($m->created_at) : now();
            DB::table('members')->where('id', $m->id)->update([
                'joined_at' => $created,
                'expires_at' => $created->copy()->addYear(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropIndex(['status', 'type']);
            $table->dropColumn(['joined_at', 'expires_at', 'renewal_reminded_at']);
        });

        DB::table('members')->whereNotIn('status', ['active', 'inactive'])->update(['status' => 'inactive']);

        Schema::table('members', function (Blueprint $table) {
            $table->string('status')->default('active')->change();
        });
    }
};
