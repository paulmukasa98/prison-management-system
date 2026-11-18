@extends('layouts.app')

@section('title', 'New Inmate Admission')

@section('content')

<x-page-heading
    title="New Inmate Admission"
    description="Register a new inmate into the prison management system — authorized workflow."
    :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Inmates',   'route' => 'inmates.index'],
        ['label' => 'New Admission'],
    ]"
/>



<div x-data="admissionForm()" @photo-changed="isDirty = true" @beforeunload.window="warnUnsaved($event)">

{{-- Step indicator --}}
<div class="mb-6">
    <x-step-indicator
        :steps="['Personal Info', 'Custody & Legal', 'Facility', 'Additional', 'Review']"
        :current="1"
        reactive-current="step"
    />
</div>

{{-- =====================================================================
     STEP 1 — Personal Information
     ===================================================================== --}}
<x-card x-show="step === 1">
    <x-section-heading title="Step 1 — Personal Information" description="Enter the inmate's personal and identification details." />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        {{-- First Name --}}
        <div>
            <label for="first_name" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                First Name <span class="text-[#DC2626]" aria-hidden="true">*</span>
            </label>
            <input id="first_name" x-model="form.first_name" @blur="validateField('first_name')" type="text"
                :class="errors.first_name ? 'border-[#DC2626]' : 'border-[#E2E8F0]'"
                class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent"
                placeholder="Enter first name" required>
            <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.first_name" x-text="errors.first_name" role="alert"></p>
        </div>
        {{-- Middle Name --}}
        <div>
            <label for="middle_name" class="block text-sm font-medium text-[#0F172A] mb-1.5">Middle Name <span class="text-xs text-[#94A3B8]">(optional)</span></label>
            <input id="middle_name" x-model="form.middle_name" type="text"
                class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent"
                placeholder="Middle name">
        </div>
        {{-- Last Name --}}
        <div>
            <label for="last_name" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                Last Name <span class="text-[#DC2626]" aria-hidden="true">*</span>
            </label>
            <input id="last_name" x-model="form.last_name" @blur="validateField('last_name')" type="text"
                :class="errors.last_name ? 'border-[#DC2626]' : 'border-[#E2E8F0]'"
                class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent"
                placeholder="Enter last name" required>
            <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.last_name" x-text="errors.last_name" role="alert"></p>
        </div>
        {{-- Gender --}}
        <div>
            <label for="gender" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                Gender / Recorded Sex <span class="text-[#DC2626]" aria-hidden="true">*</span>
            </label>
            <select id="gender" x-model="form.gender" @change="validateField('gender')"
                :class="errors.gender ? 'border-[#DC2626]' : 'border-[#E2E8F0]'"
                class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent" required>
                <option value="">Select</option>
                <option>Male</option>
                <option>Female</option>
            </select>
            <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.gender" x-text="errors.gender" role="alert"></p>
        </div>
        {{-- Date of Birth --}}
        <div>
            <label for="dob" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                Date of Birth <span class="text-[#DC2626]" aria-hidden="true">*</span>
            </label>
            <input id="dob" x-model="form.date_of_birth" @change="validateField('date_of_birth')" type="date"
                :max="todayStr"
                :class="errors.date_of_birth ? 'border-[#DC2626]' : 'border-[#E2E8F0]'"
                class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent" required>
            <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.date_of_birth" x-text="errors.date_of_birth" role="alert"></p>
        </div>
        {{-- Nationality --}}
        <div>
            <label for="nationality" class="block text-sm font-medium text-[#0F172A] mb-1.5">Nationality</label>
            <input id="nationality" x-model="form.nationality" type="text"
                class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent"
                placeholder="e.g. Ugandan" value="Ugandan">
        </div>
        {{-- District --}}
        <div>
            <label for="district" class="block text-sm font-medium text-[#0F172A] mb-1.5">District of Origin</label>
            <input id="district" x-model="form.district" type="text"
                class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent"
                placeholder="e.g. Kampala">
        </div>
        {{-- ID Type --}}
        <div>
            <label for="id_type" class="block text-sm font-medium text-[#0F172A] mb-1.5">Identification Type</label>
            <select id="id_type" x-model="form.id_type"
                class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent">
                <option value="">Select ID type</option>
                <option>National ID</option>
                <option>Passport</option>
                <option>Driving Permit</option>
                <option>Refugee ID</option>
                <option>None / Not Presented</option>
            </select>
        </div>
        {{-- ID Number --}}
        <div>
            <label for="id_number" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                Identification Number
                <span class="text-xs text-[#94A3B8]">(if applicable)</span>
            </label>
            <input id="id_number" x-model="form.id_number" type="text"
                :disabled="form.id_type === 'None / Not Presented' || !form.id_type"
                class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent disabled:bg-[#F8FAFC] disabled:cursor-not-allowed"
                placeholder="Document number">
        </div>
    </div>

    <x-inmate-photo-upload :required="true" />
</x-card>

{{-- =====================================================================
     STEP 2 — Custody & Legal
     ===================================================================== --}}
<x-card x-show="step === 2">
    <x-section-heading title="Step 2 — Custody & Legal Information" description="Provide custody classification and legal basis for detention." />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div>
            <label for="prisoner_number" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                Prisoner Number <span class="text-[#DC2626]" aria-hidden="true">*</span>
            </label>
            <input id="prisoner_number" x-model="form.prisoner_number" @blur="validateField('prisoner_number')" type="text"
                :class="errors.prisoner_number ? 'border-[#DC2626]' : 'border-[#E2E8F0]'"
                class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent font-mono"
                placeholder="Assigned by facility office" required>
            <p class="mt-1 text-xs text-[#64748B]">Assigned by the receiving facility. The official format will be configured in the backend.</p>
            <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.prisoner_number" x-text="errors.prisoner_number" role="alert"></p>
        </div>
        <div>
            <label for="admission_ref" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                Admission Reference <span class="text-[#DC2626]" aria-hidden="true">*</span>
            </label>
            <input id="admission_ref" x-model="form.admission_ref" @blur="validateField('admission_ref')" type="text"
                :class="errors.admission_ref ? 'border-[#DC2626]' : 'border-[#E2E8F0]'"
                class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent font-mono"
                placeholder="e.g. ADM-2025-0001" required>
            <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.admission_ref" x-text="errors.admission_ref" role="alert"></p>
        </div>
        <div>
            <label for="classification" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                Custody Classification <span class="text-[#DC2626]" aria-hidden="true">*</span>
            </label>
            <select id="classification" x-model="form.classification" @change="validateField('classification')"
                :class="errors.classification ? 'border-[#DC2626]' : 'border-[#E2E8F0]'"
                class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent" required>
                <option value="">Select classification</option>
                <option>Convicted</option>
                <option>Remand</option>
                <option>Civil Debtor</option>
            </select>
            <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.classification" x-text="errors.classification" role="alert"></p>
        </div>
        <div class="lg:col-span-2">
            <label for="legal_basis" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                Legal Basis for Detention <span class="text-[#DC2626]" aria-hidden="true">*</span>
            </label>
            <input id="legal_basis" x-model="form.legal_basis" @blur="validateField('legal_basis')" type="text"
                :class="errors.legal_basis ? 'border-[#DC2626]' : 'border-[#E2E8F0]'"
                class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent"
                placeholder="e.g. Sentenced — High Court of Uganda" required>
            <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.legal_basis" x-text="errors.legal_basis" role="alert"></p>
        </div>
        <div>
            <label for="warrant_ref" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                Warrant / Court Order Ref <span class="text-[#DC2626]" aria-hidden="true">*</span>
            </label>
            <input id="warrant_ref" x-model="form.warrant_ref" @blur="validateField('warrant_ref')" type="text"
                :class="errors.warrant_ref ? 'border-[#DC2626]' : 'border-[#E2E8F0]'"
                class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent font-mono"
                placeholder="HCT-00-CR-2025-0001" required>
            <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.warrant_ref" x-text="errors.warrant_ref" role="alert"></p>
        </div>
        <div class="lg:col-span-2">
            <label for="issuing_authority" class="block text-sm font-medium text-[#0F172A] mb-1.5">Issuing Authority</label>
            <input id="issuing_authority" x-model="form.issuing_authority" type="text"
                class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent"
                placeholder="e.g. High Court of Uganda, Kampala">
        </div>
        <div>
            <label for="case_ref" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                Case Reference <span class="text-xs text-[#94A3B8]">(if available)</span>
            </label>
            <input id="case_ref" x-model="form.case_ref" type="text"
                class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent font-mono"
                placeholder="Criminal Case No. 001/2025">
        </div>
        <div>
            <label for="admission_date" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                Date of Admission <span class="text-[#DC2626]" aria-hidden="true">*</span>
            </label>
            <input id="admission_date" x-model="form.admission_date" @change="validateField('admission_date')" type="date"
                :max="todayStr"
                :class="errors.admission_date ? 'border-[#DC2626]' : 'border-[#E2E8F0]'"
                class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent" required>
            <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.admission_date" x-text="errors.admission_date" role="alert"></p>
        </div>

        {{-- Conditional — Remand --}}
        <div x-show="form.classification === 'Remand'">
            <label for="next_court_date" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                Next Court Date <span class="text-xs text-[#94A3B8]">(if known)</span>
            </label>
            <input id="next_court_date" x-model="form.next_court_date" type="date"
                class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent">
        </div>

        {{-- Conditional — Convicted --}}
        <div x-show="form.classification === 'Convicted'">
            <label for="sentence" class="block text-sm font-medium text-[#0F172A] mb-1.5">Sentence</label>
            <input id="sentence" x-model="form.sentence" type="text"
                class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent"
                placeholder="e.g. 5 years imprisonment">
        </div>
        <div x-show="form.classification === 'Convicted'">
            <label for="expected_release" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                Expected Release Date
                <span class="text-xs text-[#94A3B8]">(if explicitly recorded)</span>
            </label>
            <input id="expected_release" x-model="form.expected_release" type="date"
                class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent">
            <p class="mt-1 text-xs text-[#64748B]">Release dates are not automatically calculated. Enter only if explicitly recorded on the warrant.</p>
        </div>
    </div>
</x-card>

{{-- =====================================================================
     STEP 3 — Facility Assignment
     ===================================================================== --}}
<x-card x-show="step === 3">
    <x-section-heading title="Step 3 — Facility Assignment" description="Record the receiving facility and admission logistics." />

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="facility" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                Receiving Prison Facility <span class="text-[#DC2626]" aria-hidden="true">*</span>
            </label>
            <select id="facility" x-model="form.facility" @change="validateField('facility')"
                :class="errors.facility ? 'border-[#DC2626]' : 'border-[#E2E8F0]'"
                class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent" required>
                <option value="">Select facility</option>
                @foreach($facilities as $f)
                    <option>{{ $f }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.facility" x-text="errors.facility" role="alert"></p>
        </div>
        <div>
            <label for="admission_time" class="block text-sm font-medium text-[#0F172A] mb-1.5">Admission Time</label>
            <input id="admission_time" x-model="form.admission_time" type="time"
                class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-[#0F172A] mb-1.5">Receiving Officer</label>
            <div class="flex items-center gap-3 p-3 bg-[#F8FAFC] border border-[#E2E8F0] rounded-lg">
                <div class="w-8 h-8 rounded-full bg-brand flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-bold text-white">{{ $demoContext['demo_user']['initials'] }}</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-[#0F172A]">{{ $demoContext['demo_user']['name'] }}</p>
                    <p class="text-xs text-[#64748B]">{{ $demoContext['demo_user']['role'] }} <em>(authenticated)</em></p>
                </div>
            </div>
            <p class="mt-1 text-xs text-[#64748B]">The server records the authenticated actor for every admission.</p>
        </div>
        <div class="sm:col-span-2">
            <label for="admission_notes" class="block text-sm font-medium text-[#0F172A] mb-1.5">Admission Notes</label>
            <textarea id="admission_notes" x-model="form.admission_notes" rows="3"
                class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent resize-y"
                placeholder="Any relevant notes regarding this admission…"></textarea>
        </div>
    </div>
</x-card>

{{-- =====================================================================
     STEP 4 — Additional Information
     ===================================================================== --}}
<x-card x-show="step === 4">
    <x-section-heading title="Step 4 — Additional Information" description="Next-of-kin and administrative notes." />

    <x-alert type="warning" class="mb-4" :dismissible="false">
        Sensitive medical or health information must not be entered into general administrative notes. Medical records are managed in a restricted module.
    </x-alert>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label for="kin_name" class="block text-sm font-medium text-[#0F172A] mb-1.5">Next of Kin Name <span class="text-xs text-[#94A3B8]">(optional)</span></label>
            <input id="kin_name" x-model="form.kin_name" type="text"
                class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent"
                placeholder="Full name">
        </div>
        <div>
            <label for="kin_relationship" class="block text-sm font-medium text-[#0F172A] mb-1.5">Relationship <span class="text-xs text-[#94A3B8]">(optional)</span></label>
            <input id="kin_relationship" x-model="form.kin_relationship" type="text"
                class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent"
                placeholder="e.g. Spouse, Parent">
        </div>
        <div>
            <label for="kin_contact" class="block text-sm font-medium text-[#0F172A] mb-1.5">Contact Number <span class="text-xs text-[#94A3B8]">(optional)</span></label>
            <input id="kin_contact" x-model="form.kin_contact" type="tel"
                class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent"
                placeholder="+256 700 000 000">
        </div>
        <div class="sm:col-span-3">
            <label for="notes" class="block text-sm font-medium text-[#0F172A] mb-1.5">Administrative Notes <span class="text-xs text-[#94A3B8]">(optional)</span></label>
            <textarea id="notes" x-model="form.notes" rows="4"
                class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent resize-y"
                placeholder="General administrative notes relevant to this record…"></textarea>
        </div>
    </div>
</x-card>

{{-- =====================================================================
     STEP 5 — Review
     ===================================================================== --}}
<x-card x-show="step === 5">
    <x-section-heading title="Step 5 — Review Admission" description="Verify all details before submitting for authorization." />

    <div class="space-y-5">
        {{-- Personal --}}
        <div>
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Personal Details</h3>
                <button @click="step = 1" class="text-xs text-[#2563EB] hover:underline" type="button">Edit</button>
            </div>
            <div class="bg-[#F8FAFC] rounded-lg border border-[#E2E8F0] p-4 grid grid-cols-2 sm:grid-cols-3 gap-y-3 gap-x-4">
                <div><p class="text-xs text-[#64748B]">Full Name</p><p class="text-sm font-medium text-[#0F172A]" x-text="form.first_name + ' ' + (form.middle_name ? form.middle_name + ' ' : '') + form.last_name"></p></div>
                <div><p class="text-xs text-[#64748B]">Gender</p><p class="text-sm font-medium text-[#0F172A]" x-text="form.gender || '—'"></p></div>
                <div><p class="text-xs text-[#64748B]">Date of Birth</p><p class="text-sm font-medium text-[#0F172A]" x-text="form.date_of_birth || '—'"></p></div>
                <div><p class="text-xs text-[#64748B]">Nationality</p><p class="text-sm font-medium text-[#0F172A]" x-text="form.nationality || '—'"></p></div>
                <div><p class="text-xs text-[#64748B]">District</p><p class="text-sm font-medium text-[#0F172A]" x-text="form.district || '—'"></p></div>
                <div><p class="text-xs text-[#64748B]">ID Type</p><p class="text-sm font-medium text-[#0F172A]" x-text="form.id_type || '—'"></p></div>
            </div>
        </div>

        {{-- Custody --}}
        <div>
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Custody Information</h3>
                <button @click="step = 2" class="text-xs text-[#2563EB] hover:underline" type="button">Edit</button>
            </div>
            <div class="bg-[#F8FAFC] rounded-lg border border-[#E2E8F0] p-4 grid grid-cols-2 sm:grid-cols-3 gap-y-3 gap-x-4">
                <div><p class="text-xs text-[#64748B]">Prisoner Number</p><p class="text-sm font-mono font-semibold text-[#2563EB]" x-text="form.prisoner_number || '—'"></p></div>
                <div><p class="text-xs text-[#64748B]">Admission Ref</p><p class="text-sm font-mono text-[#0F172A]" x-text="form.admission_ref || '—'"></p></div>
                <div><p class="text-xs text-[#64748B]">Classification</p><p class="text-sm font-medium text-[#0F172A]" x-text="form.classification || '—'"></p></div>
                <div><p class="text-xs text-[#64748B]">Legal Basis</p><p class="text-sm font-medium text-[#0F172A]" x-text="form.legal_basis || '—'"></p></div>
                <div><p class="text-xs text-[#64748B]">Warrant Ref</p><p class="text-sm font-mono text-[#0F172A]" x-text="form.warrant_ref || '—'"></p></div>
                <div><p class="text-xs text-[#64748B]">Admission Date</p><p class="text-sm font-medium text-[#0F172A]" x-text="form.admission_date || '—'"></p></div>
            </div>
        </div>

        {{-- Facility --}}
        <div>
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Facility Assignment</h3>
                <button @click="step = 3" class="text-xs text-[#2563EB] hover:underline" type="button">Edit</button>
            </div>
            <div class="bg-[#F8FAFC] rounded-lg border border-[#E2E8F0] p-4 grid grid-cols-2 sm:grid-cols-3 gap-y-3 gap-x-4">
                <div><p class="text-xs text-[#64748B]">Facility</p><p class="text-sm font-medium text-[#0F172A]" x-text="form.facility || '—'"></p></div>
                <div><p class="text-xs text-[#64748B]">Admission Time</p><p class="text-sm font-medium text-[#0F172A]" x-text="form.admission_time || '—'"></p></div>
                <div><p class="text-xs text-[#64748B]">Receiving Officer</p><p class="text-sm font-medium text-[#0F172A]">{{ $demoContext['demo_user']['name'] }} <em class="not-italic text-[#94A3B8]">(authenticated)</em></p></div>
            </div>
        </div>

        {{-- Additional --}}
        <div>
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Additional Information</h3>
                <button @click="step = 4" class="text-xs text-[#2563EB] hover:underline" type="button">Edit</button>
            </div>
            <div class="bg-[#F8FAFC] rounded-lg border border-[#E2E8F0] p-4">
                <p class="text-xs text-[#64748B]">Next of Kin</p>
                <p class="text-sm text-[#0F172A]" x-text="form.kin_name ? (form.kin_name + (form.kin_relationship ? ' (' + form.kin_relationship + ')' : '')) : 'Not recorded'"></p>
            </div>
        </div>

        {{-- Confirmation checkbox --}}
        <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-lg p-4">
            <input id="confirm_review" x-model="form.confirmed" type="checkbox"
                class="mt-0.5 w-4 h-4 rounded border-[#E2E8F0] text-[#2563EB] focus:ring-brand cursor-pointer flex-shrink-0">
            <label for="confirm_review" class="text-sm text-amber-800 cursor-pointer">
                I confirm that I have reviewed all the information displayed above and it is accurate to the best of my knowledge.
                <strong>This action will be authorized and recorded by the server.</strong>
            </label>
        </div>
    </div>
</x-card>

{{-- =====================================================================
     NAVIGATION BUTTONS
     ===================================================================== --}}
<div class="flex items-center justify-between mt-5">
    <div>
        @permission('inmates.read')
<x-button.secondary href="{{ route('inmates.index') }}" x-show="step === 1">Cancel</x-button.secondary>
@endpermission
        <x-button.secondary @click="prevStep()" x-show="step > 1" type="button">← Previous</x-button.secondary>
    </div>
    <div class="flex items-center gap-3">
        <span class="text-xs text-[#94A3B8]" x-text="`Step ${step} of 5`"></span>
        <button @click="nextStep()" x-show="step < 5" type="button"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-brand rounded-lg hover:bg-brand-hover transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand">
            Next →
        </button>
        <button x-show="step === 5" type="button"
            :disabled="!form.confirmed"
            @click="submitRecord()"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-[#16A34A] rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed focus-visible:outline focus-visible:outline-2 focus-visible:outline-green-600">
            Record Admission
        </button>
    </div>
</div>

{{-- Preview confirmation alert --}}
<div x-show="submitted" x-transition class="mt-4" style="display:none">
    
</div>

</div>{{-- end x-data --}}

<script>
function admissionForm() {
    const now = new Date();
    const today = `${now.getFullYear()}-${String(now.getMonth()+1).padStart(2,'0')}-${String(now.getDate()).padStart(2,'0')}`;
    return {
        step: 1,
        submitted: false,

        todayStr: today,

        form: {
            // Step 1
            first_name: '', middle_name: '', last_name: '',
            gender: '', date_of_birth: '', nationality: 'Ugandan',
            district: '', id_type: '', id_number: '',
            // Step 2
            prisoner_number: '', admission_ref: '', classification: '',
            legal_basis: '', warrant_ref: '', issuing_authority: '',
            case_ref: '', admission_date: today, next_court_date: '',
            sentence: '', expected_release: '',
            // Step 3
            facility: '', admission_time: '', admission_notes: '',
            // Step 4
            kin_name: '', kin_relationship: '', kin_contact: '', notes: '',
            // Step 5
            confirmed: false,
        },

        errors: {},

        isDirty: false,

        init() { Object.assign(this.form, @json(old()));
            this.$watch('form', () => { this.isDirty = true; }, { deep: true });
        },

        warnUnsaved(e) {
            if (this.isDirty && !this.submitted) {
                e.preventDefault();
                e.returnValue = '';
            }
        },


        validateField(field) {
            const required = ['first_name', 'last_name', 'gender', 'date_of_birth',
                              'prisoner_number', 'admission_ref', 'classification',
                              'legal_basis', 'warrant_ref', 'admission_date', 'facility'];
            if (required.includes(field) && !this.form[field]) {
                this.errors[field] = 'This field is required.';
            } else if (field === 'date_of_birth' && this.form.date_of_birth > this.todayStr) {
                this.errors[field] = 'Date of birth cannot be in the future.';
            } else if (field === 'admission_date' && this.form.admission_date > this.todayStr) {
                this.errors[field] = 'Admission date cannot be in the future.';
            } else {
                delete this.errors[field];
            }
            this.errors = { ...this.errors };
        },

        validateStep(s) {
            const stepFields = {
                1: ['first_name', 'last_name', 'gender', 'date_of_birth'],
                2: ['prisoner_number', 'admission_ref', 'classification', 'legal_basis', 'warrant_ref', 'admission_date'],
                3: ['facility'],
                4: [],
                5: [],
            };
            let valid = true;
            (stepFields[s] || []).forEach(f => {
                this.validateField(f);
                if (this.errors[f]) { valid = false; }
            });
            const photo = this.$root.querySelector('input[name=photo]');
            if (s === 1 && photo && !photo.checkValidity()) { valid = false; }
            return valid;
        },

        nextStep() {
            if (!this.validateStep(this.step)) { return; }
            if (this.step < 5) { this.step++; }
        },

        prevStep() {
            if (this.step > 1) { this.step--; }
        },

        submitRecord() {
            for (let s = 1; s <= 4; s++) {
                if (!this.validateStep(s)) { this.step = s; return; }
            }
            if (!this.form.confirmed) { return; }
            this.submitted = window.pmsSubmit({{ Js::from(route('inmates.store')) }}, this.form, {photo: this.$root.querySelector('input[name=photo]')});
            this.isDirty   = false;
        },
    };
}
</script>

@endsection
