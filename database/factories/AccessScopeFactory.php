<?php

namespace Database\Factories;

use App\Models\AccessScope;
use App\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AccessScope> */
class AccessScopeFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'scope_key' => 'DEMO-B1-SCOPE-'.fake()->unique()->uuid(),
            'kind' => 'National',
            'facility_id' => null,
        ];
    }

    public function facility(): static
    {
        return $this->state(fn (array $attributes): array => [
            'kind' => 'Facility',
            'facility_id' => Facility::factory(),
        ]);
    }
}
