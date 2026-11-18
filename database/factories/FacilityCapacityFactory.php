<?php

namespace Database\Factories;

use App\Models\Facility;
use App\Models\FacilityCapacity;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<FacilityCapacity> */
class FacilityCapacityFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'facility_id' => Facility::factory(),
            'capacity' => 100,
            'effective_on' => '2026-01-01',
            'source_reference' => 'Fictional capacity reference',
            'notes' => 'Fictional B1 demonstration data',
            'recorded_by' => null,
        ];
    }
}
