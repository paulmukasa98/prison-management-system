<?php

namespace App\Http\Requests\User;

use App\Models\Facility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->facilityIdsForPermission('roles.assign') === null
            && $this->user()?->facilityIdsForPermission('users.manage') === null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'role_key' => ['required', 'string', Rule::exists('roles', 'slug')->withoutTrashed()],
            'facility_scope' => ['required', 'string', Rule::in(['National', ...Facility::pluck('name')->all()])],
        ];
    }
}
