<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_ref', 64)->nullable()->unique();
            $table->foreignId('personnel_id')->nullable()->unique()->constrained('personnel')->restrictOnDelete()->restrictOnUpdate();
            $table->string('status', 64)->default('Disabled');
            $table->dateTime('last_login_at')->nullable();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique();
            $table->string('name');
            $table->string('module', 64);
            $table->timestamps();
        });
        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('permission_id')->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->primary(['role_id', 'permission_id']);
        });
        Schema::create('access_scopes', function (Blueprint $table) {
            $table->id();
            $table->string('scope_key', 64)->unique();
            $table->string('kind', 32);
            $table->foreignId('facility_id')->nullable()->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->unsignedBigInteger('canonical_scope')->storedAs('COALESCE(facility_id, 0)')->unique();
            $table->timestamps();
        });
        Schema::create('user_role_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('assignment_ref', 64)->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('role_id')->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('access_scope_id')->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('granted_by')->nullable()->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $table->dateTime('granted_at');
            $table->dateTime('ended_at')->nullable();
            $table->unsignedBigInteger('open_scope_id')->nullable()->storedAs('CASE WHEN ended_at IS NULL THEN access_scope_id ELSE NULL END');
            $table->timestamps();
            $table->unique(['user_id', 'role_id', 'open_scope_id'], 'b1_open_role_scope');
            $table->index(['access_scope_id', 'granted_at']);
        });

        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE access_scopes ADD CONSTRAINT b1_scope_kind CHECK ((kind = 'National' AND facility_id IS NULL) OR (kind = 'Facility' AND facility_id IS NOT NULL))");
            DB::statement('ALTER TABLE user_role_assignments ADD CONSTRAINT b1_grant_dates CHECK (ended_at IS NULL OR ended_at >= granted_at)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_role_assignments');
        Schema::dropIfExists('access_scopes');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['personnel_id']);
            $table->dropColumn(['account_ref', 'personnel_id', 'status', 'last_login_at']);
        });
    }
};
