<?php

namespace Database\Factories;

use App\Models\Inmate;
use App\Models\Visitor;
use App\Models\VisitorRelationship;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<VisitorRelationship> */
class VisitorRelationshipFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'visitor_id' => Visitor::factory(),
            'inmate_id' => Inmate::factory(),
            'relationship' => 'DEMO-B1-Fictional relationship',
            'notes' => null,
            'created_at' => '2026-01-02 08:00:00',
            'updated_at' => '2026-01-02 08:00:00',
        ];
    }
}
