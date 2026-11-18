<?php

namespace Database\Factories;

use App\Models\AccessScope;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<UserRoleAssignment> */
class UserRoleAssignmentFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'assignment_ref' => 'DEMO-B1-GRANT-'.fake()->unique()->uuid(),
            'user_id' => User::factory(),
            'role_id' => Role::factory(),
            'access_scope_id' => AccessScope::factory(),
            'granted_by' => null,
            'granted_at' => '2026-01-01 00:00:00',
            'ended_at' => null,
        ];
    }
}
