<?php

namespace App\Models;

use Database\Factories\DocumentLinkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['purpose'])]
class DocumentLink extends Model
{
    public const INMATE_PHOTO = 'Inmate Photograph';

    /** @use HasFactory<DocumentLinkFactory> */
    use HasFactory;

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function inmate(): BelongsTo
    {
        return $this->belongsTo(Inmate::class)->withTrashed();
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(AdmissionEpisode::class, 'admission_episode_id')->withTrashed();
    }

    public function legalReference(): BelongsTo
    {
        return $this->belongsTo(LegalReference::class)->withTrashed();
    }

    public function discharge(): BelongsTo
    {
        return $this->belongsTo(Discharge::class)->withTrashed();
    }

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class)->withTrashed();
    }

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class, 'personnel_id')->withTrashed();
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class)->withTrashed();
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class)->withTrashed();
    }
}
