<?php

namespace Tests;

use App\Support\B1DatabaseGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class B1DatabaseTestCase extends TestCase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        config(['database.default' => 'b1_test']);
        app(B1DatabaseGuard::class)->assertAllowed('b1_test', disposable: true);
    }

    protected function migrateFreshUsing(): array
    {
        return ['--database' => 'b1_test', '--no-interaction' => true];
    }
}
