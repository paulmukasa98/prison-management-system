@extends('layouts.app')
@section('title', 'Facility Assignments')
@section('content')

<script>window.__ASSIGNMENTS__ = @json($assignments);</script>

<div x-data="assignmentDir()" x-init="init()">

<x-page-heading title="Facility Assignments"
    description="All personnel posting and assignment records."
    :breadcrumbs="[['label'=>'Dashboard','route'=>'dashboard'],['label'=>'Personnel','route'=>'personnel.index'],['label'=>'Assignments']]"
>
    @permission('personnel.manage')
<x-button.primary size="sm" href="{{ route('personnel.assignment.create') }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Assignment
    </x-button.primary>
@endpermission
</x-page-heading>

{{-- Summary --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4"><p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Total</p><p class="text-2xl font-bold text-[#0F172A] mt-1">{{ $counts['total'] }}</p></div>
    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4"><p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Active</p><p class="text-2xl font-bold text-green-600 mt-1">{{ $counts['active'] }}</p></div>
    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4"><p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Completed</p><p class="text-2xl font-bold text-[#0F172A] mt-1">{{ $counts['completed'] }}</p></div>
    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4"><p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Suspended</p><p class="text-2xl font-bold text-amber-600 mt-1">{{ $counts['suspended'] }}</p></div>
</div>

{{-- Filters --}}
<x-card class="mb-4">
    <div class="flex flex-wrap gap-3 items-center">
        <div class="relative flex-1 min-w-48">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3" aria-hidden="true"><svg class="h-4 w-4 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></div>
            <input type="search" x-model="search" @input="applyFilters()" placeholder="Search by ref, staff number, or name…" class="w-full pl-9 pr-4 py-2 text-sm border border-[#E2E8F0] rounded-lg placeholder-[#94A3B8] focus:outline-none focus:ring-2 focus:ring-brand">
        </div>
        <select x-model="filterFacility" @change="applyFilters()" class="px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand" aria-label="Filter by facility">
            <option value="">All Facilities</option>
            @foreach($facilities as $f)<option>{{ $f }}</option>@endforeach
        </select>
        <select x-model="filterStatus" @change="applyFilters()" class="px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand" aria-label="Filter by status">
            <option value="">All Statuses</option>
            @foreach($statuses as $s)<option>{{ $s }}</option>@endforeach
        </select>
        <button @click="clearFilters()" x-show="search||filterFacility||filterStatus" class="px-3 py-2 text-xs font-medium text-[#DC2626] bg-red-50 border border-red-200 rounded-lg hover:bg-red-100">Clear</button>
        <span class="text-sm text-[#64748B]"><span class="font-semibold text-[#0F172A]" x-text="filtered.length"></span> record(s)</span>
    </div>
</x-card>

<div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="w-full text-sm text-left" aria-label="Assignment directory">
            <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0]">
                <tr>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide">Ref</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden sm:table-cell">Staff No.</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide">Name</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden md:table-cell">Facility</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden lg:table-cell">Role</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden lg:table-cell">Start</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden lg:table-cell">End</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F1F5F9]">
                <template x-for="a in paginated" :key="a.id">
                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                        <td class="px-4 py-3 font-mono text-xs text-[#2563EB] font-semibold" x-text="a.assignment_ref"></td>
                        <td class="px-4 py-3 font-mono text-xs text-[#64748B] hidden sm:table-cell" x-text="a.staff_number"></td>
                        <td class="px-4 py-3 text-sm font-medium text-[#0F172A]" x-text="a.personnel_name"></td>
                        <td class="px-4 py-3 text-sm text-[#64748B] hidden md:table-cell" x-text="a.facility"></td>
                        <td class="px-4 py-3 text-sm text-[#64748B] hidden lg:table-cell" x-text="a.role"></td>
                        <td class="px-4 py-3 text-sm text-[#64748B] hidden lg:table-cell" x-text="a.start_date"></td>
                        <td class="px-4 py-3 text-sm text-[#64748B] hidden lg:table-cell" x-text="a.end_date||'Present'"></td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                :class="{'badge-active':a.status==='Active','badge-released':a.status==='Completed','badge-warning':a.status==='Suspended','badge-inactive':a.status==='Cancelled'}"
                                x-text="a.status"></span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a :href="`/personnel/${a.personnel_id}`" class="text-xs text-[#2563EB] hover:underline font-medium">View</a>
                        </td>
                    </tr>
                </template>
                <tr x-show="filtered.length===0"><td colspan="9" class="px-6 py-10 text-center text-sm text-[#64748B]">No assignment records match your search.</td></tr>
            </tbody>
        </table>
    </div>
    <div class="flex items-center justify-between px-4 py-3 border-t border-[#E2E8F0]" x-show="filtered.length>0">
        <p class="text-xs text-[#64748B]"><span class="font-semibold" x-text="pageStart+1"></span>–<span class="font-semibold" x-text="Math.min(pageEnd,filtered.length)"></span> of <span class="font-semibold" x-text="filtered.length"></span></p>
        <div class="flex items-center gap-1">
            <button @click="prevPage()" :disabled="page===1" class="px-3 py-1.5 text-xs border border-[#E2E8F0] rounded-lg disabled:opacity-40 hover:bg-[#F8FAFC]">← Prev</button>
            <template x-for="p in totalPages" :key="p"><button @click="page=p;updatePaginated()" :class="p===page?'bg-brand text-white border-brand':'border-[#E2E8F0] hover:bg-[#F8FAFC]'" class="px-3 py-1.5 text-xs border rounded-lg min-w-[32px]" x-text="p"></button></template>
            <button @click="nextPage()" :disabled="page===totalPages" class="px-3 py-1.5 text-xs border border-[#E2E8F0] rounded-lg disabled:opacity-40 hover:bg-[#F8FAFC]">Next →</button>
        </div>
    </div>
</div>

</div>

<script>
function assignmentDir(){
    return{
        all:[],filtered:[],paginated:[],search:'',filterFacility:'',filterStatus:'',page:1,perPage:15,
        get pageStart(){return(this.page-1)*this.perPage;},
        get pageEnd(){return this.page*this.perPage;},
        get totalPages(){return Math.max(1,Math.ceil(this.filtered.length/this.perPage));},
        init(){this.all=window.__ASSIGNMENTS__||[];this.applyFilters();},
        applyFilters(){
            const q=this.search.toLowerCase().trim();
            this.filtered=this.all.filter(a=>{
                if(q){const h=`${a.assignment_ref} ${a.staff_number} ${a.personnel_name}`.toLowerCase();if(!h.includes(q))return false;}
                if(this.filterFacility&&a.facility!==this.filterFacility)return false;
                if(this.filterStatus&&a.status!==this.filterStatus)return false;
                return true;
            });
            this.page=1;this.updatePaginated();
        },
        updatePaginated(){this.paginated=this.filtered.slice(this.pageStart,this.pageEnd);},
        prevPage(){if(this.page>1){this.page--;this.updatePaginated();}},
        nextPage(){if(this.page<this.totalPages){this.page++;this.updatePaginated();}},
        clearFilters(){this.search='';this.filterFacility='';this.filterStatus='';this.applyFilters();},
    };
}
</script>
@endsection
