<?php

namespace App\Http\Requests\Report;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('reports.view') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $timezone = config('reporting.timezone');
        $this->merge([
            'from' => $this->input('from', now($timezone)->startOfMonth()->toDateString()),
            'to' => $this->input('to', now($timezone)->toDateString()),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
            'facility_id' => ['nullable', 'integer'],
        ];
    }

    /** @return list<\Closure> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isEmpty() && CarbonImmutable::parse($this->input('from'))->diffInDays(CarbonImmutable::parse($this->input('to'))) > config('reporting.maximum_range_days')) {
                $validator->errors()->add('to', 'Choose a reporting period of at most '.config('reporting.maximum_range_days').' days.');
            }
        }];
    }
}
