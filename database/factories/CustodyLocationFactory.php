<?php

namespace Database\Factories;

use App\Models\AdmissionEpisode;
use App\Models\CustodyLocation;
use App\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CustodyLocation> */
class CustodyLocationFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'location_ref' => 'DEMO-B1-LOCATION-'.fake()->unique()->uuid(),
            'admission_episode_id' => AdmissionEpisode::factory(),
            'facility_id' => Facility::factory(),
            'entered_at' => '2026-01-01 08:00:00',
            'left_at' => null,
            'entered_transfer_id' => null,
            'left_transfer_id' => null,
            'created_at' => '2026-01-01 08:00:00',
            'updated_at' => '2026-01-01 08:00:00',
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'left_at' => '2026-02-01 08:00:00',
        ]);
    }
}
