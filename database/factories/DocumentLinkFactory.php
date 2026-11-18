<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\DocumentLink;
use App\Models\Inmate;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DocumentLink> */
class DocumentLinkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'document_id' => Document::factory(),
            'inmate_id' => Inmate::factory(),
            'purpose' => 'Fictional metadata illustration',
        ];
    }
}
