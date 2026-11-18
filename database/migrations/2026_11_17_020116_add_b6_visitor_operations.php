<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $fingerprints = [];
        DB::table('visitors')->whereNotNull('id_number')->orderBy('id')->chunkById(100, function ($visitors) use (&$fingerprints): void {
            foreach ($visitors as $visitor) {
                if (! $visitor->id_type || str_contains($visitor->id_number, '*')) {
                    continue;
                }
                $fingerprint = hash_hmac('sha256', mb_strtolower(trim($visitor->id_type)).'|'.mb_strtoupper(preg_replace('/\s+/', '', $visitor->id_number)), config('app.key'));
                if (isset($fingerprints[$fingerprint])) {
                    throw new RuntimeException('Resolve duplicate visitor identity documents before applying B6. No schema changes have been made.');
                }
                $fingerprints[$fingerprint] = $visitor->id;
            }
        });
        Schema::table('visitors', function (Blueprint $table): void {
            $table->foreignId('registered_facility_id')->nullable()->constrained('facilities')->restrictOnDelete()->restrictOnUpdate();
            $table->char('identity_fingerprint', 64)->nullable()->unique();
            $table->text('id_number')->nullable()->change();
        });
        Schema::table('visit_events', function (Blueprint $table): void {
            $table->string('actor_role')->nullable();
        });

        DB::table('visitors')->whereNotNull('id_number')->orderBy('id')->chunkById(100, function ($visitors): void {
            foreach ($visitors as $visitor) {
                $fingerprint = $visitor->id_type && ! str_contains($visitor->id_number, '*')
                    ? hash_hmac('sha256', mb_strtolower(trim($visitor->id_type)).'|'.mb_strtoupper(preg_replace('/\s+/', '', $visitor->id_number)), config('app.key'))
                    : null;
                DB::table('visitors')->where('id', $visitor->id)->update([
                    'id_number' => Crypt::encryptString($visitor->id_number), 'identity_fingerprint' => $fingerprint,
                ]);
            }
        });

        $permissionId = DB::table('permissions')->insertGetId([
            'slug' => 'visits.authorize', 'name' => 'Approve or deny visit requests', 'module' => 'Visitors',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        foreach (DB::table('roles')->whereIn('slug', ['system_admin', 'commissioner', 'facility_officer_in_charge'])->pluck('id') as $roleId) {
            DB::table('permission_role')->insert(['permission_id' => $permissionId, 'role_id' => $roleId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('visitors')->whereNotNull('id_number')->orderBy('id')->chunkById(100, function ($visitors): void {
            foreach ($visitors as $visitor) {
                DB::table('visitors')->where('id', $visitor->id)->update(['id_number' => Crypt::decryptString($visitor->id_number)]);
            }
        });
        $permissionId = DB::table('permissions')->where('slug', 'visits.authorize')->value('id');
        DB::table('permission_role')->where('permission_id', $permissionId)->delete();
        DB::table('permissions')->where('id', $permissionId)->delete();
        Schema::table('visit_events', fn (Blueprint $table) => $table->dropColumn('actor_role'));
        Schema::table('visitors', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('registered_facility_id');
            $table->dropColumn('identity_fingerprint');
        });
    }
};
