<?php

namespace Database\Factories;

use App\Models\Transfer;
use App\Models\TransferEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TransferEvent> */
class TransferEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_ref' => 'DEMO-B1-TE-'.fake()->unique()->uuid(),
            'transfer_id' => Transfer::factory(),
            'sequence' => 1,
            'event_type' => 'Fictional Request',
            'from_state' => null,
            'to_state' => 'Draft',
            'actor_id' => null,
            'facility_id' => null,
            'occurred_at' => '2026-01-02 08:00:00',
            'recorded_at' => '2026-01-02 08:00:00',
        ];
    }
}
