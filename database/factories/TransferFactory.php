<?php

namespace Database\Factories;

use App\Models\AdmissionEpisode;
use App\Models\Facility;
use App\Models\Transfer;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Transfer> */
class TransferFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'transfer_ref' => 'DEMO-B1-TRANSFER-'.fake()->unique()->uuid(),
            'admission_episode_id' => AdmissionEpisode::factory(),
            'origin_facility_id' => Facility::factory(),
            'destination_facility_id' => Facility::factory(),
            'requesting_facility_id' => fn (array $attributes): int => $attributes['origin_facility_id'],
            'state' => 'Draft',
            'priority' => 'Routine',
            'reason' => null,
            'requested_at' => '2026-01-02 08:00:00',
            'proposed_on' => null,
            'dispatched_at' => null,
            'received_at' => null,
            'requested_by' => null,
            'notes' => null,
            'created_at' => '2026-01-02 08:00:00',
            'updated_at' => '2026-01-02 08:00:00',
        ];
    }
}
