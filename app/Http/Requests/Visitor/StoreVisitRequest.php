<?php

namespace App\Http\Requests\Visitor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('visits.schedule') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'visitor_id' => ['required', 'integer'],
            'admission_episode_id' => ['required', 'integer'],
            'facility_id' => ['required', 'integer'],
            'scheduled_start_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'scheduled_end_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:scheduled_start_at'],
            'purpose' => ['required', Rule::in(config('visitation.purposes'))],
            'relationship' => ['required', Rule::in(config('visitation.relationships'))],
            'notes' => ['nullable', 'string', 'max:2000'],
            'confirmed' => ['accepted'],
        ];
    }
}
