<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Role> */
class RoleFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'slug' => 'DEMO-B1-ROLE-'.fake()->unique()->uuid(),
            'name' => 'Fictional Role',
            'description' => 'Fictional B1 demonstration role',
        ];
    }
}
