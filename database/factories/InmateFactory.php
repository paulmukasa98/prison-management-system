<?php

namespace Database\Factories;

use App\Models\Inmate;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Inmate> */
class InmateFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $uuid = fake()->unique()->uuid();

        return [
            'identity_ref' => 'DEMO-B1-INMATE-'.$uuid,
            'prisoner_number' => null,
            'first_name' => 'Fictional',
            'middle_name' => null,
            'last_name' => 'DEMO-B1-'.$uuid,
            'gender' => null,
            'date_of_birth' => null,
            'nationality' => null,
            'district' => null,
            'id_type' => null,
            'id_number' => null,
            'kin_name' => null,
            'kin_relationship' => null,
            'kin_contact' => null,
            'notes' => null,
            'created_at' => '2026-01-01 08:00:00',
            'updated_at' => '2026-01-01 08:00:00',
        ];
    }
}
