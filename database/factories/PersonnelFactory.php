<?php

namespace Database\Factories;

use App\Models\Personnel;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Personnel> */
class PersonnelFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'staff_number' => 'DEMO-B1-STAFF-'.fake()->unique()->uuid(),
            'first_name' => 'Fictional Given',
            'middle_name' => null,
            'last_name' => 'Fictional Family',
            'gender' => null,
            'date_of_birth' => null,
            'nationality' => 'Fictional Nationality',
            'rank' => 'Fictional Rank',
            'department' => 'Fictional Department',
            'employment_status' => 'Inactive',
            'appointed_on' => '2026-01-01',
            'email' => 'DEMO-B1-STAFF-'.fake()->unique()->uuid().'@example.invalid',
            'phone' => null,
            'kin_name' => null,
            'kin_contact' => null,
            'notes' => 'Fictional B1 demonstration data',
        ];
    }
}
