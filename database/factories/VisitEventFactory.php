<?php

namespace Database\Factories;

use App\Models\Visit;
use App\Models\VisitEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<VisitEvent> */
class VisitEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_ref' => 'DEMO-B1-VE-'.fake()->unique()->uuid(),
            'visit_id' => Visit::factory(),
            'sequence' => 1,
            'event_type' => 'Fictional Request',
            'from_state' => null,
            'to_state' => 'Requested',
            'actor_id' => null,
            'occurred_at' => '2026-01-02 08:00:00',
            'recorded_at' => '2026-01-02 08:00:00',
        ];
    }
}
