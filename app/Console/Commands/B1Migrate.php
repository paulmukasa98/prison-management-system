<?php

namespace App\Console\Commands;

use App\Support\B1DatabaseGuard;
use Illuminate\Console\Command;

class B1Migrate extends Command
{
    protected $signature = 'b1:migrate {--database=b1_dev : Isolated B1 connection}';

    protected $description = 'Migrate an explicitly isolated local B1 database without resetting it';

    public function handle(B1DatabaseGuard $guard): int
    {
        $connection = (string) $this->option('database');
        $guard->assertAllowed($connection);

        return $this->call('migrate', ['--database' => $connection, '--no-interaction' => true]);
    }
}
