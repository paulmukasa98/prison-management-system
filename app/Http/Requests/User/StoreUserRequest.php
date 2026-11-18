<?php

namespace App\Http\Requests\User;

use App\Models\Facility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->facilityIdsForPermission('users.manage') === null
            && $this->user()?->facilityIdsForPermission('roles.assign') === null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'personnel_id' => ['nullable', 'integer', Rule::exists('personnel', 'id')->withoutTrashed()->where('employment_status', 'Active'), 'unique:users,personnel_id'],
            'role_key' => ['required', 'string', Rule::exists('roles', 'slug')->withoutTrashed()],
            'facility_scope' => ['required', 'string', Rule::in(['National', ...Facility::pluck('name')->all()])],
            'initial_password' => ['required', 'string', 'min:12', 'max:255'],
        ];
    }
}
