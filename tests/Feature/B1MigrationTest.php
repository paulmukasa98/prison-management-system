<?php

namespace Tests\Feature;

use App\Support\B1DatabaseGuard;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class B1MigrationTest extends TestCase
{
    public function test_disposable_schema_rolls_back_and_reapplies_all_b1_migrations(): void
    {
        config(['database.default' => 'b1_test']);
        app(B1DatabaseGuard::class)->assertAllowed('b1_test', disposable: true);
        RefreshDatabaseState::$migrated = false;

        $this->artisan('migrate:fresh', ['--database' => 'b1_test', '--no-interaction' => true])->assertSuccessful();
        $this->assertTrue(Schema::hasTable('document_links'));
        $this->artisan('migrate:rollback', ['--database' => 'b1_test', '--step' => count(glob(database_path('migrations/2026_*.php'))), '--no-interaction' => true])->assertSuccessful();
        $this->assertFalse(Schema::hasTable('inmates'));
        $this->assertFalse(Schema::hasTable('facilities'));
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertFalse(Schema::hasColumn('users', 'personnel_id'));
        $this->artisan('b1:migrate', ['--database' => 'b1_test', '--no-interaction' => true])->assertSuccessful();
        $this->assertTrue(Schema::hasTable('admission_episodes'));
        $this->assertTrue(Schema::hasTable('audit_events'));
        $this->assertTrue(Schema::hasColumn('users', 'personnel_id'));
        RefreshDatabaseState::$migrated = true;
    }
}
