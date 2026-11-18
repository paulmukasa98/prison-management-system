<?php

namespace App\Http\Requests\Visitor;

use App\Models\Visit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransitionVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        $visit = Visit::visibleTo($this->user())->findOrFail($this->route('id'));
        $ability = match ($this->route()->getName()) {
            'visitors.visits.review.store' => 'review',
            'visitors.visits.schedule.store' => 'schedule',
            'visitors.visits.cancel.store' => 'cancel',
            default => 'checkinout',
        };

        return $this->user()->can($ability, $visit);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'decision' => [Rule::requiredIf($this->routeIs('visitors.visits.review.store')), Rule::in(['Approve', 'Deny'])],
            'reason' => ['required', 'string', 'max:1000'],
            'identity_verified' => [Rule::requiredIf($this->routeIs('visitors.visits.check-in.store') && config('visitation.require_identity_verification')), 'boolean'],
            'confirmed' => ['accepted'],
        ];
    }
}
