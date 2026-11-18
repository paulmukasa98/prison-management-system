@extends('layouts.app')
@section('title', 'New Assignment')
@section('content')

<x-page-heading title="New Facility Assignment" description="Record a personnel posting to a facility."
    :breadcrumbs="[['label'=>'Dashboard','route'=>'dashboard'],['label'=>'Personnel','route'=>'personnel.index'],['label'=>'Assignments','route'=>'personnel.assignments'],['label'=>'New']]"
>
    @permission('personnel.read')
<x-button.secondary href="{{ route('personnel.assignments') }}">Cancel</x-button.secondary>
@endpermission
</x-page-heading>

<x-alert type="warning" class="mb-5"><strong>This action will be authorized and recorded by the server.</strong> This form does not create a real assignment record.</x-alert>

<script>window.__PERSONNEL_LIST__ = @json($personnel);</script>

<div x-data="assignmentForm()" @beforeunload.window="warnUnsaved($event)">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="space-y-5">
            {{-- Personnel selector --}}
            <x-card>
                <x-section-heading title="Select Personnel" description="Search for the officer to be assigned."/>
                <div class="relative mb-3">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3" aria-hidden="true"><svg class="h-4 w-4 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></div>
                    <input type="search" x-model="psearch" @input="filterPersonnel()" placeholder="Staff number or name…" class="w-full pl-9 pr-4 py-2 text-sm border border-[#E2E8F0] rounded-lg placeholder-[#94A3B8] focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div class="border border-[#E2E8F0] rounded-lg overflow-hidden max-h-56 overflow-y-auto">
                    <template x-if="filteredPersonnel.length===0"><div class="p-6 text-center text-sm text-[#64748B]">No personnel found.</div></template>
                    <template x-for="p in filteredPersonnel" :key="p.id">
                        <button type="button" @click="selectPerson(p)"
                            :class="form.personnel_id===p.id?'bg-[#DBEAFE] border-l-4 border-brand':'hover:bg-[#F8FAFC] border-l-4 border-transparent'"
                            class="w-full flex items-center gap-3 p-3 text-left border-b border-[#F1F5F9] last:border-0 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-[#DBEAFE] flex items-center justify-center flex-shrink-0" aria-hidden="true"><span class="text-xs font-bold text-[#2563EB]" x-text="p.initials"></span></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-[#0F172A]" x-text="p.full_name"></p>
                                <p class="text-xs text-[#64748B]" x-text="`${p.staff_number} · ${p.rank}`"></p>
                            </div>
                            <div x-show="form.personnel_id===p.id" class="text-[#2563EB]" aria-hidden="true"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div>
                        </button>
                    </template>
                </div>
                <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.personnel_id" x-text="errors.personnel_id"></p>
            </x-card>

            {{-- Assignment details --}}
            <x-card>
                <x-section-heading title="Assignment Details"/>
                <div class="space-y-4">
                    <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Current Facility</label>
                        <input type="text" :value="selectedPerson ? (selectedPerson.facility||'HQ / Unassigned') : '—'" disabled class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg bg-[#F8FAFC] text-[#64748B] cursor-not-allowed"></div>
                    <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Destination Facility <span class="text-[#DC2626]" aria-hidden="true">*</span></label>
                        <select x-model="form.facility" @change="v('facility')" :class="errors.facility?'border-[#DC2626]':'border-[#E2E8F0]'" class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand">
                            <option value="">Select facility</option>
                            @foreach($facilities as $f)<option>{{ $f }}</option>@endforeach
                        </select>
                        <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.facility" x-text="errors.facility"></p></div>
                    <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Assignment Role</label>
                        <select x-model="form.role" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand">
                            <option value="">Select role</option>
                            @foreach($roles as $r)<option>{{ $r }}</option>@endforeach
                        </select></div>
                    <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Proposed Start Date <span class="text-[#DC2626]" aria-hidden="true">*</span></label>
                        <input x-model="form.start_date" @change="v('start_date')" type="date" :max="todayStr" :class="errors.start_date?'border-[#DC2626]':'border-[#E2E8F0]'" class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand">
                        <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.start_date" x-text="errors.start_date"></p></div>
                    <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Assignment Reason</label>
                        <select x-model="form.reason" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand">
                            <option value="">Select reason</option>
                            @foreach($reasons as $r)<option>{{ $r }}</option>@endforeach
                        </select></div>
                    <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Notes</label>
                        <textarea x-model="form.notes" rows="2" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand resize-y"></textarea></div>
                </div>
            </x-card>
        </div>

        {{-- Review panel --}}
        <div>
            <x-card>
                <x-section-heading title="Assignment Preview"/>
                <dl x-show="form.personnel_id">
                    <x-detail-row label="Personnel">
                        <span x-text="selectedPerson ? selectedPerson.full_name : '—'"></span>
                    </x-detail-row>
                    <x-detail-row label="Staff Number" mono>
                        <span x-text="selectedPerson ? selectedPerson.staff_number : '—'"></span>
                    </x-detail-row>
                    <x-detail-row label="From">
                        <span x-text="selectedPerson ? (selectedPerson.facility||'HQ / Unassigned') : '—'"></span>
                    </x-detail-row>
                    <x-detail-row label="To"><span x-text="form.facility||'—'"></span></x-detail-row>
                    <x-detail-row label="Role"><span x-text="form.role||'—'"></span></x-detail-row>
                    <x-detail-row label="Start Date"><span x-text="form.start_date||'—'"></span></x-detail-row>
                    <x-detail-row label="Reason"><span x-text="form.reason||'—'"></span></x-detail-row>
                </dl>
                <div x-show="!form.personnel_id" class="py-8 text-center text-sm text-[#64748B]">Select a personnel record to preview the assignment.</div>
                <div class="mt-5 pt-4 border-t border-[#E2E8F0]">
                    <button @click="submitRecord()" type="button"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-brand rounded-lg hover:bg-brand-hover transition-colors disabled:opacity-50"
                        :disabled="!form.personnel_id||!form.facility||!form.start_date">
                        Save Assignment
                    </button>
                </div>
                <div x-show="submitted" class="mt-4" style="display:none">
                    
                </div>
            </x-card>
        </div>
    </div>
</div>

<script>
function assignmentForm(){
    const today=new Date().toISOString().split('T')[0];
    return{
        allPersonnel:[],filteredPersonnel:[],selectedPerson:null,
        psearch:'',submitted:false,isDirty:false,todayStr:today,
        form:{personnel_id:null,facility:'',role:'',start_date:today,reason:'',notes:''},
        errors:{},
        init() { Object.assign(this.form, @json(old()));
            this.allPersonnel=window.__PERSONNEL_LIST__||[];
            this.filteredPersonnel=this.allPersonnel;
            const params=new URLSearchParams(window.location.search);
            const pid=parseInt(params.get('personnel_id'))||0;
            if(pid){const p=this.allPersonnel.find(x=>x.id===pid);if(p)this.selectPerson(p);}
            this.$watch('form',()=>{this.isDirty=true;},{deep:true});
        },
        warnUnsaved(e){if(this.isDirty&&!this.submitted){e.preventDefault();e.returnValue='';}},
        filterPersonnel(){const q=this.psearch.toLowerCase().trim();this.filteredPersonnel=q?this.allPersonnel.filter(p=>`${p.staff_number} ${p.full_name}`.toLowerCase().includes(q)):this.allPersonnel;},
        selectPerson(p){this.selectedPerson=p;this.form.personnel_id=p.id;delete this.errors.personnel_id;this.errors={...this.errors};},
        v(f){if(!this.form[f]){this.errors[f]='Required.';}else{delete this.errors[f];}this.errors={...this.errors};},
        submitRecord(){this.submitted=window.pmsSubmit({{ Js::from(route('personnel.assignment.store')) }}, this.form);this.isDirty=false;},
    };
}
</script>
@endsection
