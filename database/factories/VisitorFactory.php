<?php

namespace Database\Factories;

use App\Models\Visitor;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Visitor> */
class VisitorFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $uuid = fake()->unique()->uuid();

        return [
            'visitor_ref' => 'DEMO-B1-VISITOR-'.$uuid,
            'first_name' => 'Fictional',
            'middle_name' => null,
            'last_name' => 'DEMO-B1-'.$uuid,
            'gender' => null,
            'id_type' => null,
            'id_number' => null,
            'phone' => null,
            'email' => 'demo-b1-'.$uuid.'@example.invalid',
            'residence' => null,
            'status' => 'Under Review',
            'registered_at' => '2026-01-01 08:00:00',
            'notes' => null,
            'created_at' => '2026-01-01 08:00:00',
            'updated_at' => '2026-01-01 08:00:00',
        ];
    }
}
