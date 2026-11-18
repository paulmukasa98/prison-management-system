<?php

namespace Database\Factories;

use App\Models\AdmissionEpisode;
use App\Models\Discharge;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Discharge> */
class DischargeFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'discharge_ref' => 'DEMO-B1-DISCHARGE-'.fake()->unique()->uuid(),
            'admission_episode_id' => AdmissionEpisode::factory(),
            'legal_reference_id' => null,
            'reason' => null,
            'state' => 'Draft',
            'authority_reference' => null,
            'requested_at' => null,
            'authorized_at' => null,
            'executed_at' => null,
            'requested_by' => null,
            'authorized_by' => null,
            'executed_by' => null,
            'notes' => null,
            'created_at' => '2026-01-02 08:00:00',
            'updated_at' => '2026-01-02 08:00:00',
        ];
    }
}
