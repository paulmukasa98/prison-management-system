<?php

namespace Database\Factories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Permission> */
class PermissionFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'slug' => 'DEMO-B1-PERMISSION-'.fake()->unique()->uuid(),
            'name' => 'Fictional Permission',
            'module' => 'Fictional Module',
        ];
    }
}
