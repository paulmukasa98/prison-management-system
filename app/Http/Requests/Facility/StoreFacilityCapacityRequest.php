<?php

namespace App\Http\Requests\Facility;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreFacilityCapacityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('facilities.manage') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'capacity' => ['required', 'integer', 'min:0'],
            'effective_on' => ['required', 'date'],
            'source_reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
