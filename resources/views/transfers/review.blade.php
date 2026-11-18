@extends('layouts.app')

@section('title', 'Review Transfer — ' . $transfer['transfer_ref'])

@section('content')

<x-page-heading
    title="Review Transfer Request"
    :description="'Authorization review for ' . $transfer['transfer_ref']"
    :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Transfers', 'route' => 'transfers.index'],
        ['label' => $transfer['transfer_ref'], 'url' => route('transfers.show', $transfer['id'])],
        ['label' => 'Review'],
    ]"
>
    <x-button.secondary href="{{ route('transfers.show', $transfer['id']) }}">← Back to Transfer</x-button.secondary>
</x-page-heading>



<div x-data="reviewForm()" x-init="init()">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Left — Transfer Summary --}}
        <div class="space-y-4">
            <x-card>
                <x-section-heading title="Transfer Request"/>
                <dl>
                    <x-detail-row label="Reference" mono>{{ $transfer['transfer_ref'] }}</x-detail-row>
                    <x-detail-row label="Status"><x-transfer-status-badge :status="$transfer['status']"/></x-detail-row>
                    <x-detail-row label="Request Date">{{ \Carbon\Carbon::parse($transfer['request_date'])->format('d M Y') }}</x-detail-row>
                </dl>
            </x-card>
            <x-card>
                <x-section-heading title="Inmate"/>
                <dl>
                    <x-detail-row label="Prisoner No." mono>{{ $transfer['prisoner_number'] }}</x-detail-row>
                    <x-detail-row label="Name">{{ $transfer['inmate_name'] }}</x-detail-row>
                    <x-detail-row label="Classification"><x-custody-badge :classification="$transfer['classification']"/></x-detail-row>
                </dl>
            </x-card>
            <x-card>
                <x-section-heading title="Movement"/>
                <div class="py-2">
                    <x-facility-movement :from="$transfer['origin_facility']" :to="$transfer['destination_facility']"/>
                </div>
                <dl class="mt-2">
                    <x-detail-row label="Reason">{{ $transfer['transfer_reason'] }}</x-detail-row>
                    @if($transfer['proposed_transfer_date'])
                        <x-detail-row label="Proposed Date">{{ \Carbon\Carbon::parse($transfer['proposed_transfer_date'])->format('d M Y') }}</x-detail-row>
                    @endif
                    <x-detail-row label="Notes">{{ $transfer['notes'] ?? '—' }}</x-detail-row>
                    @if($transfer['clarification_request'])
                        <x-detail-row label="Clarification Requested"><span class="text-amber-700">{{ $transfer['clarification_request'] }}</span></x-detail-row>
                    @endif
                </dl>
            </x-card>
        </div>

        {{-- Right — Review Form --}}
        <div class="lg:col-span-2 space-y-5">
            <x-card>
                <x-section-heading title="Authorization Decision" description="Select a decision and provide the required information. Actions require server authorization."/>

                {{-- Decision radio --}}
                <fieldset class="mb-5">
                    <legend class="text-sm font-medium text-[#0F172A] mb-3">Decision <span class="text-[#DC2626]" aria-hidden="true">*</span></legend>
                    <div class="space-y-2">
                        @permission('transfers.authorize', $transfer['origin_facility_id'])
<label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors"
                            :class="decision === 'Approve' ? 'border-green-400 bg-green-50' : 'border-[#E2E8F0] hover:bg-[#F8FAFC]'">
                            <input type="radio" x-model="decision" value="Approve" class="text-[#16A34A] focus:ring-[#16A34A]">
                            <div>
                                <p class="text-sm font-semibold text-[#0F172A]">Approve</p>
                                <p class="text-xs text-[#64748B]">Authorize the transfer to proceed to dispatch.</p>
                            </div>
                        </label>
@endpermission
                        <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors"
                            :class="decision === 'Reject' ? 'border-red-400 bg-red-50' : 'border-[#E2E8F0] hover:bg-[#F8FAFC]'">
                            <input type="radio" x-model="decision" value="Reject" class="text-[#DC2626] focus:ring-[#DC2626]">
                            <div>
                                <p class="text-sm font-semibold text-[#0F172A]">Reject</p>
                                <p class="text-xs text-[#64748B]">Decline the transfer request. A reason must be provided.</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors"
                            :class="decision === 'Clarify' ? 'border-amber-400 bg-amber-50' : 'border-[#E2E8F0] hover:bg-[#F8FAFC]'">
                            <input type="radio" x-model="decision" value="Clarify" class="text-[#D97706] focus:ring-[#D97706]">
                            <div>
                                <p class="text-sm font-semibold text-[#0F172A]">Request Clarification</p>
                                <p class="text-xs text-[#64748B]">Ask the requesting facility for additional information.</p>
                            </div>
                        </label>
                    </div>
                    <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.decision" x-text="errors.decision" role="alert"></p>
                </fieldset>

                {{-- Auth Ref (Approve) --}}
                <div x-show="decision === 'Approve'" class="mb-4">
                    <label for="auth_ref" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                        Authorization Reference <span class="text-[#DC2626]" aria-hidden="true">*</span>
                    </label>
                    <input id="auth_ref" x-model="form.auth_ref" @blur="ve('auth_ref')" type="text"
                        :class="errors.auth_ref ? 'border-[#DC2626]' : 'border-[#E2E8F0]'"
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent font-mono"
                        placeholder="AUTH-2025-0001">
                    <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.auth_ref" x-text="errors.auth_ref" role="alert"></p>
                </div>

                {{-- Rejection reason --}}
                <div x-show="decision === 'Reject'" class="mb-4">
                    <label for="rejection_reason" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                        Rejection Reason <span class="text-[#DC2626]" aria-hidden="true">*</span>
                    </label>
                    <textarea id="rejection_reason" x-model="form.rejection_reason" @blur="ve('rejection_reason')" rows="3"
                        :class="errors.rejection_reason ? 'border-[#DC2626]' : 'border-[#E2E8F0]'"
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent resize-y"
                        placeholder="Provide the reason for rejecting this transfer request…"></textarea>
                    <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.rejection_reason" x-text="errors.rejection_reason" role="alert"></p>
                </div>

                {{-- Clarification request --}}
                <div x-show="decision === 'Clarify'" class="mb-4">
                    <label for="clarification" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                        Clarification Request <span class="text-[#DC2626]" aria-hidden="true">*</span>
                    </label>
                    <textarea id="clarification" x-model="form.clarification_request" @blur="ve('clarification_request')" rows="3"
                        :class="errors.clarification_request ? 'border-[#DC2626]' : 'border-[#E2E8F0]'"
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent resize-y"
                        placeholder="Specify what additional information is required…"></textarea>
                    <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.clarification_request" x-text="errors.clarification_request" role="alert"></p>
                </div>

                <div class="mb-5">
                    <label for="review_notes" class="block text-sm font-medium text-[#0F172A] mb-1.5">Review Notes <span class="text-xs text-[#94A3B8]">(optional)</span></label>
                    <textarea id="review_notes" x-model="form.review_notes" rows="2"
                        class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent resize-y"
                        placeholder="Additional review notes…"></textarea>
                </div>

                {{-- Review summary --}}
                <div x-show="decision && showSummary" class="bg-[#F8FAFC] border border-[#E2E8F0] rounded-lg p-4 mb-5">
                    <p class="text-sm font-semibold text-[#0F172A] mb-2">Review Summary</p>
                    <div class="grid grid-cols-2 gap-y-2 text-xs">
                        <div><span class="text-[#64748B]">Decision:</span> <span class="font-semibold text-[#0F172A]" x-text="decision"></span></div>
                        <div x-show="form.auth_ref"><span class="text-[#64748B]">Auth Ref:</span> <span class="font-mono font-semibold" x-text="form.auth_ref"></span></div>
                        <div class="col-span-2" x-show="form.rejection_reason"><span class="text-[#64748B]">Reason:</span> <span x-text="form.rejection_reason"></span></div>
                        <div class="col-span-2" x-show="form.clarification_request"><span class="text-[#64748B]">Clarification:</span> <span x-text="form.clarification_request"></span></div>
                    </div>
                </div>

                <div class="flex gap-3 justify-end">
                    <x-button.secondary href="{{ route('transfers.show', $transfer['id']) }}">Cancel</x-button.secondary>
                    <button @click="submitReview()" type="button"
                        :class="decision === 'Reject' ? 'bg-[#DC2626] hover:bg-red-700' : decision === 'Approve' ? 'bg-[#16A34A] hover:bg-green-700' : 'bg-amber-500 hover:bg-amber-600'"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors">
                        <span x-text="decision === 'Approve' ? 'Confirm Approval' : decision === 'Reject' ? 'Confirm Rejection' : decision === 'Clarify' ? 'Request Clarification' : 'Confirm Decision'"></span>
                    </button>
                </div>
            </x-card>

            <div x-show="submitted" style="display:none">
                
            </div>
        </div>
    </div>
</div>

<script>
function reviewForm() {
    return {
        decision: '',
        showSummary: false,
        submitted: false,
        errors: {},
        form: {
            auth_ref: '',
            rejection_reason: '',
            clarification_request: '',
            review_notes: '',
        },
        init() { Object.assign(this.form, @json(old()));
            this.$watch('decision', () => { this.showSummary = false; });
        },
        ve(field) {
            if (!this.form[field]) {
                this.errors[field] = 'This field is required.';
            } else {
                delete this.errors[field];
            }
            this.errors = { ...this.errors };
        },
        submitReview() {
            this.errors = {};
            if (!this.decision) { this.errors.decision = 'Please select a decision.'; this.errors = { ...this.errors }; return; }
            if (this.decision === 'Approve' && !this.form.auth_ref) { this.ve('auth_ref'); return; }
            if (this.decision === 'Reject'  && !this.form.rejection_reason) { this.ve('rejection_reason'); return; }
            if (this.decision === 'Clarify' && !this.form.clarification_request) { this.ve('clarification_request'); return; }
            this.showSummary = true;
            this.submitted = window.pmsSubmit({{ Js::from(route('transfers.review.store', $transfer['id'])) }}, {...this.form, decision: this.decision, notes: this.decision === 'Reject' ? this.form.rejection_reason : this.decision === 'Clarify' ? this.form.clarification_request : this.form.review_notes});
        },
    };
}
</script>

@endsection
