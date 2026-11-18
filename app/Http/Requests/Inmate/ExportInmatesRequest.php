<?php

namespace App\Http\Requests\Inmate;

use Illuminate\Foundation\Http\FormRequest;

class ExportInmatesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->hasPermission('inmates.read') ?? false)
            && ($this->user()?->hasPermission('reports.export') ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'facility' => ['nullable', 'string', 'max:255'],
            'classification' => ['nullable', 'in:Convicted,Remand,Civil Debtor'],
            'status' => ['nullable', 'in:In Custody,Remanded,Released,Transferred'],
            'gender' => ['nullable', 'in:Male,Female,Other'],
            'sort' => ['nullable', 'in:prisoner_number,full_name,last_name,gender,facility,classification,status,admission_date'],
            'direction' => ['nullable', 'in:asc,desc'],
        ];
    }
}
