<?php

namespace App\Models;

use Database\Factories\VisitorRelationshipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['visitor_id', 'inmate_id', 'relationship', 'notes'])]
class VisitorRelationship extends Model
{
    /** @use HasFactory<VisitorRelationshipFactory> */
    use HasFactory;

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class)->withTrashed();
    }

    public function inmate(): BelongsTo
    {
        return $this->belongsTo(Inmate::class)->withTrashed();
    }
}
