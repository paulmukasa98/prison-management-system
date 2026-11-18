@extends('layouts.app')

@section('title', 'Edit — ' . $inmate['full_name'])

@section('content')

<x-page-heading
    title="Edit Inmate Details"
    :description="'Editing ordinary personal information for ' . $inmate['full_name'] . ' (' . $inmate['prisoner_number'] . ')'"
    :breadcrumbs="[
        ['label' => 'Dashboard',               'route' => 'dashboard'],
        ['label' => 'Inmates',                 'route' => 'inmates.index'],
        ['label' => $inmate['prisoner_number'],'url'   => route('inmates.show', $inmate['id'])],
        ['label' => 'Edit'],
    ]"
>
    <x-button.secondary href="{{ route('inmates.show', $inmate['id']) }}">Cancel</x-button.secondary>
</x-page-heading>



<div x-data="editInmate()" @photo-changed="isDirty = true" @beforeunload.window="warnUnsaved($event)">

    {{-- Restricted fields notice --}}
    <x-card class="mb-4">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-[#64748B] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-[#0F172A]">Restricted fields are locked</p>
                <p class="text-xs text-[#64748B] mt-0.5">
                    Prisoner number, custody classification, warrant details, and legal documents are legally significant records.
                    Changes to these fields require a controlled correction process and must not be made via an ordinary edit form.
                    Fields marked <span class="font-medium text-[#64748B]">🔒</span> below cannot be edited here.
                </p>
            </div>
        </div>
    </x-card>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Edit form --}}
        <div class="lg:col-span-2 space-y-5">
            <x-card><x-inmate-photo-upload :photo="$inmate['photo']" /></x-card>
            <x-card>
                <x-section-heading title="Personal Information"/>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-[#0F172A] mb-1.5">First Name <span class="text-[#DC2626]" aria-hidden="true">*</span></label>
                        <input id="first_name" x-model="form.first_name" @blur="v('first_name')" type="text"
                            :class="errors.first_name ? 'border-[#DC2626]' : 'border-[#E2E8F0]'"
                            class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent">
                        <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.first_name" x-text="errors.first_name" role="alert"></p>
                    </div>
                    <div>
                        <label for="middle_name" class="block text-sm font-medium text-[#0F172A] mb-1.5">Middle Name <span class="text-xs text-[#94A3B8]">(optional)</span></label>
                        <input id="middle_name" x-model="form.middle_name" type="text"
                            class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent">
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-[#0F172A] mb-1.5">Last Name <span class="text-[#DC2626]" aria-hidden="true">*</span></label>
                        <input id="last_name" x-model="form.last_name" @blur="v('last_name')" type="text"
                            :class="errors.last_name ? 'border-[#DC2626]' : 'border-[#E2E8F0]'"
                            class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent">
                        <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.last_name" x-text="errors.last_name" role="alert"></p>
                    </div>
                    <div>
                        <label for="gender" class="block text-sm font-medium text-[#0F172A] mb-1.5">Gender / Recorded Sex</label>
                        <select id="gender" x-model="form.gender"
                            class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent">
                            <option>Male</option>
                            <option>Female</option>
                        </select>
                    </div>
                    <div>
                        <label for="nationality" class="block text-sm font-medium text-[#0F172A] mb-1.5">Nationality</label>
                        <input id="nationality" x-model="form.nationality" type="text"
                            class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent">
                    </div>
                    <div>
                        <label for="district" class="block text-sm font-medium text-[#0F172A] mb-1.5">District of Origin</label>
                        <input id="district" x-model="form.district" type="text"
                            class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent">
                    </div>
                </div>
            </x-card>

            <x-card>
                <x-section-heading title="Identification"/>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="id_type" class="block text-sm font-medium text-[#0F172A] mb-1.5">Document Type</label>
                        <select id="id_type" x-model="form.id_type"
                            class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent">
                            <option>National ID</option>
                            <option>Passport</option>
                            <option>Driving Permit</option>
                            <option>Refugee ID</option>
                            <option>None / Not Presented</option>
                        </select>
                    </div>
                    <div>
                        <label for="id_number" class="block text-sm font-medium text-[#0F172A] mb-1.5">Document Number</label>
                        <input id="id_number" x-model="form.id_number" type="text"
                            :disabled="form.id_type === 'None / Not Presented'"
                            class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent disabled:bg-[#F8FAFC] disabled:cursor-not-allowed font-mono">
                    </div>
                </div>
            </x-card>

            <x-card>
                <x-section-heading title="Next of Kin"/>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="kin_name" class="block text-sm font-medium text-[#0F172A] mb-1.5">Name</label>
                        <input id="kin_name" x-model="form.kin_name" type="text"
                            class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent">
                    </div>
                    <div>
                        <label for="kin_rel" class="block text-sm font-medium text-[#0F172A] mb-1.5">Relationship</label>
                        <input id="kin_rel" x-model="form.kin_relationship" type="text"
                            class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent">
                    </div>
                    <div>
                        <label for="kin_contact" class="block text-sm font-medium text-[#0F172A] mb-1.5">Contact</label>
                        <input id="kin_contact" x-model="form.kin_contact" type="tel"
                            class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent">
                    </div>
                </div>
            </x-card>

            {{-- Review changes summary --}}
            <div x-show="hasChanges" class="bg-amber-50 border border-amber-200 rounded-xl p-5">
                <p class="text-sm font-semibold text-amber-800 mb-3">Changed Fields</p>
                <ul class="space-y-1.5">
                    <template x-for="change in changedFields" :key="change.field">
                        <li class="text-xs text-amber-700 flex gap-3">
                            <span class="font-medium capitalize" x-text="change.field.replace('_', ' ')"></span>:
                            <span class="line-through text-amber-500" x-text="change.old"></span>
                            <span class="text-amber-800">→</span>
                            <span class="font-medium" x-text="change.new"></span>
                        </li>
                    </template>
                </ul>
                <p class="text-xs text-amber-600 mt-3"><em>Changes are validated and audited when saved.</em></p>
            </div>

            {{-- Action row --}}
            <div class="flex gap-3 justify-end">
                <x-button.secondary href="{{ route('inmates.show', $inmate['id']) }}">Cancel</x-button.secondary>
                <button @click="saveRecord()"
                    :disabled="Object.keys(errors).length > 0"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-brand rounded-lg hover:bg-brand-hover transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    Review Changes
                </button>
            </div>

            <div x-show="saved" class="mt-2" style="display:none">
                
            </div>
        </div>

        {{-- Restricted fields panel --}}
        <div class="space-y-5">
            <x-card>
                <x-section-heading title="🔒 Restricted Fields" description="These fields require a controlled correction process."/>
                <dl>
                    <x-detail-row label="Prisoner Number" mono>{{ $inmate['prisoner_number'] }}</x-detail-row>
                    <x-detail-row label="Custody Classification"><x-custody-badge :classification="$inmate['classification']"/></x-detail-row>
                    <x-detail-row label="Admission Reference" mono>{{ $inmate['admission_ref'] }}</x-detail-row>
                    <x-detail-row label="Warrant Reference" mono>{{ $inmate['warrant_ref'] }}</x-detail-row>
                    <x-detail-row label="Issuing Authority">{{ $inmate['issuing_authority'] }}</x-detail-row>
                    @if($inmate['sentence'])
                        <x-detail-row label="Sentence">{{ $inmate['sentence'] }}</x-detail-row>
                    @endif
                </dl>
                <p class="text-xs text-[#94A3B8] mt-3">To correct these fields, raise a formal record amendment request (available in Phase 4).</p>
            </x-card>
            <x-card>
                <x-section-heading title="Custody Status"/>
                <dl>
                    <x-detail-row label="Status"><x-inmate-status-badge :status="$inmate['status']"/></x-detail-row>
                    <x-detail-row label="Facility">{{ $inmate['facility'] }}</x-detail-row>
                </dl>
            </x-card>
        </div>

    </div>
</div>

<script>
function editInmate() {
    const original = {{ Js::from(\Illuminate\Support\Arr::only($inmate, ['first_name', 'middle_name', 'last_name', 'gender', 'nationality', 'district', 'id_type', 'id_number', 'kin_name', 'kin_relationship', 'kin_contact'])) }};

    return {
        form: { ...original, ...@json(old()) },
        errors: {},
        saved: false,
        isDirty: false,

        init() { Object.assign(this.form, @json(old()));
            this.$watch('form', () => { this.isDirty = true; }, { deep: true });
        },

        warnUnsaved(e) {
            if (this.isDirty && !this.saved) { e.preventDefault(); e.returnValue = ''; }
        },

        get hasChanges() {
            return this.changedFields.length > 0;
        },

        get changedFields() {
            return Object.keys(this.form).filter(k => this.form[k] !== original[k]).map(k => ({
                field: k,
                old: original[k] || '(empty)',
                new: this.form[k]  || '(empty)',
            }));
        },

        v(field) {
            if (!this.form[field]) {
                this.errors[field] = 'This field is required.';
            } else {
                delete this.errors[field];
            }
            this.errors = { ...this.errors };
        },

        saveRecord() {
            const photo = this.$root.querySelector('input[name=photo]');
            if (photo && !photo.checkValidity()) { return; }
            this.v('first_name');
            this.v('last_name');
            if (Object.keys(this.errors).length) { return; }
            this.saved = window.pmsSubmit({{ Js::from(route('inmates.update', $inmate['id'])) }}, this.form, {photo});
            this.isDirty = false;
        },
    };
}
</script>

@endsection
