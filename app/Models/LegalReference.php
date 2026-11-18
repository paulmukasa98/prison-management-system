<?php

namespace App\Models;

use Database\Factories\LegalReferenceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'reference',
    'admission_episode_id',
    'type',
    'external_reference',
    'issuing_authority',
    'issued_on',
    'case_reference',
    'next_court_on',
    'sentence_text',
    'supplied_release_on',
    'notes',
])]
#[Hidden(['notes', 'sentence_text'])]
class LegalReference extends Model
{
    /** @use HasFactory<LegalReferenceFactory> */
    use HasFactory, SoftDeletes;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'issued_on' => 'date',
            'next_court_on' => 'date',
            'supplied_release_on' => 'date',
        ];
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(AdmissionEpisode::class, 'admission_episode_id')->withTrashed();
    }
}
