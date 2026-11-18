<?php

namespace Database\Factories;

use App\Models\AdmissionEpisode;
use App\Models\Visit;
use App\Models\Visitor;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Visit> */
class VisitFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'visit_ref' => 'DEMO-B1-VISIT-'.fake()->unique()->uuid(),
            'visitor_id' => Visitor::factory(),
            'admission_episode_id' => AdmissionEpisode::factory(),
            'facility_id' => fn (array $attributes): int => AdmissionEpisode::findOrFail($attributes['admission_episode_id'])->admitting_facility_id,
            'purpose' => null,
            'relationship' => null,
            'state' => 'Requested',
            'requested_at' => '2026-01-02 08:00:00',
            'scheduled_start_at' => '2026-01-03 09:00:00',
            'scheduled_end_at' => '2026-01-03 09:30:00',
            'checked_in_at' => null,
            'checked_out_at' => null,
            'notes' => null,
            'created_at' => '2026-01-02 08:00:00',
            'updated_at' => '2026-01-02 08:00:00',
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'state' => 'Completed',
            'checked_in_at' => '2026-01-03 09:00:00',
            'checked_out_at' => '2026-01-03 09:30:00',
        ]);
    }
}
