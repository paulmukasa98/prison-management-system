<?php

namespace Database\Factories;

use App\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Facility> */
class FacilityFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'code' => 'DEMO-B1-FAC-'.fake()->unique()->uuid(),
            'name' => 'Fictional Facility',
            'type' => 'Fictional Type',
            'region' => 'Fictional Region',
            'district' => 'Fictional District',
            'location_description' => 'Fictional demonstration location',
            'status' => 'Inactive',
            'established_on' => '2026-01-01',
            'notes' => 'Fictional B1 demonstration data',
        ];
    }
}
