@extends('layouts.app')
@section('title', 'Edit — ' . $facility['name'])
@section('content')

<x-page-heading title="Edit Facility Details"
    :description="'Editing ' . $facility['name'] . ' (' . $facility['code'] . ')'"
    :breadcrumbs="[['label'=>'Dashboard','route'=>'dashboard'],['label'=>'Facilities','route'=>'facilities.index'],['label'=>$facility['code'],'url'=>route('facilities.show',$facility['id'])],['label'=>'Edit']]"
>
    <x-button.secondary href="{{ route('facilities.show', $facility['id']) }}">Cancel</x-button.secondary>
</x-page-heading>



<div x-data="editFacility()" @beforeunload.window="warnUnsaved($event)">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 space-y-5">
            <x-card>
                <x-section-heading title="Facility Information"/>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Facility Name <span class="text-[#DC2626]" aria-hidden="true">*</span></label>
                        <input x-model="form.name" @blur="v('name')" type="text" :class="errors.name?'border-[#DC2626]':'border-[#E2E8F0]'" class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand"></div>
                    <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Type</label>
                        <select x-model="form.type" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand">
                            @foreach($types as $t)<option>{{ $t }}</option>@endforeach
                        </select></div>
                    <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Region</label>
                        <select x-model="form.region" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand">
                            @foreach($regions as $r)<option>{{ $r }}</option>@endforeach
                        </select></div>
                    <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">District</label>
                        <input x-model="form.district" type="text" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand"></div>
                    <div class="sm:col-span-2"><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Administrative Notes</label>
                        <textarea x-model="form.notes" rows="2" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand resize-y"></textarea></div>
                </div>
            </x-card>

            {{-- Capacity change --}}
            <x-card>
                <x-section-heading title="Capacity Change" description="Propose a new recorded capacity. Original value is shown."/>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Existing Capacity</label>
                        <input type="text" value="{{ number_format($facility['capacity']) }}" disabled class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg bg-[#F8FAFC] text-[#64748B] cursor-not-allowed"></div>
                    <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Proposed Capacity</label>
                        <input x-model="form.capacity" type="number" min="0" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand"></div>
                    <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Effective Date</label>
                        <input x-model="form.capacity_date" type="date" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand"></div>
                    <div class="sm:col-span-3"><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Change Reason</label>
                        <input x-model="form.capacity_reason" type="text" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand" placeholder="Reason for capacity change…"></div>
                </div>
                <p class="text-xs text-[#94A3B8] mt-2"><em>Capacity changes do not automatically alter historical occupancy reports.</em></p>
            </x-card>

            {{-- Change summary --}}
            <div x-show="hasChanges" class="bg-amber-50 border border-amber-200 rounded-xl p-5">
                <p class="text-sm font-semibold text-amber-800 mb-3">Changed Fields</p>
                <ul class="space-y-1.5">
                    <template x-for="c in changedFields" :key="c.field">
                        <li class="text-xs text-amber-700 flex gap-3 flex-wrap">
                            <span class="font-medium capitalize" x-text="c.field.replace('_',' ')"></span>:
                            <span class="line-through text-amber-500" x-text="c.old"></span> →
                            <span class="font-medium" x-text="c.new"></span>
                        </li>
                    </template>
                </ul>
                <p class="text-xs text-amber-600 mt-3"><em>Changes are validated and audited when saved.</em></p>
            </div>

            <div class="flex gap-3 justify-end">
                <x-button.secondary href="{{ route('facilities.show', $facility['id']) }}">Cancel</x-button.secondary>
                <button @click="saveRecord()" type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-brand rounded-lg hover:bg-brand-hover transition-colors disabled:opacity-50" :disabled="Object.keys(errors).length>0">Review Changes</button>
            </div>
            <div x-show="saved" style="display:none"></div>
        </div>

        {{-- Restricted sidebar --}}
        <div class="space-y-5">
            <x-card>
                <x-section-heading title="🔒 Restricted Fields"/>
                <dl>
                    <x-detail-row label="Facility Code" mono>{{ $facility['code'] }}</x-detail-row>
                    <x-detail-row label="Established">{{ $facility['established'] ? \Carbon\Carbon::parse($facility['established'])->format('d M Y') : '—' }}</x-detail-row>
                </dl>
                <p class="text-xs text-[#94A3B8] mt-3">These fields require a formal record amendment process.</p>
            </x-card>
            <x-card>
                <x-section-heading title="Current Occupancy"/>
                <x-occupancy-bar :population="$facility['current_population']" :capacity="$facility['capacity']" :pct="$facility['occupancy_pct']" class="mb-3"/>
                
            </x-card>
        </div>
    </div>
</div>

<script>
function editFacility(){
    const orig = {{ Js::from(\Illuminate\Support\Arr::only($facility, ['name', 'type', 'region', 'district', 'notes', 'capacity', 'capacity_date']) + ['capacity_reason' => '']) }};
    return{
        form:{...orig,...@json(old())},errors:{},saved:false,isDirty:false,
        init() { Object.assign(this.form, @json(old()));this.$watch('form',()=>{this.isDirty=true;},{deep:true});},
        warnUnsaved(e){if(this.isDirty&&!this.saved){e.preventDefault();e.returnValue='';}},
        get hasChanges(){return this.changedFields.length>0;},
        get changedFields(){return Object.keys(this.form).filter(k=>this.form[k]!==orig[k]).map(k=>({field:k,old:orig[k]||'(empty)',new:this.form[k]||'(empty)'}));},
        v(f){if(!this.form[f]){this.errors[f]='Required.';}else{delete this.errors[f];}this.errors={...this.errors};},
        saveRecord(){this.v('name');if(Object.keys(this.errors).length)return;this.saved=window.pmsSubmit({{ Js::from(route('facilities.update', $facility['id'])) }}, {...this.form, status:{{ Js::from($facility['status']) }}});this.isDirty=false;},
    };
}
</script>
@endsection
