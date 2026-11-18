<?php

namespace App\Http\Requests\Transfer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReceiveTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('transfers.receive') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'arrival_date' => ['required', 'date'],
            'arrival_time' => ['nullable', 'string', 'max:20'],
            'arrival_ref' => ['required', 'string', 'max:100'],
            'cell_block' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
