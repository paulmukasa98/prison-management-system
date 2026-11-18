<?php

namespace App\Http\Requests\Transfer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DispatchTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('transfers.dispatch') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'dispatch_date' => ['required', 'date'],
            'dispatch_time' => ['nullable', 'string', 'max:20'],
            'handover_ref' => ['required', 'string', 'max:100'],
            'escort_ref' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
