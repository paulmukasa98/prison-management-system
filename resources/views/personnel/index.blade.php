@extends('layouts.app')
@section('title', 'Personnel Directory')
@section('content')

<script>window.__PERSONNEL__ = @json($personnel);</script>

<div x-data="personnelDirectory()" x-init="init()">

<x-page-heading
    title="Personnel Directory"
    description="Manage prison service officers and administrative staff across all facilities."
    :breadcrumbs="[['label'=>'Dashboard','route'=>'dashboard'],['label'=>'Personnel','route'=>'personnel.index'],['label'=>'All Personnel']]"
>
    @permission('personnel.manage')
<x-button.primary size="sm" href="{{ route('personnel.create') }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Register Personnel
    </x-button.primary>
@endpermission
</x-page-heading>

{{-- Summary Cards --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4">
        <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Total Personnel</p>
        <p class="text-2xl font-bold text-[#0F172A] tabular-nums mt-1">{{ $counts['total'] }}</p>
        <p class="text-xs text-[#64748B] mt-0.5">All records</p>
    </div>
    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4">
        <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Active</p>
        <p class="text-2xl font-bold text-green-600 tabular-nums mt-1">{{ $counts['active'] }}</p>
        <p class="text-xs text-[#64748B] mt-0.5">Currently serving</p>
    </div>
    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4">
        <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Assigned</p>
        <p class="text-2xl font-bold text-[#0F172A] tabular-nums mt-1">{{ $counts['assigned'] }}</p>
        <p class="text-xs text-[#64748B] mt-0.5">At a facility</p>
    </div>
    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4">
        <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Unassigned</p>
        <p class="text-2xl font-bold text-amber-600 tabular-nums mt-1">{{ $counts['unassigned'] }}</p>
        <p class="text-xs text-[#64748B] mt-0.5">HQ / unposted</p>
    </div>
</div>

{{-- Search & Filters --}}
<x-card class="mb-4">
    <div class="flex flex-col gap-3">
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
            <div class="relative flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3" aria-hidden="true">
                    <svg class="h-4 w-4 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="search" x-model="search" @input="applyFilters()"
                    placeholder="Search by staff number, name, email, or rank…"
                    class="w-full pl-9 pr-4 py-2 text-sm text-[#0F172A] bg-white border border-[#E2E8F0] rounded-lg placeholder-[#94A3B8] focus:outline-none focus:ring-2 focus:ring-brand">
            </div>
            <span class="text-sm text-[#64748B] flex-shrink-0">
                <span class="font-semibold text-[#0F172A]" x-text="filtered.length"></span> of <span x-text="all.length"></span>
            </span>
        </div>
        <div class="flex flex-wrap gap-2">
            <select x-model="filterFacility" @change="applyFilters()" class="px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand" aria-label="Filter by facility">
                <option value="">All Facilities</option>
                @foreach($facilities as $f)<option>{{ $f }}</option>@endforeach
            </select>
            <select x-model="filterDept" @change="applyFilters()" class="px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand" aria-label="Filter by department">
                <option value="">All Departments</option>
                @foreach($departments as $d)<option>{{ $d }}</option>@endforeach
            </select>
            <select x-model="filterRank" @change="applyFilters()" class="px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand" aria-label="Filter by rank">
                <option value="">All Ranks</option>
                @foreach($ranks as $r)<option>{{ $r }}</option>@endforeach
            </select>
            <select x-model="filterStatus" @change="applyFilters()" class="px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand" aria-label="Filter by status">
                <option value="">All Statuses</option>
                @foreach($statuses as $s)<option>{{ $s }}</option>@endforeach
            </select>
            <select x-model="filterGender" @change="applyFilters()" class="px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand" aria-label="Filter by gender">
                <option value="">All Genders</option>
                <option>Male</option>
                <option>Female</option>
            </select>
            <button @click="clearFilters()" x-show="hasFilters" class="px-3 py-2 text-xs font-medium text-[#DC2626] bg-red-50 border border-red-200 rounded-lg hover:bg-red-100">Clear Filters</button>
        </div>
    </div>
</x-card>

{{-- Table --}}
<div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="w-full text-sm text-left" aria-label="Personnel directory">
            <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0]">
                <tr>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide cursor-pointer select-none" @click="sort('staff_number')">
                        <div class="flex items-center gap-1">Staff No. <span x-text="sortIcon('staff_number')"></span></div>
                    </th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide cursor-pointer select-none" @click="sort('last_name')">
                        <div class="flex items-center gap-1">Name <span x-text="sortIcon('last_name')"></span></div>
                    </th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden md:table-cell">Rank / Position</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden lg:table-cell">Department</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden lg:table-cell cursor-pointer select-none" @click="sort('facility')">
                        <div class="flex items-center gap-1">Facility <span x-text="sortIcon('facility')"></span></div>
                    </th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F1F5F9]">
                <template x-for="p in paginated" :key="p.id">
                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                        <td class="px-4 py-3">
                            <a :href="`/personnel/${p.id}`" class="font-mono text-xs text-[#2563EB] font-semibold hover:underline" x-text="p.staff_number"></a>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-[#DBEAFE] flex items-center justify-center flex-shrink-0" aria-hidden="true">
                                    <span class="text-xs font-bold text-[#2563EB]" x-text="p.initials"></span>
                                </div>
                                <div>
                                    <a :href="`/personnel/${p.id}`" class="text-sm font-medium text-[#0F172A] hover:text-[#2563EB]" x-text="p.full_name"></a>
                                    <p class="text-xs text-[#94A3B8]" x-text="p.gender"></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell text-sm text-[#64748B]" x-text="p.rank"></td>
                        <td class="px-4 py-3 hidden lg:table-cell text-sm text-[#64748B]" x-text="p.department"></td>
                        <td class="px-4 py-3 hidden lg:table-cell text-sm text-[#64748B]" x-text="p.facility || 'HQ / Unassigned'"></td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium"
                                :class="{
                                    'badge-active':   p.employment_status==='Active',
                                    'badge-warning':  p.employment_status==='On Leave',
                                    'badge-danger':   p.employment_status==='Suspended',
                                    'badge-inactive': p.employment_status==='Retired'||p.employment_status==='Terminated',
                                }" x-text="p.employment_status">
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a :href="`/personnel/${p.id}`" class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-[#2563EB] hover:bg-[#DBEAFE] rounded-lg">View</a>
                                @permission('personnel.manage')
<template x-if="p.can_manage"><a :href="`/personnel/${p.id}/edit`" class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-[#64748B] hover:bg-[#F1F5F9] rounded-lg">Edit</a></template>
@endpermission
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="filtered.length === 0">
                    <td colspan="7" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 bg-[#F1F5F9] rounded-full flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <p class="text-sm font-semibold text-[#0F172A] mb-1">No personnel found</p>
                            <button @click="clearFilters()" class="text-xs text-[#2563EB] hover:underline">Clear all filters</button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    {{-- Pagination --}}
    <div class="flex items-center justify-between px-4 py-3 border-t border-[#E2E8F0]" x-show="filtered.length > 0">
        <p class="text-xs text-[#64748B]">
            <span class="font-semibold text-[#0F172A]" x-text="pageStart+1"></span>–<span class="font-semibold text-[#0F172A]" x-text="Math.min(pageEnd,filtered.length)"></span>
            of <span class="font-semibold text-[#0F172A]" x-text="filtered.length"></span>
        </p>
        <div class="flex items-center gap-1">
            <button @click="prevPage()" :disabled="page===1" class="px-3 py-1.5 text-xs border border-[#E2E8F0] rounded-lg disabled:opacity-40 hover:bg-[#F8FAFC]">← Prev</button>
            <template x-for="p in totalPages" :key="p">
                <button @click="page=p;updatePaginated()" :class="p===page?'bg-brand text-white border-brand':'border-[#E2E8F0] hover:bg-[#F8FAFC]'" class="px-3 py-1.5 text-xs border rounded-lg min-w-[32px]" x-text="p"></button>
            </template>
            <button @click="nextPage()" :disabled="page===totalPages" class="px-3 py-1.5 text-xs border border-[#E2E8F0] rounded-lg disabled:opacity-40 hover:bg-[#F8FAFC]">Next →</button>
        </div>
    </div>
</div>

</div>

<script>
function personnelDirectory() {
    return {
        all:[], filtered:[], paginated:[],
        search:'', filterFacility:'', filterDept:'', filterRank:'', filterStatus:'', filterGender:'',
        sortField:'staff_number', sortDir:'asc', page:1, perPage:12,
        get pageStart(){return(this.page-1)*this.perPage;},
        get pageEnd(){return this.page*this.perPage;},
        get totalPages(){return Math.max(1,Math.ceil(this.filtered.length/this.perPage));},
        get hasFilters(){return this.search||this.filterFacility||this.filterDept||this.filterRank||this.filterStatus||this.filterGender;},
        init(){this.all=window.__PERSONNEL__||[];this.applyFilters();},
        applyFilters(){
            const q=this.search.toLowerCase().trim();
            this.filtered=this.all.filter(p=>{
                if(q){const h=`${p.staff_number} ${p.full_name} ${p.email||''} ${p.rank}`.toLowerCase();if(!h.includes(q))return false;}
                if(this.filterFacility&&(p.facility||'')!==this.filterFacility)return false;
                if(this.filterDept&&p.department!==this.filterDept)return false;
                if(this.filterRank&&p.rank!==this.filterRank)return false;
                if(this.filterStatus&&p.employment_status!==this.filterStatus)return false;
                if(this.filterGender&&p.gender!==this.filterGender)return false;
                return true;
            });
            this.sortData();this.page=1;this.updatePaginated();
        },
        sort(f){if(this.sortField===f){this.sortDir=this.sortDir==='asc'?'desc':'asc';}else{this.sortField=f;this.sortDir='asc';}this.sortData();this.updatePaginated();},
        sortData(){const d=this.sortDir==='asc'?1:-1;this.filtered.sort((a,b)=>{const av=(a[this.sortField]||'').toString().toLowerCase();const bv=(b[this.sortField]||'').toString().toLowerCase();return av<bv?-d:av>bv?d:0;});},
        sortIcon(f){if(this.sortField!==f)return'↕';return this.sortDir==='asc'?'↑':'↓';},
        updatePaginated(){this.paginated=this.filtered.slice(this.pageStart,this.pageEnd);},
        prevPage(){if(this.page>1){this.page--;this.updatePaginated();}},
        nextPage(){if(this.page<this.totalPages){this.page++;this.updatePaginated();}},
        clearFilters(){this.search='';this.filterFacility='';this.filterDept='';this.filterRank='';this.filterStatus='';this.filterGender='';this.applyFilters();},
    };
}
</script>
@endsection
