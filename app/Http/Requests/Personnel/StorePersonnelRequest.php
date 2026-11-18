<?php

namespace App\Http\Requests\Personnel;

use App\Models\Facility;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePersonnelRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'facility_id' => $this->input('facility_id') ?: Facility::visibleTo($this->user(), 'personnel.manage')->where('name', $this->input('facility'))->value('id'),
            'appointed_on' => $this->input('appointed_on', $this->input('appointment_date')),
            'duty_title' => $this->input('duty_title', $this->input('assignment_role')),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->hasPermission('personnel.manage') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'staff_number' => ['nullable', 'string', 'max:64', 'unique:personnel,staff_number'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'in:Male,Female,Other'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'rank' => ['required', 'string', 'max:100'],
            'department' => ['required', 'string', 'max:100'],
            'employment_status' => ['required', 'string', 'in:Active,On Leave,Suspended,Retired,Terminated,Inactive'],
            'appointed_on' => ['nullable', 'date'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:64'],
            'kin_name' => ['nullable', 'string', 'max:255'],
            'kin_contact' => ['nullable', 'string', 'max:64'],
            'facility_id' => ['nullable', 'required_with:facility', 'integer', Rule::exists('facilities', 'id')->withoutTrashed()],
            'duty_title' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
