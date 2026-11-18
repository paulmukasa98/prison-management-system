<?php

namespace App\Http\Requests\Transfer;

use App\Models\Facility;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransferRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'destination_facility_id' => $this->input('destination_facility_id') ?: Facility::where('name', $this->input('destination_facility'))->value('id'),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->hasPermission('transfers.request') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'inmate_id' => ['required', 'integer', Rule::exists('inmates', 'id')->withoutTrashed()],
            'origin_facility_id' => ['nullable', 'integer', Rule::exists('facilities', 'id')->withoutTrashed()],
            'destination_facility_id' => ['required', 'integer', Rule::exists('facilities', 'id')->withoutTrashed()],
            'priority' => ['nullable', 'string', 'in:Routine,High,Urgent,Priority,Medical,Court Appearance,Security,Overcrowding Relief,Administrative'],
            'transfer_reason' => ['required', 'string', 'max:500'],
            'proposed_transfer_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'supporting_doc_ref' => ['nullable', 'string', 'max:100'],
        ];
    }
}
