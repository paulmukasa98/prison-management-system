<?php

namespace App\Http\Requests\Inmate;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DischargeInmateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('inmates.discharge') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'discharge_reason' => [
                'required',
                'string',
                'in:Sentence Served,Bail Granted,Court Acquittal,Presidential Pardon,Appeal Upheld,Civil Order Lifted,Death',
            ],
            'discharge_date' => ['required', 'date'],
            'auth_ref' => ['required', 'string', 'max:100'],
            'auth_authority' => ['required', 'string', 'max:255'],
            'discharge_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
