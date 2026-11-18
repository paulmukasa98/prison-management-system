@extends('layouts.app')

@section('title', 'New Transfer Request')

@section('content')

<x-page-heading
    title="New Transfer Request"
    description="Create a new inmate transfer request — authorized workflow."
    :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Transfers', 'route' => 'transfers.index'],
        ['label' => 'New Request'],
    ]"
/>



<script>
    window.__ELIGIBLE_INMATES__ = @json($inmates);
    window.__TRF_FACILITIES__   = @json($facilities);
</script>

<div x-data="transferForm()" @beforeunload.window="warnUnsaved($event)">

{{-- Step indicator --}}
<div class="mb-6">
    <x-step-indicator
        :steps="['Select Inmate', 'Transfer Details', 'Request Info', 'Review', 'Preview']"
        :current="1"
        reactive-current="step"
    />
</div>

{{-- =====================================================================
     STEP 1 — Select Inmate
     ===================================================================== --}}
<x-card x-show="step === 1">
    <x-section-heading title="Step 1 — Select Inmate" description="Search and select the inmate to be transferred. Only inmates currently in custody are eligible."/>

    <div class="relative mb-4">
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3" aria-hidden="true">
            <svg class="h-4 w-4 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <input type="search" x-model="inmateSearch" @input="filterInmates()"
            placeholder="Search by prisoner number or name…"
            class="w-full pl-9 pr-4 py-2.5 text-sm text-[#0F172A] bg-white border border-[#E2E8F0] rounded-lg
                   placeholder-[#94A3B8] focus:outline-none focus:ring-2 focus:ring-brand"
            aria-label="Search eligible inmates">
    </div>

    {{-- Results list --}}
    <div class="border border-[#E2E8F0] rounded-lg overflow-hidden">
        <template x-if="filteredInmates.length === 0">
            <div class="p-8 text-center text-sm text-[#64748B]">No eligible inmates match your search.</div>
        </template>
        <template x-for="inmate in filteredInmates" :key="inmate.id">
            <button type="button"
                @click="selectInmate(inmate)"
                :class="form.inmate_id === inmate.id ? 'bg-[#DBEAFE] border-l-4 border-brand' : 'hover:bg-[#F8FAFC] border-l-4 border-transparent'"
                class="w-full flex items-center gap-3 p-3 text-left border-b border-[#F1F5F9] last:border-0 transition-colors"
                :aria-pressed="form.inmate_id === inmate.id"
                :aria-label="`Select ${inmate.full_name}`">
                <div class="w-9 h-9 rounded-full bg-[#DBEAFE] flex items-center justify-center flex-shrink-0" aria-hidden="true">
                    <span class="text-xs font-bold text-[#2563EB]" x-text="inmate.initials"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-[#0F172A]" x-text="inmate.full_name"></p>
                    <p class="text-xs text-[#64748B]">
                        <span class="font-mono" x-text="inmate.prisoner_number"></span>
                        &middot; <span x-text="inmate.classification"></span>
                        &middot; <span x-text="inmate.facility"></span>
                    </p>
                </div>
                <div>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium"
                        :class="inmate.status === 'In Custody' ? 'badge-active' : 'badge-pending'"
                        x-text="inmate.status"></span>
                </div>
                <div x-show="form.inmate_id === inmate.id" class="text-[#2563EB]" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </button>
        </template>
    </div>

    <p x-show="errors.inmate_id" class="mt-2 text-xs text-[#DC2626]" role="alert" x-text="errors.inmate_id"></p>
    <p class="mt-3 text-xs text-[#64748B]">
        Showing <span x-text="filteredInmates.length"></span> eligible inmates.
        Only inmates with status <em>In Custody</em> or <em>Remanded</em> can be transferred.
    </p>
</x-card>

{{-- =====================================================================
     STEP 2 — Transfer Details
     ===================================================================== --}}
<x-card x-show="step === 2">
    <x-section-heading title="Step 2 — Transfer Details"/>

    {{-- Selected inmate summary --}}
    <div class="flex items-center gap-3 p-3 bg-[#F8FAFC] border border-[#E2E8F0] rounded-lg mb-5">
        <div class="w-9 h-9 rounded-full bg-[#DBEAFE] flex items-center justify-center flex-shrink-0" aria-hidden="true">
            <span class="text-xs font-bold text-[#2563EB]" x-text="selectedInmate ? selectedInmate.initials : ''"></span>
        </div>
        <div>
            <p class="text-sm font-semibold text-[#0F172A]" x-text="selectedInmate ? selectedInmate.full_name : ''"></p>
            <p class="text-xs text-[#64748B]" x-text="selectedInmate ? (selectedInmate.prisoner_number + ' · ' + selectedInmate.classification) : ''"></p>
        </div>
        <button @click="step = 1" class="ml-auto text-xs text-[#2563EB] hover:underline" type="button">Change</button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-[#0F172A] mb-1.5">
                Originating Facility
                <span class="text-xs text-[#94A3B8]">(inmate's current facility)</span>
            </label>
            <input type="text" :value="form.origin_facility" disabled
                class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg bg-[#F8FAFC] text-[#64748B] cursor-not-allowed">
            <p class="mt-1 text-xs text-[#64748B]">Automatically set from the selected inmate's current facility.</p>
        </div>
        <div>
            <label for="destination" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                Destination Facility <span class="text-[#DC2626]" aria-hidden="true">*</span>
            </label>
            <select id="destination" x-model="form.destination_facility" @change="validateField('destination_facility')"
                :class="errors.destination_facility ? 'border-[#DC2626]' : 'border-[#E2E8F0]'"
                class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent" required>
                <option value="">Select destination</option>
                @foreach($facilities as $f)
                    <option>{{ $f }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.destination_facility" x-text="errors.destination_facility" role="alert"></p>
        </div>
        <div>
            <label for="reason" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                Transfer Reason <span class="text-[#DC2626]" aria-hidden="true">*</span>
            </label>
            <select id="reason" x-model="form.transfer_reason" @change="validateField('transfer_reason')"
                :class="errors.transfer_reason ? 'border-[#DC2626]' : 'border-[#E2E8F0]'"
                class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent" required>
                <option value="">Select reason</option>
                @foreach($reasons as $r)
                    <option>{{ $r }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-[#64748B]">These are configurable design categories — not verified official UPS terminology.</p>
            <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.transfer_reason" x-text="errors.transfer_reason" role="alert"></p>
        </div>
        <div>
            <label for="priority" class="block text-sm font-medium text-[#0F172A] mb-1.5">Priority</label>
            <select id="priority" x-model="form.priority"
                class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent">
                @foreach($priorities as $p)
                    <option>{{ $p }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="proposed_date" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                Proposed Transfer Date <span class="text-[#DC2626]" aria-hidden="true">*</span>
            </label>
            <input id="proposed_date" x-model="form.proposed_transfer_date" @change="validateField('proposed_transfer_date')" type="date"
                :class="errors.proposed_transfer_date ? 'border-[#DC2626]' : 'border-[#E2E8F0]'"
                class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent" required>
            <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.proposed_transfer_date" x-text="errors.proposed_transfer_date" role="alert"></p>
        </div>
        <div class="sm:col-span-2">
            <label for="notes" class="block text-sm font-medium text-[#0F172A] mb-1.5">Additional Notes</label>
            <textarea id="notes" x-model="form.notes" rows="3"
                class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent resize-y"
                placeholder="Any additional context supporting this request…"></textarea>
        </div>
    </div>
</x-card>

{{-- =====================================================================
     STEP 3 — Request Information
     ===================================================================== --}}
<x-card x-show="step === 3">
    <x-section-heading title="Step 3 — Request Information"/>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-[#0F172A] mb-1.5">Requesting Facility</label>
            <input type="text" :value="form.origin_facility" disabled
                class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg bg-[#F8FAFC] text-[#64748B] cursor-not-allowed">
        </div>
        <div>
            <label class="block text-sm font-medium text-[#0F172A] mb-1.5">Request Date</label>
            <input type="text" :value="todayStr" disabled
                class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg bg-[#F8FAFC] text-[#64748B] cursor-not-allowed">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-[#0F172A] mb-1.5">Requesting Officer</label>
            <div class="flex items-center gap-3 p-3 bg-[#F8FAFC] border border-[#E2E8F0] rounded-lg">
                <div class="w-8 h-8 rounded-full bg-brand flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-bold text-white">AN</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-[#0F172A]">Agnes Nakamya</p>
                    <p class="text-xs text-[#64748B]">System Administrator — Uganda Prisons HQ <em>(demo context — not an authenticated identity)</em></p>
                </div>
            </div>
        </div>
        <div>
            <label for="doc_ref" class="block text-sm font-medium text-[#0F172A] mb-1.5">Supporting Document Reference <span class="text-xs text-[#94A3B8]">(optional)</span></label>
            <input id="doc_ref" x-model="form.supporting_doc_ref" type="text"
                class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent font-mono"
                placeholder="DOC-FAC-2025-0001">
        </div>
        <div>
            <label for="req_notes" class="block text-sm font-medium text-[#0F172A] mb-1.5">Request Notes <span class="text-xs text-[#94A3B8]">(optional)</span></label>
            <textarea id="req_notes" x-model="form.request_notes" rows="2"
                class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent resize-y"
                placeholder="Notes for the reviewing authority…"></textarea>
        </div>
    </div>
</x-card>

{{-- =====================================================================
     STEP 4 — Review
     ===================================================================== --}}
<x-card x-show="step === 4">
    <x-section-heading title="Step 4 — Review Transfer Request" description="Verify all details before submitting for authorization."/>

    <div class="space-y-5">
        <div>
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Inmate</h3>
                <button @click="step = 1" class="text-xs text-[#2563EB] hover:underline" type="button">Change</button>
            </div>
            <div class="bg-[#F8FAFC] rounded-lg border border-[#E2E8F0] p-4 grid grid-cols-2 sm:grid-cols-3 gap-y-3">
                <div><p class="text-xs text-[#64748B]">Prisoner Number</p><p class="text-sm font-mono font-semibold text-[#2563EB]" x-text="selectedInmate ? selectedInmate.prisoner_number : '—'"></p></div>
                <div><p class="text-xs text-[#64748B]">Full Name</p><p class="text-sm font-medium" x-text="selectedInmate ? selectedInmate.full_name : '—'"></p></div>
                <div><p class="text-xs text-[#64748B]">Classification</p><p class="text-sm" x-text="selectedInmate ? selectedInmate.classification : '—'"></p></div>
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Transfer Details</h3>
                <button @click="step = 2" class="text-xs text-[#2563EB] hover:underline" type="button">Edit</button>
            </div>
            <div class="bg-[#F8FAFC] rounded-lg border border-[#E2E8F0] p-4 grid grid-cols-2 sm:grid-cols-3 gap-y-3">
                <div><p class="text-xs text-[#64748B]">From</p><p class="text-sm font-medium" x-text="form.origin_facility || '—'"></p></div>
                <div><p class="text-xs text-[#64748B]">To</p><p class="text-sm font-medium" x-text="form.destination_facility || '—'"></p></div>
                <div><p class="text-xs text-[#64748B]">Reason</p><p class="text-sm" x-text="form.transfer_reason || '—'"></p></div>
                <div><p class="text-xs text-[#64748B]">Proposed Date</p><p class="text-sm" x-text="form.proposed_transfer_date || '—'"></p></div>
                <div><p class="text-xs text-[#64748B]">Priority</p><p class="text-sm" x-text="form.priority || '—'"></p></div>
            </div>
        </div>

        <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-lg p-4">
            <input id="confirm_review" x-model="form.confirmed" type="checkbox"
                class="mt-0.5 w-4 h-4 rounded border-[#E2E8F0] text-[#2563EB] focus:ring-brand cursor-pointer flex-shrink-0">
            <label for="confirm_review" class="text-sm text-amber-800 cursor-pointer">
                I confirm that I have reviewed all the information displayed above and it is accurate.
                <strong>This action will be authorized and recorded by the server.</strong>
            </label>
        </div>
    </div>
</x-card>

{{-- =====================================================================
     STEP 5 — Submission Preview
     ===================================================================== --}}
<x-card x-show="step === 5">
    <x-section-heading title="Step 5 — Transfer Request Preview" description="Summary of the proposed transfer request."/>

    <div class="bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl p-6 mb-5">
        <div class="flex items-center gap-2 mb-4">
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending Approval
            </span>
            <span class="text-xs text-[#94A3B8]">Preview — not yet submitted</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-4 gap-x-6">
            <div><p class="text-xs text-[#64748B]">Inmate</p><p class="text-sm font-semibold" x-text="selectedInmate ? selectedInmate.full_name : '—'"></p></div>
            <div><p class="text-xs text-[#64748B]">Prisoner No.</p><p class="text-sm font-mono font-semibold text-[#2563EB]" x-text="selectedInmate ? selectedInmate.prisoner_number : '—'"></p></div>
            <div><p class="text-xs text-[#64748B]">Classification</p><p class="text-sm" x-text="selectedInmate ? selectedInmate.classification : '—'"></p></div>
            <div><p class="text-xs text-[#64748B]">From</p><p class="text-sm font-medium" x-text="form.origin_facility || '—'"></p></div>
            <div><p class="text-xs text-[#64748B]">To</p><p class="text-sm font-medium" x-text="form.destination_facility || '—'"></p></div>
            <div><p class="text-xs text-[#64748B]">Reason</p><p class="text-sm" x-text="form.transfer_reason || '—'"></p></div>
            <div><p class="text-xs text-[#64748B]">Proposed Date</p><p class="text-sm" x-text="form.proposed_transfer_date || '—'"></p></div>
            <div><p class="text-xs text-[#64748B]">Priority</p><p class="text-sm" x-text="form.priority || '—'"></p></div>
            <div><p class="text-xs text-[#64748B]">Requesting Officer</p><p class="text-sm text-[#64748B] italic">Agnes Nakamya (demo)</p></div>
        </div>
    </div>

    <div x-show="submitted" class="mb-5">
        
    </div>
</x-card>

{{-- Navigation --}}
<div class="flex items-center justify-between mt-5">
    <div>
        @permissionAny(['transfers.request', 'transfers.review', 'transfers.authorize', 'transfers.dispatch', 'transfers.receive'])
<x-button.secondary href="{{ route('transfers.index') }}" x-show="step === 1">Cancel</x-button.secondary>
@endpermissionAny
        <x-button.secondary @click="prevStep()" x-show="step > 1" type="button">← Previous</x-button.secondary>
    </div>
    <div class="flex items-center gap-3">
        <span class="text-xs text-[#94A3B8]" x-text="`Step ${step} of 5`"></span>
        <button @click="nextStep()" x-show="step < 4" type="button"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-brand rounded-lg hover:bg-brand-hover transition-colors">
            Next →
        </button>
        <button @click="nextStep()" x-show="step === 4" type="button"
            :disabled="!form.confirmed"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-brand rounded-lg hover:bg-brand-hover transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
            Continue →
        </button>
        <button x-show="step === 5" @click="submitRecord()" :disabled="submitted" type="button"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-[#16A34A] rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
            Submit Transfer Request
        </button>
    </div>
</div>

</div>{{-- end x-data --}}

<script>
function transferForm() {
    const now = new Date();
    const today = `${now.getFullYear()}-${String(now.getMonth()+1).padStart(2,'0')}-${String(now.getDate()).padStart(2,'0')}`;
    const params = new URLSearchParams(window.location.search);
    const preselect = parseInt(params.get('inmate_id')) || 0;

    return {
        step: 1,
        submitted: false,
        isDirty: false,
        todayStr: today,
        inmateSearch: '',
        allInmates: [],
        filteredInmates: [],
        selectedInmate: null,
        errors: {},

        form: {
            inmate_id: null,
            origin_facility: '',
            destination_facility: '',
            transfer_reason: '',
            priority: 'Routine',
            proposed_transfer_date: '',
            notes: '',
            supporting_doc_ref: '',
            request_notes: '',
            confirmed: false,
        },

        init() { Object.assign(this.form, @json(old()));
            this.allInmates     = window.__ELIGIBLE_INMATES__ || [];
            this.filteredInmates = this.allInmates;
            if (preselect) {
                const found = this.allInmates.find(i => i.id === preselect);
                if (found) { this.selectInmate(found); this.step = 2; }
            }
            this.$watch('form', () => { this.isDirty = true; }, { deep: true });
        },

        warnUnsaved(e) {
            if (this.isDirty && !this.submitted) { e.preventDefault(); e.returnValue = ''; }
        },

        filterInmates() {
            const q = this.inmateSearch.toLowerCase().trim();
            this.filteredInmates = q
                ? this.allInmates.filter(i => `${i.prisoner_number} ${i.full_name}`.toLowerCase().includes(q))
                : this.allInmates;
        },

        selectInmate(inmate) {
            this.selectedInmate          = inmate;
            this.form.inmate_id          = inmate.id;
            this.form.origin_facility    = inmate.facility;
            delete this.errors.inmate_id;
        },

        validateField(field) {
            if (field === 'destination_facility') {
                if (!this.form.destination_facility) {
                    this.errors.destination_facility = 'Please select a destination facility.';
                } else if (this.form.destination_facility === this.form.origin_facility) {
                    this.errors.destination_facility = 'Destination must differ from the origin facility.';
                } else {
                    delete this.errors.destination_facility;
                }
            } else if (!this.form[field]) {
                this.errors[field] = 'This field is required.';
            } else {
                delete this.errors[field];
            }
            this.errors = { ...this.errors };
        },

        validateStep(s) {
            if (s === 1) {
                if (!this.form.inmate_id) { this.errors.inmate_id = 'Please select an inmate.'; this.errors = { ...this.errors }; return false; }
            }
            if (s === 2) {
                this.validateField('destination_facility');
                this.validateField('transfer_reason');
                this.validateField('proposed_transfer_date');
                if (Object.keys(this.errors).length) return false;
            }
            return true;
        },

        nextStep() {
            if (!this.validateStep(this.step)) return;
            if (this.step < 5) this.step++;
        },
        prevStep() { if (this.step > 1) this.step--; },
        submitRecord() {
            for (let s = 1; s <= 4; s++) {
                if (!this.validateStep(s)) { this.step = s; return; }
            }
            if (!this.form.confirmed) { return; }
            this.submitted = window.pmsSubmit({{ Js::from(route('transfers.store')) }}, this.form); this.isDirty = false;
        },
    };
}
</script>

@endsection
