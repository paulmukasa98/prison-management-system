<?php

namespace Database\Factories;

use App\Models\AuditEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AuditEvent> */
class AuditEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'audit_ref' => 'DEMO-B1-AUD-'.fake()->unique()->uuid(),
            'actor_id' => null,
            'facility_id' => null,
            'occurred_at' => '2026-01-02 08:00:00',
            'recorded_at' => '2026-01-02 08:00:00',
            'action' => 'fictional.seed',
            'outcome' => 'Illustration',
            'resource_type' => 'System',
            'resource_reference' => null,
            'correlation_id' => 'DEMO-B1-COR-'.fake()->uuid(),
            'description' => 'Fictional development illustration, not an operational audit.',
            'changes' => null,
        ];
    }
}
