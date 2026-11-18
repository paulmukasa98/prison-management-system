<?php

namespace Database\Factories;

use App\Models\AdmissionEpisode;
use App\Models\LegalReference;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LegalReference> */
class LegalReferenceFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'reference' => 'DEMO-B1-LEGAL-'.fake()->unique()->uuid(),
            'admission_episode_id' => AdmissionEpisode::factory(),
            'type' => 'DEMO-B1-Fictional',
            'external_reference' => null,
            'issuing_authority' => null,
            'issued_on' => '2026-01-02',
            'case_reference' => null,
            'next_court_on' => null,
            'sentence_text' => null,
            'supplied_release_on' => null,
            'notes' => null,
            'created_at' => '2026-01-02 08:00:00',
            'updated_at' => '2026-01-02 08:00:00',
        ];
    }
}
