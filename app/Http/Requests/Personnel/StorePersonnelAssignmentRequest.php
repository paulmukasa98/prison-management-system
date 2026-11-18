<?php

namespace App\Http\Requests\Personnel;

use App\Models\Facility;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePersonnelAssignmentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'facility_id' => $this->input('facility_id') ?: Facility::visibleTo($this->user(), 'personnel.manage')->where('name', $this->input('facility'))->value('id'),
            'duty_title' => $this->input('duty_title', $this->input('role')),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->hasPermission('personnel.manage') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'personnel_id' => ['required', 'integer', Rule::exists('personnel', 'id')->withoutTrashed()],
            'facility_id' => ['required', 'integer', Rule::exists('facilities', 'id')->withoutTrashed()],
            'duty_title' => ['required', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:500'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'authority_reference' => ['nullable', 'string', 'max:100'],
        ];
    }
}
