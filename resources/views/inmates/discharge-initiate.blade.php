@extends('layouts.app')

@section('title', 'Initiate Discharge — ' . $inmate['prisoner_number'])

@section('content')

<x-page-heading
    title="Initiate Discharge"
    :description="'Discharge initiation for ' . $inmate['full_name'] . ' (' . $inmate['prisoner_number'] . ')'"
    :breadcrumbs="[
        ['label' => 'Dashboard',                'route' => 'dashboard'],
        ['label' => 'Inmates',                  'route' => 'inmates.index'],
        ['label' => $inmate['prisoner_number'], 'url'   => route('inmates.show', $inmate['id'])],
        ['label' => 'Initiate Discharge'],
    ]"
>
    <x-button.secondary href="{{ route('inmates.show', $inmate['id']) }}">← Back to Profile</x-button.secondary>
</x-page-heading>



<div x-data="dischargeForm()" x-init="init()">

{{-- Step indicator --}}
<div class="mb-6">
    <x-step-indicator
        :steps="['Confirm Inmate', 'Custody Review', 'Discharge Basis', 'Authorization', 'Confirm & Preview']"
        :current="1"
        reactive-current="step"
    />
</div>

{{-- ===================================================================
     STEP 1 — Confirm inmate
     =================================================================== --}}
<x-card x-show="step === 1">
    <x-section-heading title="Step 1 — Confirm Inmate" description="Verify the inmate identity before proceeding."/>

    <div class="flex flex-col sm:flex-row items-start gap-5 mb-5">
        <div class="w-16 h-20 bg-[#F1F5F9] rounded-lg border border-[#E2E8F0] flex items-center justify-center flex-shrink-0">
            <span class="text-xl font-bold text-[#64748B]">{{ $inmate['initials'] }}</span>
        </div>
        <div class="flex-1">
            <div class="flex flex-wrap items-center gap-2 mb-2">
                <span class="font-mono text-sm font-semibold text-[#2563EB]">{{ $inmate['prisoner_number'] }}</span>
                <x-inmate-status-badge :status="$inmate['status']"/>
                <x-custody-badge :classification="$inmate['classification']"/>
            </div>
            <h2 class="text-lg font-bold text-[#0F172A]">{{ $inmate['full_name'] }}</h2>
            <p class="text-sm text-[#64748B]">{{ $inmate['gender'] }} &middot; Age {{ $inmate['age'] }} &middot; {{ $inmate['district'] }}</p>
            <p class="text-sm text-[#64748B] mt-1">Currently at: <strong>{{ $inmate['facility'] }}</strong></p>
        </div>
    </div>

    <div class="flex items-start gap-3 bg-red-50 border border-red-200 rounded-lg px-4 py-3">
        <input id="confirm_identity" x-model="confirmed_identity" type="checkbox"
            class="mt-0.5 w-4 h-4 rounded border-[#E2E8F0] text-[#DC2626] focus:ring-[#DC2626] cursor-pointer flex-shrink-0">
        <label for="confirm_identity" class="text-sm text-red-700 cursor-pointer">
            I confirm that this is the correct inmate and I am authorised to initiate discharge proceedings for this record.
        </label>
    </div>
</x-card>

{{-- ===================================================================
     STEP 2 — Custody Review
     =================================================================== --}}
<x-card x-show="step === 2">
    <x-section-heading title="Step 2 — Current Custody Record" description="Review the current admission details before recording discharge."/>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-10">
        <dl>
            <x-detail-row label="Prisoner Number" mono>{{ $inmate['prisoner_number'] }}</x-detail-row>
            <x-detail-row label="Admission Reference" mono>{{ $inmate['admission_ref'] }}</x-detail-row>
            <x-detail-row label="Classification"><x-custody-badge :classification="$inmate['classification']"/></x-detail-row>
            <x-detail-row label="Legal Basis">{{ $inmate['legal_basis'] }}</x-detail-row>
        </dl>
        <dl>
            <x-detail-row label="Current Facility">{{ $inmate['facility'] }}</x-detail-row>
            <x-detail-row label="Admission Date">{{ \Carbon\Carbon::parse($inmate['admission_date'])->format('d M Y') }}</x-detail-row>
            <x-detail-row label="Warrant / Order Ref" mono>{{ $inmate['warrant_ref'] }}</x-detail-row>
            <x-detail-row label="Issuing Authority">{{ $inmate['issuing_authority'] }}</x-detail-row>
            @if($inmate['expected_release'])
                <x-detail-row label="Expected Release">{{ \Carbon\Carbon::parse($inmate['expected_release'])->format('d M Y') }}</x-detail-row>
            @endif
        </dl>
    </div>
    <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
        <p class="text-xs text-amber-700">Verify that the legal basis for detention has been legally concluded before proceeding. This system does not automatically determine eligibility for release.</p>
    </div>
</x-card>

{{-- ===================================================================
     STEP 3 — Discharge Basis
     =================================================================== --}}
<x-card x-show="step === 3">
    <x-section-heading title="Step 3 — Record Discharge Basis"/>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="discharge_reason" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                Discharge Reason <span class="text-[#DC2626]" aria-hidden="true">*</span>
            </label>
            <select id="discharge_reason" x-model="form.discharge_reason" @change="ve('discharge_reason')"
                :class="errors.discharge_reason ? 'border-[#DC2626]' : 'border-[#E2E8F0]'"
                class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent" required>
                <option value="">Select reason</option>
                <option>Sentence Served</option>
                <option>Bail Granted</option>
                <option>Court Acquittal</option>
                <option>Presidential Pardon</option>
                <option>Appeal Upheld</option>
                <option>Civil Order Lifted</option>
            </select>
            <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.discharge_reason" x-text="errors.discharge_reason" role="alert"></p>
        </div>
        <div>
            <label for="discharge_date" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                Proposed Discharge Date <span class="text-[#DC2626]" aria-hidden="true">*</span>
            </label>
            <input id="discharge_date" x-model="form.discharge_date" @change="ve('discharge_date')" type="date"
                :max="todayStr"
                :class="errors.discharge_date ? 'border-[#DC2626]' : 'border-[#E2E8F0]'"
                class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent" required>
            <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.discharge_date" x-text="errors.discharge_date" role="alert"></p>
        </div>
        <div class="sm:col-span-2">
            <label for="discharge_notes" class="block text-sm font-medium text-[#0F172A] mb-1.5">Administrative Notes</label>
            <textarea id="discharge_notes" x-model="form.discharge_notes" rows="3"
                class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent resize-y"
                placeholder="Any relevant notes regarding this discharge…"></textarea>
        </div>
    </div>
</x-card>

{{-- ===================================================================
     STEP 4 — Authorization
     =================================================================== --}}
<x-card x-show="step === 4">
    <x-section-heading title="Step 4 — Authorization Details"/>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="auth_ref" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                Discharge Authorization Reference <span class="text-[#DC2626]" aria-hidden="true">*</span>
            </label>
            <input id="auth_ref" x-model="form.auth_ref" @blur="ve('auth_ref')" type="text"
                :class="errors.auth_ref ? 'border-[#DC2626]' : 'border-[#E2E8F0]'"
                class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent font-mono"
                placeholder="DCH-2025-0001" required>
            <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.auth_ref" x-text="errors.auth_ref" role="alert"></p>
        </div>
        <div>
            <label for="auth_authority" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                Authorizing Authority <span class="text-[#DC2626]" aria-hidden="true">*</span>
            </label>
            <input id="auth_authority" x-model="form.auth_authority" @blur="ve('auth_authority')" type="text"
                :class="errors.auth_authority ? 'border-[#DC2626]' : 'border-[#E2E8F0]'"
                class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent"
                placeholder="e.g. High Court of Uganda, Kampala" required>
            <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.auth_authority" x-text="errors.auth_authority" role="alert"></p>
        </div>
    </div>
    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-xs text-blue-700">Authorization references must correspond to official court orders, pardon certificates, or bail documents. This system does not issue authorization documents.</p>
    </div>
</x-card>

{{-- ===================================================================
     STEP 5 — Confirm & Preview
     =================================================================== --}}
<x-card x-show="step === 5">
    <x-section-heading title="Step 5 — Review Discharge Information" description="Review all discharge details before submitting for authorization."/>

    <div class="space-y-4">
        <div>
            <h3 class="text-xs font-semibold text-[#64748B] uppercase tracking-wide mb-2">Inmate</h3>
            <div class="bg-[#F8FAFC] rounded-lg border border-[#E2E8F0] p-4 grid grid-cols-2 sm:grid-cols-3 gap-y-3">
                <div><p class="text-xs text-[#64748B]">Prisoner Number</p><p class="text-sm font-mono font-semibold text-[#2563EB]">{{ $inmate['prisoner_number'] }}</p></div>
                <div><p class="text-xs text-[#64748B]">Name</p><p class="text-sm font-medium">{{ $inmate['full_name'] }}</p></div>
                <div><p class="text-xs text-[#64748B]">Facility</p><p class="text-sm">{{ $inmate['facility'] }}</p></div>
            </div>
        </div>
        <div>
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Discharge Details</h3>
                <button @click="step = 3" class="text-xs text-[#2563EB] hover:underline" type="button">Edit</button>
            </div>
            <div class="bg-[#F8FAFC] rounded-lg border border-[#E2E8F0] p-4 grid grid-cols-2 sm:grid-cols-3 gap-y-3">
                <div><p class="text-xs text-[#64748B]">Reason</p><p class="text-sm font-medium" x-text="form.discharge_reason || '—'"></p></div>
                <div><p class="text-xs text-[#64748B]">Discharge Date</p><p class="text-sm font-medium" x-text="form.discharge_date || '—'"></p></div>
            </div>
        </div>
        <div>
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Authorization</h3>
                <button @click="step = 4" class="text-xs text-[#2563EB] hover:underline" type="button">Edit</button>
            </div>
            <div class="bg-[#F8FAFC] rounded-lg border border-[#E2E8F0] p-4 grid grid-cols-2 gap-y-3">
                <div><p class="text-xs text-[#64748B]">Auth Reference</p><p class="text-sm font-mono font-semibold" x-text="form.auth_ref || '—'"></p></div>
                <div><p class="text-xs text-[#64748B]">Authorizing Authority</p><p class="text-sm" x-text="form.auth_authority || '—'"></p></div>
            </div>
        </div>

        <div class="flex items-start gap-3 bg-red-50 border border-red-200 rounded-lg p-4">
            <input id="final_confirm" x-model="form.final_confirmed" type="checkbox"
                class="mt-0.5 w-4 h-4 rounded border-[#E2E8F0] text-[#DC2626] focus:ring-[#DC2626] cursor-pointer flex-shrink-0">
            <label for="final_confirm" class="text-sm text-red-700 cursor-pointer">
                I confirm that I have reviewed all discharge information above and I accept responsibility for initiating this discharge.
                <strong>This action will be authorized and recorded by the server.</strong>
            </label>
        </div>
    </div>
</x-card>

{{-- Navigation --}}
<div class="flex items-center justify-between mt-5">
    <div>
        <x-button.secondary href="{{ route('inmates.show', $inmate['id']) }}" x-show="step === 1">Cancel</x-button.secondary>
        <x-button.secondary @click="prevStep()" x-show="step > 1" type="button">← Previous</x-button.secondary>
    </div>
    <div class="flex items-center gap-3">
        <span class="text-xs text-[#94A3B8]" x-text="`Step ${step} of 5`"></span>
        <button @click="nextStep()" x-show="step === 1" type="button"
            :disabled="!confirmed_identity"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-brand rounded-lg hover:bg-brand-hover transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
            Continue →
        </button>
        <button @click="nextStep()" x-show="step > 1 && step < 5" type="button"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-brand rounded-lg hover:bg-brand-hover transition-colors">
            Next →
        </button>
        <button x-show="step === 5" type="button"
            :disabled="!form.final_confirmed"
            @click="submitRecord()"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-[#DC2626] rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
            Confirm Discharge
        </button>
    </div>
</div>

<div x-show="submitted" x-transition class="mt-4" style="display:none">
    
</div>

</div>{{-- end x-data --}}

<script>
function dischargeForm() {
    const now = new Date();
    const today = `${now.getFullYear()}-${String(now.getMonth()+1).padStart(2,'0')}-${String(now.getDate()).padStart(2,'0')}`;
    return {
        step: 1,
        submitted: false,
        todayStr: today,
        confirmed_identity: false,
        form: {
            discharge_reason: '',
            discharge_date: today,
            discharge_notes: '',
            auth_ref: '',
            auth_authority: '',
            final_confirmed: false,
        },
        errors: {},
        init() { Object.assign(this.form, @json(old()));},
        ve(field) {
            if (!this.form[field]) {
                this.errors[field] = 'This field is required.';
            } else {
                delete this.errors[field];
            }
            this.errors = { ...this.errors };
        },
        validateStep(s) {
            const stepFields = {
                1: [], 2: [],
                3: ['discharge_reason', 'discharge_date'],
                4: ['auth_ref', 'auth_authority'],
                5: [],
            };
            let valid = true;
            (stepFields[s] || []).forEach(f => { this.ve(f); if (this.errors[f]) valid = false; });
            return valid;
        },
        nextStep() {
            if (!this.validateStep(this.step)) return;
            if (this.step < 5) this.step++;
        },
        prevStep() { if (this.step > 1) this.step--; },
        submitRecord() {
            if (!this.confirmed_identity) { this.step = 1; return; }
            for (let s = 2; s <= 4; s++) {
                if (!this.validateStep(s)) { this.step = s; return; }
            }
            if (!this.form.final_confirmed) { return; }
            this.submitted = window.pmsSubmit({{ Js::from(route('inmates.discharge.store', $inmate['id'])) }}, this.form);
        },
    };
}
</script>

@endsection
