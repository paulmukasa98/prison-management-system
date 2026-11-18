<?php

namespace App\Http\Requests\Visitor;

use App\Models\Visitor;
use Illuminate\Validation\Rule;

class UpdateVisitorRequest extends StoreVisitorRequest
{
    public function authorize(): bool
    {
        $visitor = Visitor::visibleTo($this->user())->findOrFail($this->route('id'));

        return $this->user()->can('update', $visitor);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['facility_id']);
        $rules['id_number'] = ['nullable', 'string', 'max:128'];
        $rules['status'] = ['required', Rule::in(['Registered', 'Under Review', 'Blocked'])];
        $rules['correction_reason'] = ['required', 'string', 'max:1000'];

        return $rules;
    }
}
