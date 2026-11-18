@extends('layouts.app')
@section('title', 'Register Personnel')
@section('content')

<x-page-heading title="Register Personnel" description="Add a new personnel record — authorized workflow."
    :breadcrumbs="[['label'=>'Dashboard','route'=>'dashboard'],['label'=>'Personnel','route'=>'personnel.index'],['label'=>'Register']]"/>



<div x-data="personnelForm()" @beforeunload.window="warnUnsaved($event)">
<div class="mb-6">
    <x-step-indicator :steps="['Personal Info','Employment','Assignment','Contact','Review']" :current="1" reactive-current="step"/>
</div>

{{-- Step 1 --}}
<x-card x-show="step===1">
    <x-section-heading title="Step 1 — Personal Information"/>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">First Name <span class="text-[#DC2626]" aria-hidden="true">*</span></label>
            <input x-model="form.first_name" @blur="v('first_name')" type="text" :class="errors.first_name?'border-[#DC2626]':'border-[#E2E8F0]'" class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand" placeholder="First name">
            <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.first_name" x-text="errors.first_name" role="alert"></p></div>
        <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Middle Name <span class="text-xs text-[#94A3B8]">(optional)</span></label>
            <input x-model="form.middle_name" type="text" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand"></div>
        <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Last Name <span class="text-[#DC2626]" aria-hidden="true">*</span></label>
            <input x-model="form.last_name" @blur="v('last_name')" type="text" :class="errors.last_name?'border-[#DC2626]':'border-[#E2E8F0]'" class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand" placeholder="Last name">
            <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.last_name" x-text="errors.last_name" role="alert"></p></div>
        <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Gender <span class="text-[#DC2626]" aria-hidden="true">*</span></label>
            <select x-model="form.gender" @change="v('gender')" :class="errors.gender?'border-[#DC2626]':'border-[#E2E8F0]'" class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand">
                <option value="">Select</option><option>Male</option><option>Female</option></select>
            <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.gender" x-text="errors.gender" role="alert"></p></div>
        <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Date of Birth</label>
            <input x-model="form.date_of_birth" type="date" :max="todayStr" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand"></div>
        <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Nationality</label>
            <input x-model="form.nationality" type="text" value="Ugandan" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand"></div>
    </div>
    <div class="mt-4 pt-4 border-t border-[#E2E8F0]">
        <p class="text-sm font-medium text-[#0F172A] mb-2">Photograph <span class="text-xs text-[#94A3B8]">(optional — preview only)</span></p>
        <div class="flex items-center gap-4">
            <div class="w-20 h-24 bg-[#F1F5F9] rounded-lg border-2 border-dashed border-[#E2E8F0] flex items-center justify-center overflow-hidden flex-shrink-0">
                <template x-if="!photoPreview"><div class="text-center px-2"><svg class="w-6 h-6 text-[#94A3B8] mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></div></template>
                <img x-show="photoPreview" :src="photoPreview" alt="Preview" class="w-full h-full object-cover">
            </div>
            <div>
                <label class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-[#0F172A] bg-white border border-[#E2E8F0] rounded-lg cursor-pointer hover:bg-[#F8FAFC]">
                    <svg class="w-4 h-4 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Upload Photo</label>
                <input type="file" accept="image/*" class="sr-only" @change="previewPhoto($event)">
                <p class="text-xs text-[#64748B] mt-1">Preview only — not uploaded.</p>
            </div>
        </div>
    </div>
</x-card>

{{-- Step 2 --}}
<x-card x-show="step===2">
    <x-section-heading title="Step 2 — Employment Information"/>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Staff Number <span class="text-[#DC2626]" aria-hidden="true">*</span></label>
            <input x-model="form.staff_number" @blur="v('staff_number')" type="text" :class="errors.staff_number?'border-[#DC2626]':'border-[#E2E8F0]'" class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand font-mono" placeholder="DEMO-STF-0001">
            <p class="mt-1 text-xs text-[#64748B]">The official format will be configured in the backend.</p>
            <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.staff_number" x-text="errors.staff_number" role="alert"></p></div>
        <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Rank / Position <span class="text-[#DC2626]" aria-hidden="true">*</span></label>
            <select x-model="form.rank" @change="v('rank')" :class="errors.rank?'border-[#DC2626]':'border-[#E2E8F0]'" class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand">
                <option value="">Select rank</option>
                @foreach($ranks as $r)<option>{{ $r }}</option>@endforeach
            </select>
            <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.rank" x-text="errors.rank" role="alert"></p></div>
        <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Department <span class="text-[#DC2626]" aria-hidden="true">*</span></label>
            <select x-model="form.department" @change="v('department')" :class="errors.department?'border-[#DC2626]':'border-[#E2E8F0]'" class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand">
                <option value="">Select department</option>
                @foreach($departments as $d)<option>{{ $d }}</option>@endforeach
            </select>
            <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.department" x-text="errors.department" role="alert"></p></div>
        <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Employment Status</label>
            <select x-model="form.employment_status" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand">
                @foreach($statuses as $s)<option>{{ $s }}</option>@endforeach
            </select></div>
        <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Appointment Date</label>
            <input x-model="form.appointment_date" type="date" :max="todayStr" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand"></div>
    </div>
    <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
        <p class="text-xs text-amber-700">Registering a personnel record does not automatically create a system user account or grant application permissions. User accounts are managed separately by an authorized system administrator.</p>
    </div>
</x-card>

{{-- Step 3 --}}
<x-card x-show="step===3">
    <x-section-heading title="Step 3 — Facility Assignment" description="Leave blank if the officer is initially unassigned."/>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Assigned Facility <span class="text-xs text-[#94A3B8]">(optional)</span></label>
            <select x-model="form.facility" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand">
                <option value="">HQ / Unassigned</option>
                @foreach($facilities as $f)<option>{{ $f }}</option>@endforeach
            </select></div>
        <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Assignment Start Date</label>
            <input x-model="form.assignment_start" type="date" :max="todayStr" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand"></div>
        <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Assignment Role</label>
            <input x-model="form.assignment_role" type="text" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand" placeholder="e.g. Duty Officer"></div>
        <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Assignment Notes</label>
            <textarea x-model="form.assignment_notes" rows="2" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand resize-y"></textarea></div>
    </div>
</x-card>

{{-- Step 4 --}}
<x-card x-show="step===4">
    <x-section-heading title="Step 4 — Contact Information"/>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Official Email</label>
            <input x-model="form.email" type="email" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand" placeholder="officer@demo.ups.go.ug"></div>
        <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Official Phone</label>
            <input x-model="form.phone" type="tel" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand" placeholder="+256 700 000 000"></div>
        <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Emergency Contact Name</label>
            <input x-model="form.kin_name" type="text" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand"></div>
        <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Emergency Contact Number</label>
            <input x-model="form.kin_contact" type="tel" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand"></div>
    </div>
</x-card>

{{-- Step 5 — Review --}}
<x-card x-show="step===5">
    <x-section-heading title="Step 5 — Review Registration" description="Verify all details before submitting for authorization."/>
    <div class="space-y-4">
        <div>
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Personal</h3>
                <button @click="step=1" type="button" class="text-xs text-[#2563EB] hover:underline">Edit</button>
            </div>
            <div class="bg-[#F8FAFC] border border-[#E2E8F0] rounded-lg p-4 grid grid-cols-2 sm:grid-cols-3 gap-y-3">
                <div><p class="text-xs text-[#64748B]">Full Name</p><p class="text-sm font-medium" x-text="(form.first_name+' '+(form.middle_name?form.middle_name+' ':'')+form.last_name).trim()||'—'"></p></div>
                <div><p class="text-xs text-[#64748B]">Gender</p><p class="text-sm" x-text="form.gender||'—'"></p></div>
                <div><p class="text-xs text-[#64748B]">Date of Birth</p><p class="text-sm" x-text="form.date_of_birth||'—'"></p></div>
                <div><p class="text-xs text-[#64748B]">Nationality</p><p class="text-sm" x-text="form.nationality||'—'"></p></div>
            </div>
        </div>
        <div>
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Employment</h3>
                <button @click="step=2" type="button" class="text-xs text-[#2563EB] hover:underline">Edit</button>
            </div>
            <div class="bg-[#F8FAFC] border border-[#E2E8F0] rounded-lg p-4 grid grid-cols-2 sm:grid-cols-3 gap-y-3">
                <div><p class="text-xs text-[#64748B]">Staff Number</p><p class="text-sm font-mono font-semibold text-[#2563EB]" x-text="form.staff_number||'—'"></p></div>
                <div><p class="text-xs text-[#64748B]">Rank</p><p class="text-sm" x-text="form.rank||'—'"></p></div>
                <div><p class="text-xs text-[#64748B]">Department</p><p class="text-sm" x-text="form.department||'—'"></p></div>
                <div><p class="text-xs text-[#64748B]">Status</p><p class="text-sm" x-text="form.employment_status||'—'"></p></div>
                <div><p class="text-xs text-[#64748B]">Appointed</p><p class="text-sm" x-text="form.appointment_date||'—'"></p></div>
                <div><p class="text-xs text-[#64748B]">Facility</p><p class="text-sm" x-text="form.facility||'HQ / Unassigned'"></p></div>
            </div>
        </div>
        <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-lg p-4">
            <input id="confirm_reg" x-model="form.confirmed" type="checkbox" class="mt-0.5 w-4 h-4 rounded border-[#E2E8F0] text-[#2563EB] cursor-pointer flex-shrink-0">
            <label for="confirm_reg" class="text-sm text-amber-800 cursor-pointer">I confirm the information above is accurate. <strong>This action will be authorized and recorded by the server.</strong></label>
        </div>
    </div>
</x-card>

{{-- Navigation --}}
<div class="flex items-center justify-between mt-5">
    <div>
        @permission('personnel.read')
<x-button.secondary href="{{ route('personnel.index') }}" x-show="step===1">Cancel</x-button.secondary>
@endpermission
        <x-button.secondary @click="prevStep()" x-show="step>1" type="button">← Previous</x-button.secondary>
    </div>
    <div class="flex items-center gap-3">
        <span class="text-xs text-[#94A3B8]" x-text="`Step ${step} of 5`"></span>
        <button @click="nextStep()" x-show="step<5" type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-brand rounded-lg hover:bg-brand-hover transition-colors">Next →</button>
        <button x-show="step===5" @click="submitRecord()" :disabled="!form.confirmed" type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-[#16A34A] rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">Register Personnel</button>
    </div>
</div>
<div x-show="submitted" x-transition class="mt-4" style="display:none">
    
</div>
</div>

<script>
function personnelForm(){
    const now=new Date();
    const today=`${now.getFullYear()}-${String(now.getMonth()+1).padStart(2,'0')}-${String(now.getDate()).padStart(2,'0')}`;
    return{step:1,submitted:false,isDirty:false,photoPreview:null,todayStr:today,
        form:{first_name:'',middle_name:'',last_name:'',gender:'',date_of_birth:'',nationality:'Ugandan',staff_number:'',rank:'',department:'',employment_status:'Active',appointment_date:today,facility:'',assignment_start:'',assignment_role:'',assignment_notes:'',email:'',phone:'',kin_name:'',kin_contact:'',confirmed:false},
        errors:{},
        init() { Object.assign(this.form, @json(old()));this.$watch('form',()=>{this.isDirty=true;},{deep:true});},
        warnUnsaved(e){if(this.isDirty&&!this.submitted){e.preventDefault();e.returnValue='';}},
        previewPhoto(ev){const f=ev.target.files[0];if(!f)return;const r=new FileReader();r.onload=e=>{this.photoPreview=e.target.result;};r.readAsDataURL(f);},
        v(field){if(!this.form[field]){this.errors[field]='Required.';}else{delete this.errors[field];}this.errors={...this.errors};},
        validateStep(s){const req={1:['first_name','last_name','gender'],2:['staff_number','rank','department'],3:[],4:[],5:[]};let ok=true;(req[s]||[]).forEach(f=>{this.v(f);if(this.errors[f])ok=false;});return ok;},
        nextStep(){if(!this.validateStep(this.step))return;if(this.step<5)this.step++;},
        prevStep(){if(this.step>1)this.step--;},
        submitRecord(){
            for(let s=1;s<=4;s++){if(!this.validateStep(s)){this.step=s;return;}}
            if(!this.form.confirmed)return;
            this.submitted=window.pmsSubmit({{ Js::from(route('personnel.store')) }}, this.form);this.isDirty=false;
        },
    };
}
</script>
@endsection
