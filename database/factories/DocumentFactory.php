<?php

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Document> */
class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'document_ref' => 'DEMO-B1-DOC-'.fake()->unique()->uuid(),
            'disk' => 'local',
            'storage_key' => null,
            'original_name' => null,
            'mime_type' => null,
            'size_bytes' => null,
            'sha256' => null,
            'state' => 'Metadata Only',
            'uploaded_by' => null,
            'retain_until' => null,
        ];
    }
}
