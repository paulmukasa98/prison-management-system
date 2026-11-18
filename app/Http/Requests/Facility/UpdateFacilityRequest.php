<?php

namespace App\Http\Requests\Facility;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFacilityRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'region' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'location_description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', 'in:Active,Inactive,Under Renovation,Decommissioned'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'capacity_date' => ['nullable', 'date'],
            'capacity_source' => ['nullable', 'string', 'max:255'],
            'capacity_reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
