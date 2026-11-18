@extends('layouts.app')
@section('title', 'Register Facility')
@section('content')

<x-page-heading title="Register Facility" description="Add a new facility record — authorized workflow."
    :breadcrumbs="[['label'=>'Dashboard','route'=>'dashboard'],['label'=>'Facilities','route'=>'facilities.index'],['label'=>'Register']]"
>
    @permission('facilities.read')
<x-button.secondary href="{{ route('facilities.index') }}">Cancel</x-button.secondary>
@endpermission
</x-page-heading>



<div x-data="facilityForm()" @beforeunload.window="warnUnsaved($event)">

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <div class="space-y-5">
        {{-- Identity --}}
        <x-card>
            <x-section-heading title="Facility Identity"/>
            <div class="space-y-4">
                <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Facility Name <span class="text-[#DC2626]" aria-hidden="true">*</span></label>
                    <input x-model="form.name" @blur="v('name')" type="text" :class="errors.name?'border-[#DC2626]':'border-[#E2E8F0]'" class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand" placeholder="Facility name">
                    <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.name" x-text="errors.name"></p></div>
                <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Facility Code</label>
                    <input x-model="form.code" type="text" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand font-mono" placeholder="DEMO-FAC-XXX">
                    <p class="mt-1 text-xs text-[#64748B]">Official codes are assigned by the backend. Do not invent official codes.</p></div>
                <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Facility Type <span class="text-[#DC2626]" aria-hidden="true">*</span></label>
                    <select x-model="form.type" @change="v('type')" :class="errors.type?'border-[#DC2626]':'border-[#E2E8F0]'" class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand">
                        <option value="">Select type</option>
                        @foreach($types as $t)<option>{{ $t }}</option>@endforeach
                    </select>
                    <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.type" x-text="errors.type"></p></div>
            </div>
        </x-card>

        {{-- Location --}}
        <x-card>
            <x-section-heading title="Location"/>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Region</label>
                    <select x-model="form.region" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand">
                        <option value="">Select region</option>
                        @foreach($regions as $r)<option>{{ $r }}</option>@endforeach
                    </select></div>
                <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">District</label>
                    <input x-model="form.district" type="text" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand" placeholder="e.g. Kampala"></div>
                <div class="sm:col-span-2"><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Location Description</label>
                    <textarea x-model="form.location_desc" rows="2" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand resize-y"></textarea></div>
            </div>
        </x-card>

        {{-- Capacity --}}
        <x-card>
            <x-section-heading title="Capacity"/>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Recorded Capacity</label>
                    <input x-model="form.capacity" type="number" min="0" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand" placeholder="e.g. 500"></div>
                <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Capacity Effective Date</label>
                    <input x-model="form.capacity_date" type="date" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand"></div>
                <div class="sm:col-span-2"><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Capacity Notes</label>
                    <input x-model="form.capacity_notes" type="text" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand" placeholder="e.g. Figures require verification"></div>
            </div>
        </x-card>

        {{-- Administration --}}
        <x-card>
            <x-section-heading title="Administration"/>
            <div class="space-y-4">
                <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Administrative Status</label>
                    <select x-model="form.status" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand">
                        <option>Active</option><option>Inactive</option><option>Under Renovation</option>
                    </select></div>
                <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Establishment Date <span class="text-xs text-[#94A3B8]">(if known)</span></label>
                    <input x-model="form.established" type="date" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand"></div>
                <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Administrative Notes</label>
                    <textarea x-model="form.notes" rows="2" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand resize-y"></textarea></div>
            </div>
        </x-card>
    </div>

    {{-- Review panel --}}
    <div>
        <x-card>
            <x-section-heading title="Registration Review"/>
            <dl>
                <x-detail-row label="Name"><span x-text="form.name||'—'"></span></x-detail-row>
                <x-detail-row label="Code" mono><span x-text="form.code||'(assigned by backend)'"></span></x-detail-row>
                <x-detail-row label="Type"><span x-text="form.type||'—'"></span></x-detail-row>
                <x-detail-row label="Region"><span x-text="form.region||'—'"></span></x-detail-row>
                <x-detail-row label="District"><span x-text="form.district||'—'"></span></x-detail-row>
                <x-detail-row label="Capacity"><span x-text="form.capacity||'Not recorded'"></span></x-detail-row>
                <x-detail-row label="Status"><span x-text="form.status||'—'"></span></x-detail-row>
            </dl>
            <div class="mt-5 pt-4 border-t border-[#E2E8F0]">
                <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">
                    <input id="confirm_fac" x-model="form.confirmed" type="checkbox" class="mt-0.5 w-4 h-4 cursor-pointer flex-shrink-0">
                    <label for="confirm_fac" class="text-xs text-amber-800 cursor-pointer">I confirm the information above. <strong>This action will be authorized and recorded by the server.</strong></label>
                </div>
                <button @click="submitRecord()" :disabled="!form.confirmed" type="button"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-brand rounded-lg hover:bg-brand-hover transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    Register Facility
                </button>
            </div>
            <div x-show="submitted" class="mt-4" style="display:none">
                
            </div>
        </x-card>
    </div>
</div>
</div>

<script>
function facilityForm(){
    return{
        submitted:false,isDirty:false,errors:{},
        form:{name:'',code:'',type:'',region:'',district:'',location_desc:'',capacity:'',capacity_date:'',capacity_notes:'',status:'Active',established:'',notes:'',confirmed:false},
        init() { Object.assign(this.form, @json(old()));this.$watch('form',()=>{this.isDirty=true;},{deep:true});},
        warnUnsaved(e){if(this.isDirty&&!this.submitted){e.preventDefault();e.returnValue='';}},
        v(f){if(!this.form[f]){this.errors[f]='Required.';}else{delete this.errors[f];}this.errors={...this.errors};},
        submitRecord(){if(!this.form.name||!this.form.type){this.v('name');this.v('type');return;}this.submitted=window.pmsSubmit({{ Js::from(route('facilities.store')) }}, {...this.form, location_description:this.form.location_desc, established_on:this.form.established});this.isDirty=false;},
    };
}
</script>
@endsection
