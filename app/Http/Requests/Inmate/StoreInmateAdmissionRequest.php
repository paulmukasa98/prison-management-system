<?php

namespace App\Http\Requests\Inmate;

use App\Services\InmatePhotoService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInmateAdmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('inmates.admit') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'photo' => ['required', ...array_diff(InmatePhotoService::RULES, ['nullable'])],
            'prisoner_number' => ['nullable', 'string', 'max:64'],
            'admission_ref' => ['nullable', 'string', 'max:64', 'unique:admission_episodes,admission_ref'],
            // Personal Details
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'string', 'in:Male,Female,Other'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'id_type' => ['nullable', 'string', 'max:100'],
            'id_number' => ['nullable', 'string', 'max:100'],

            // Custody & Legal
            'classification' => ['required', 'string', 'in:Convicted,Remand,Civil Debtor'],
            'legal_basis' => ['required', 'string', 'max:255'],
            'warrant_ref' => ['nullable', 'string', 'max:100'],
            'issuing_authority' => ['nullable', 'string', 'max:255'],
            'case_ref' => ['nullable', 'string', 'max:100'],
            'admission_date' => ['nullable', 'date'],
            'next_court_date' => ['nullable', 'date'],
            'sentence' => ['nullable', 'string', 'max:500'],
            'expected_release' => ['nullable', 'date'],

            // Facility
            'facility_id' => ['nullable', 'integer', Rule::exists('facilities', 'id')->withoutTrashed()],
            'facility' => ['nullable', 'string', 'max:255', Rule::exists('facilities', 'name')->withoutTrashed()],

            // Next of kin & Notes
            'kin_name' => ['nullable', 'string', 'max:255'],
            'kin_relationship' => ['nullable', 'string', 'max:100'],
            'kin_contact' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
