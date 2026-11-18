@extends('layouts.app')

@section('title', 'Confirm Arrival — ' . $transfer['transfer_ref'])

@section('content')

<x-page-heading
    title="Confirm Arrival"
    :description="'Receiving confirmation at ' . ($transfer['receiving_facility'] ?? $transfer['destination_facility'])"
    :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Transfers', 'route' => 'transfers.index'],
        ['label' => $transfer['transfer_ref'], 'url' => route('transfers.show', $transfer['id'])],
        ['label' => 'Confirm Arrival'],
    ]"
>
    <x-button.secondary href="{{ route('transfers.show', $transfer['id']) }}">← Back</x-button.secondary>
</x-page-heading>



<div x-data="receiveForm()" x-init="init()">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Transfer summary sidebar --}}
        <div class="space-y-4">
            <x-card>
                <x-section-heading title="Transfer"/>
                <dl>
                    <x-detail-row label="Reference" mono>{{ $transfer['transfer_ref'] }}</x-detail-row>
                    <x-detail-row label="Status"><x-transfer-status-badge :status="$transfer['status']"/></x-detail-row>
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
                @if($transfer['dispatch_date'])
                <dl class="mt-2">
                    <x-detail-row label="Dispatched">{{ \Carbon\Carbon::parse($transfer['dispatch_date'])->format('d M Y, H:i') }}</x-detail-row>
                    <x-detail-row label="Handover Ref" mono>{{ $transfer['handover_ref'] ?? '—' }}</x-detail-row>
                </dl>
                @endif
            </x-card>
        </div>

        {{-- Receiving form --}}
        <div class="lg:col-span-2 space-y-5">
            <x-card x-show="!submitted">
                <x-section-heading title="Arrival Confirmation"/>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="arrival_date" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                            Arrival Date <span class="text-[#DC2626]" aria-hidden="true">*</span>
                        </label>
                        <input id="arrival_date" x-model="form.arrival_date" @change="ve('arrival_date')" type="date"
                            :max="todayStr"
                            :class="errors.arrival_date ? 'border-[#DC2626]' : 'border-[#E2E8F0]'"
                            class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent" required>
                        <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.arrival_date" x-text="errors.arrival_date" role="alert"></p>
                    </div>
                    <div>
                        <label for="arrival_time" class="block text-sm font-medium text-[#0F172A] mb-1.5">Arrival Time</label>
                        <input id="arrival_time" x-model="form.arrival_time" type="time"
                            class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent">
                    </div>
                    <div>
                        <label for="arrival_ref" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                            Arrival Reference <span class="text-[#DC2626]" aria-hidden="true">*</span>
                        </label>
                        <input id="arrival_ref" x-model="form.arrival_ref" @blur="ve('arrival_ref')" type="text"
                            :class="errors.arrival_ref ? 'border-[#DC2626]' : 'border-[#E2E8F0]'"
                            class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent font-mono"
                            placeholder="ARR-2025-0001" required>
                        <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.arrival_ref" x-text="errors.arrival_ref" role="alert"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#0F172A] mb-1.5">Receiving Facility</label>
                        <input type="text" value="{{ $transfer['receiving_facility'] ?? $transfer['destination_facility'] }}" disabled
                            class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg bg-[#F8FAFC] text-[#64748B] cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#0F172A] mb-1.5">Receiving Officer</label>
                        <div class="flex items-center gap-2 p-2.5 bg-[#F8FAFC] border border-[#E2E8F0] rounded-lg">
                            <div class="w-7 h-7 rounded-full bg-brand flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-bold text-white">AN</span>
                            </div>
                            <p class="text-sm text-[#0F172A]">Agnes Nakamya <em class="not-italic text-xs text-[#94A3B8]">(demo)</em></p>
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="receipt_notes" class="block text-sm font-medium text-[#0F172A] mb-1.5">Arrival Condition Notes</label>
                        <textarea id="receipt_notes" x-model="form.receipt_notes" rows="3"
                            class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent resize-y"
                            placeholder="Notes on inmate condition and documentation at arrival…"></textarea>
                    </div>
                </div>

                {{-- Confirmation --}}
                <div class="mt-5 flex items-start gap-3 bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <input id="confirm_arrival" x-model="form.confirmed" type="checkbox"
                        class="mt-0.5 w-4 h-4 rounded border-[#E2E8F0] text-purple-600 focus:ring-purple-600 cursor-pointer flex-shrink-0">
                    <label for="confirm_arrival" class="text-sm text-purple-800 cursor-pointer">
                        I confirm that the inmate has been received at this facility and all documentation has been verified.
                        <strong>This action will be authorized and recorded by the server.</strong>
                    </label>
                </div>

                <div class="flex gap-3 justify-end mt-5">
                    <x-button.secondary href="{{ route('transfers.show', $transfer['id']) }}">Cancel</x-button.secondary>
                    <button @click="submitRecord()" :disabled="!form.confirmed" type="button"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        Confirm Arrival
                    </button>
                </div>
            </x-card>

            <div x-show="submitted" style="display:none">
                
            </div>
        </div>
    </div>
</div>

<script>
function receiveForm() {
    const today = new Date().toISOString().split('T')[0];
    return {
        todayStr: today,
        submitted: false,
        errors: {},
        form: {
            arrival_date: today,
            arrival_time: '14:00',
            arrival_ref: '',
            receipt_notes: '',
            confirmed: false,
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
        submitRecord() {
            this.ve('arrival_date');
            this.ve('arrival_ref');
            if (Object.keys(this.errors).length) return;
            this.submitted = window.pmsSubmit({{ Js::from(route('transfers.receive.store', $transfer['id'])) }}, {...this.form, notes: this.form.receipt_notes});
        },
    };
}
</script>

@endsection
