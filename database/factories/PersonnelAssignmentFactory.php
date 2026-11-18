<?php

namespace Database\Factories;

use App\Models\Facility;
use App\Models\Personnel;
use App\Models\PersonnelAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PersonnelAssignment> */
class PersonnelAssignmentFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'assignment_ref' => 'DEMO-B1-POST-'.fake()->unique()->uuid(),
            'personnel_id' => Personnel::factory(),
            'facility_id' => Facility::factory(),
            'duty_title' => 'Fictional Duty',
            'reason' => 'Fictional demonstration posting',
            'start_date' => '2026-01-01',
            'end_date' => null,
            'status' => 'Draft',
            'authority_reference' => 'Fictional authority reference',
            'recorded_by' => null,
        ];
    }
}
