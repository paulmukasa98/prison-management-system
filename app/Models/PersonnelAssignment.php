<?php

namespace App\Models;

use Database\Factories\PersonnelAssignmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'assignment_ref',
    'personnel_id',
    'facility_id',
    'duty_title',
    'reason',
    'start_date',
    'end_date',
    'status',
    'authority_reference',
    'recorded_by',
])]
class PersonnelAssignment extends Model
{
    /** @use HasFactory<PersonnelAssignmentFactory> */
    use HasFactory, SoftDeletes;

    protected $attributes = [
        'status' => 'Draft',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /** @return BelongsTo<Personnel, $this> */
    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class)->withTrashed();
    }

    /** @return BelongsTo<Facility, $this> */
    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class)->withTrashed();
    }

    /** @return BelongsTo<User, $this> */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by')->withTrashed();
    }
}
