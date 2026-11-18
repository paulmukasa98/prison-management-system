@extends('layouts.app')

@section('title', 'Record Dispatch — ' . $transfer['transfer_ref'])

@section('content')

<x-page-heading
    title="Record Dispatch"
    :description="'Dispatch from ' . $transfer['origin_facility'] . ' for ' . $transfer['transfer_ref']"
    :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Transfers', 'route' => 'transfers.index'],
        ['label' => $transfer['transfer_ref'], 'url' => route('transfers.show', $transfer['id'])],
        ['label' => 'Record Dispatch'],
    ]"
>
    <x-button.secondary href="{{ route('transfers.show', $transfer['id']) }}">← Back</x-button.secondary>
</x-page-heading>



<div x-data="dispatchForm()" x-init="init()">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Transfer summary sidebar --}}
        <div class="space-y-4">
            <x-card>
                <x-section-heading title="Transfer"/>
                <dl>
                    <x-detail-row label="Reference" mono>{{ $transfer['transfer_ref'] }}</x-detail-row>
                    <x-detail-row label="Status"><x-transfer-status-badge :status="$transfer['status']"/></x-detail-row>
                    <x-detail-row label="Authorization" mono>{{ $transfer['auth_ref'] ?? '—' }}</x-detail-row>
                </dl>
            </x-card>
            <x-card>
                <x-section-heading title="Inmate"/>
                <dl>
                    <x-detail-row label="Name">{{ $transfer['inmate_name'] }}</x-detail-row>
                    <x-detail-row label="Prisoner No." mono>{{ $transfer['prisoner_number'] }}</x-detail-row>
                    <x-detail-row label="Classification"><x-custody-badge :classification="$transfer['classification']"/></x-detail-row>
                </dl>
            </x-card>
            <x-card>
                <x-section-heading title="Movement"/>
                <div class="py-2">
                    <x-facility-movement :from="$transfer['origin_facility']" :to="$transfer['destination_facility']"/>
                </div>
            </x-card>
        </div>

        {{-- Dispatch form --}}
        <div class="lg:col-span-2 space-y-5">
            <x-card x-show="!submitted">
                <x-section-heading title="Dispatch Details"/>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="dispatch_date" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                            Dispatch Date <span class="text-[#DC2626]" aria-hidden="true">*</span>
                        </label>
                        <input id="dispatch_date" x-model="form.dispatch_date" @change="ve('dispatch_date')" type="date"
                            :max="todayStr"
                            :class="errors.dispatch_date ? 'border-[#DC2626]' : 'border-[#E2E8F0]'"
                            class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent" required>
                        <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.dispatch_date" x-text="errors.dispatch_date" role="alert"></p>
                    </div>
                    <div>
                        <label for="dispatch_time" class="block text-sm font-medium text-[#0F172A] mb-1.5">Dispatch Time</label>
                        <input id="dispatch_time" x-model="form.dispatch_time" type="time"
                            class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent">
                    </div>
                    <div>
                        <label for="handover_ref" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                            Handover Reference <span class="text-[#DC2626]" aria-hidden="true">*</span>
                        </label>
                        <input id="handover_ref" x-model="form.handover_ref" @blur="ve('handover_ref')" type="text"
                            :class="errors.handover_ref ? 'border-[#DC2626]' : 'border-[#E2E8F0]'"
                            class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent font-mono"
                            placeholder="HO-TRF-2025-0001" required>
                        <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.handover_ref" x-text="errors.handover_ref" role="alert"></p>
                    </div>
                    <div>
                        <label for="escort_ref" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                            Escort Reference <span class="text-xs text-[#94A3B8]">(if applicable)</span>
                        </label>
                        <input id="escort_ref" x-model="form.escort_ref" type="text"
                            class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent font-mono"
                            placeholder="ESC-2025-0001">
                        <p class="mt-1 text-xs text-[#64748B]">Do not record unnecessary operational security details in this field.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#0F172A] mb-1.5">Dispatching Facility</label>
                        <input type="text" value="{{ $transfer['origin_facility'] }}" disabled
                            class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg bg-[#F8FAFC] text-[#64748B] cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#0F172A] mb-1.5">Dispatching Officer</label>
                        <div class="flex items-center gap-2 p-2.5 bg-[#F8FAFC] border border-[#E2E8F0] rounded-lg">
                            <div class="w-7 h-7 rounded-full bg-brand flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-bold text-white">AN</span>
                            </div>
                            <p class="text-sm text-[#0F172A]">Agnes Nakamya <em class="not-italic text-xs text-[#94A3B8]">(demo)</em></p>
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="departure_notes" class="block text-sm font-medium text-[#0F172A] mb-1.5">Departure Notes</label>
                        <textarea id="departure_notes" x-model="form.departure_notes" rows="3"
                            class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent resize-y"
                            placeholder="Notes regarding the inmate's condition and documentation at departure…"></textarea>
                    </div>
                </div>

                {{-- Review step --}}
                <div x-show="showReview" class="mt-5 p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                    <p class="text-sm font-semibold text-indigo-800 mb-2">Dispatch Preview Summary</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-2 text-xs">
                        <div><p class="text-indigo-600">Date</p><p x-text="form.dispatch_date + (form.dispatch_time ? ' ' + form.dispatch_time : '')"></p></div>
                        <div><p class="text-indigo-600">Handover Ref</p><p class="font-mono" x-text="form.handover_ref"></p></div>
                        <div x-show="form.escort_ref"><p class="text-indigo-600">Escort Ref</p><p class="font-mono" x-text="form.escort_ref"></p></div>
                        <div><p class="text-indigo-600">From</p><p>{{ $transfer['origin_facility'] }}</p></div>
                        <div><p class="text-indigo-600">To</p><p>{{ $transfer['destination_facility'] }}</p></div>
                    </div>
                </div>

                <div class="flex gap-3 justify-end mt-5">
                    <x-button.secondary href="{{ route('transfers.show', $transfer['id']) }}">Cancel</x-button.secondary>
                    <button @click="showReview ? submitRecord() : previewDispatch()" type="button"
                        :class="showReview ? 'bg-brand hover:bg-brand-hover' : 'bg-brand hover:bg-brand-hover'"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors">
                        <span x-text="showReview ? 'Confirm Dispatch Preview' : 'Review Dispatch'"></span>
                    </button>
                </div>
            </x-card>

            <div x-show="submitted" style="display:none">
                
            </div>
        </div>
    </div>
</div>

<script>
function dispatchForm() {
    const today = new Date().toISOString().split('T')[0];
    return {
        todayStr: today,
        showReview: false,
        submitted: false,
        errors: {},
        form: {
            dispatch_date: today,
            dispatch_time: '09:00',
            handover_ref: '',
            escort_ref: '',
            departure_notes: '',
        },
        init() { Object.assign(this.form, @json(old()));},
        ve(field) {
            if (!this.form[field]) {
                this.errors[field] = 'This field is required.';
            } else {
                delete this.errors[field];
            }
            this.errors = { ...this.errors };
        },
        previewDispatch() {
            this.ve('dispatch_date');
            this.ve('handover_ref');
            if (Object.keys(this.errors).length) return;
            this.showReview = true;
        },
        submitRecord() { this.submitted = window.pmsSubmit({{ Js::from(route('transfers.dispatch.store', $transfer['id'])) }}, {...this.form, notes: this.form.departure_notes}); },
    };
}
</script>

@endsection
