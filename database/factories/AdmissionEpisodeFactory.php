<?php

namespace Database\Factories;

use App\Models\AdmissionEpisode;
use App\Models\Facility;
use App\Models\Inmate;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AdmissionEpisode> */
class AdmissionEpisodeFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'admission_ref' => 'DEMO-B1-ADMISSION-'.fake()->unique()->uuid(),
            'inmate_id' => Inmate::factory(),
            'admitting_facility_id' => Facility::factory(),
            'admitted_at' => '2026-01-01 08:00:00',
            'closed_at' => null,
            'state' => 'Draft',
            'classification' => null,
            'legal_basis' => null,
            'notes' => null,
            'created_at' => '2026-01-01 08:00:00',
            'updated_at' => '2026-01-01 08:00:00',
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'admitted_at' => '2026-01-01 08:00:00',
            'closed_at' => '2026-02-01 08:00:00',
            'state' => 'Closed',
        ]);
    }
}
