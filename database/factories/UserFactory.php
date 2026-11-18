<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'account_ref' => 'DEMO-B1-USER-'.fake()->unique()->uuid(),
            'personnel_id' => null,
            'name' => 'Fictional Development Account',
            'email' => 'fictional-'.Str::uuid().'@example.invalid',
            'email_verified_at' => null,
            'password' => Hash::make(Str::random(64)),
            'status' => 'Disabled',
            'remember_token' => null,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => ['email_verified_at' => null]);
    }
}
