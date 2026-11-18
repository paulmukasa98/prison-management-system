<?php

namespace App\Http\Requests\Visitor;

use App\Models\Visitor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVisitorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('register', Visitor::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', Rule::in(['Male', 'Female', 'Other'])],
            'id_type' => ['nullable', Rule::in(config('visitation.id_types'))],
            'id_number' => ['nullable', Rule::requiredIf(fn (): bool => filled($this->input('id_type')) && $this->input('id_type') !== 'None / Not Presented'), 'string', 'max:128'],
            'phone' => ['nullable', 'string', 'max:64'],
            'email' => ['nullable', 'email', 'max:255'],
            'residence' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'facility_id' => ['required', 'integer', Rule::exists('facilities', 'id')->withoutTrashed()],
            'confirmed' => ['accepted'],
        ];
    }
}
