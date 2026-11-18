<?php

namespace Tests\Feature;

use App\Support\B1DatabaseGuard;
use Database\Seeders\B1DemoSeeder;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class B1DatabaseSafetyTest extends TestCase
{
    public static function unsafeConnections(): array
    {
        return [
            'original connection' => ['mysql', [], false],
            'development reset' => ['b1_dev', [], true],
            'wrong database' => ['b1_test', ['database' => 'ups_prison_management'], true],
            'remote host' => ['b1_test', ['host' => '192.0.2.1'], true],
            'shared port' => ['b1_test', ['port' => '3306'], true],
            'URL override' => ['b1_test', ['url' => 'mysql://example.invalid/shared'], true],
            'socket override' => ['b1_test', ['unix_socket' => 'shared'], true],
        ];
    }

    #[DataProvider('unsafeConnections')]
    public function test_rejects_unsafe_database_before_connecting(string $connection, array $overrides, bool $disposable): void
    {
        foreach ($overrides as $key => $value) {
            config(["database.connections.{$connection}.{$key}" => $value]);
        }
        DB::shouldReceive('connection')->never();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('B1 writes require');

        app(B1DatabaseGuard::class)->assertAllowed($connection, $disposable);
    }

    public function test_rejects_production_seeding_before_any_database_write(): void
    {
        $this->app->instance('env', 'production');
        DB::shouldReceive('getDefaultConnection')->andReturn('b1_test');
        DB::shouldReceive('connection')->never();
        DB::shouldReceive('transaction')->never();
        $this->expectException(RuntimeException::class);

        (new B1DemoSeeder)->run();
    }

    public function test_rejects_resets_outside_testing(): void
    {
        $this->app->instance('env', 'local');
        DB::shouldReceive('connection')->never();
        $this->expectException(RuntimeException::class);

        app(B1DatabaseGuard::class)->assertAllowed('b1_test', disposable: true);
    }
}
