@extends('layouts.app')
@section('title', 'Edit — ' . $person['full_name'])
@section('content')

<x-page-heading title="Edit Personnel Details"
    :description="'Editing ' . $person['full_name'] . ' (' . $person['staff_number'] . ')'"
    :breadcrumbs="[['label'=>'Dashboard','route'=>'dashboard'],['label'=>'Personnel','route'=>'personnel.index'],['label'=>$person['staff_number'],'url'=>route('personnel.show',$person['id'])],['label'=>'Edit']]"
>
    <x-button.secondary href="{{ route('personnel.show', $person['id']) }}">Cancel</x-button.secondary>
</x-page-heading>



<div x-data="editPersonnel()" @beforeunload.window="warnUnsaved($event)">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 space-y-5">
            {{-- Editable section --}}
            <x-card>
                <x-section-heading title="Personal Information"/>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">First Name <span class="text-[#DC2626]" aria-hidden="true">*</span></label>
                        <input x-model="form.first_name" @blur="v('first_name')" type="text" :class="errors.first_name?'border-[#DC2626]':'border-[#E2E8F0]'" class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand">
                        <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.first_name" x-text="errors.first_name"></p></div>
                    <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Last Name <span class="text-[#DC2626]" aria-hidden="true">*</span></label>
                        <input x-model="form.last_name" @blur="v('last_name')" type="text" :class="errors.last_name?'border-[#DC2626]':'border-[#E2E8F0]'" class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand">
                        <p class="mt-1 text-xs text-[#DC2626]" x-show="errors.last_name" x-text="errors.last_name"></p></div>
                    <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Gender</label>
                        <select x-model="form.gender" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand"><option>Male</option><option>Female</option></select></div>
                    <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Nationality</label>
                        <input x-model="form.nationality" type="text" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand"></div>
                </div>
            </x-card>
            <x-card>
                <x-section-heading title="Employment"/>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Rank</label>
                        <select x-model="form.rank" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand">
                            @foreach($ranks as $r)<option>{{ $r }}</option>@endforeach
                        </select></div>
                    <div><label class="block text-sm font-medium text-[#0F172A] mb-1.5">Department</label>
                        <select x-model="form.department" class="w-full px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand">
                            @foreach($departments as $d)<option>{{ $d }}</option>@endforeach
                        </select></div>
                </div>
            </x-card>

            {{-- Change summary --}}
            <div x-show="hasChanges" class="bg-amber-50 border border-amber-200 rounded-xl p-5">
                <p class="text-sm font-semibold text-amber-800 mb-3">Changed Fields</p>
                <ul class="space-y-1.5">
                    <template x-for="c in changedFields" :key="c.field">
                        <li class="text-xs text-amber-700 flex gap-3">
                            <span class="font-medium capitalize" x-text="c.field.replace('_',' ')"></span>:
                            <span class="line-through text-amber-500" x-text="c.old"></span>
                            <span class="text-amber-800">→</span>
                            <span class="font-medium" x-text="c.new"></span>
                        </li>
                    </template>
                </ul>
                <p class="text-xs text-amber-600 mt-3"><em>Changes are validated and audited when saved.</em></p>
            </div>

            <div class="flex gap-3 justify-end">
                <x-button.secondary href="{{ route('personnel.show', $person['id']) }}">Cancel</x-button.secondary>
                <button @click="saveRecord()" :disabled="Object.keys(errors).length>0" type="button"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-brand rounded-lg hover:bg-brand-hover transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    Review Changes
                </button>
            </div>
            <div x-show="saved" style="display:none">
                
            </div>
        </div>

        {{-- Restricted sidebar --}}
        <div class="space-y-5">
            <x-card>
                <x-section-heading title="🔒 Restricted Fields" description="Require a controlled process to change."/>
                <dl>
                    <x-detail-row label="Staff Number" mono>{{ $person['staff_number'] }}</x-detail-row>
                    <x-detail-row label="Appointment Date">{{ \Carbon\Carbon::parse($person['appointment_date'])->format('d M Y') }}</x-detail-row>
                </dl>
            </x-card>
            <x-card>
                <x-section-heading title="Assignment"/>
                <dl>
                    <x-detail-row label="Current Facility">{{ $person['facility'] ?? 'HQ / Unassigned' }}</x-detail-row>
                    <x-detail-row label="Status"><x-personnel-status-badge :status="$person['employment_status']"/></x-detail-row>
                </dl>
                <div class="mt-3">
                    @permission('personnel.manage', $person['facility_id'])
<a href="{{ route('personnel.assignment.create', ['personnel_id' => $person['id']]) }}" class="text-xs text-[#2563EB] hover:underline">New Assignment →</a>
@endpermission
                </div>
            </x-card>
        </div>
    </div>
</div>

<script>
function editPersonnel(){
    const orig = {{ Js::from(\Illuminate\Support\Arr::only($person, ['first_name', 'last_name', 'gender', 'nationality', 'rank', 'department'])) }};
    return{
        form:{...orig,...@json(old())},errors:{},saved:false,isDirty:false,
        init() { Object.assign(this.form, @json(old()));this.$watch('form',()=>{this.isDirty=true;},{deep:true});},
        warnUnsaved(e){if(this.isDirty&&!this.saved){e.preventDefault();e.returnValue='';}},
        get hasChanges(){return this.changedFields.length>0;},
        get changedFields(){return Object.keys(this.form).filter(k=>this.form[k]!==orig[k]).map(k=>({field:k,old:orig[k]||'(empty)',new:this.form[k]||'(empty)'}));},
        v(f){if(!this.form[f]){this.errors[f]='Required.';}else{delete this.errors[f];}this.errors={...this.errors};},
        saveRecord(){this.v('first_name');this.v('last_name');if(Object.keys(this.errors).length)return;this.saved=window.pmsSubmit({{ Js::from(route('personnel.update', $person['id'])) }}, {...this.form, employment_status: {{ Js::from($person['employment_status']) }}});this.isDirty=false;},
    };
}
</script>
@endsection
