<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (in_array(DB::getDefaultConnection(), ['b1_dev', 'b1_test'], true)) {
            $this->call(B1DemoSeeder::class);
        }
        $this->call(CanonicalDemoSeeder::class);
    }
}
