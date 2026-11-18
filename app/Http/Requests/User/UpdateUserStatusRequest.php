<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->facilityIdsForPermission('users.manage') === null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['Active', 'Suspended', 'Deactivated', 'Disabled'])],
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }
}
