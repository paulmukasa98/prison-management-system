<?php

namespace App\Http\Requests\Facility;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreFacilityRequest extends FormRequest
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
            'code' => ['nullable', 'string', 'max:64', 'unique:facilities,code'],
            'name' => ['required', 'string', 'max:255', 'unique:facilities,name'],
            'type' => ['required', 'string', 'max:100'],
            'region' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'location_description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', 'in:Active,Inactive,Under Renovation,Decommissioned'],
            'established_on' => ['nullable', 'date'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
