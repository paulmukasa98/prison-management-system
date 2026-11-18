@extends('layouts.app')
@section('title', 'Prison Facilities')
@section('content')

<script>window.__FACILITIES__ = @json($facilities);</script>

<div x-data="facilityDir()" x-init="init()">

<x-page-heading title="Prison Facilities"
    description="Directory of Uganda Prisons Service facility records."
    :breadcrumbs="[['label'=>'Dashboard','route'=>'dashboard'],['label'=>'Prison Facilities','route'=>'facilities.index']]"
>
    @permission('facilities.read')
<x-button.secondary size="sm" href="{{ route('facilities.capacity') }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        Capacity Overview
    </x-button.secondary>
@endpermission
    @nationalPermission('facilities.manage')
<x-button.primary size="sm" href="{{ route('facilities.create') }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Register Facility
    </x-button.primary>
@endnationalPermission
</x-page-heading>

{{-- Summary cards --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4"><p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Total Facilities</p><p class="text-2xl font-bold text-[#0F172A] mt-1">{{ $counts['total'] }}</p></div>
    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4"><p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Active</p><p class="text-2xl font-bold text-green-600 mt-1">{{ $counts['active'] }}</p></div>
    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4"><p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Total Capacity</p><p class="text-2xl font-bold text-[#0F172A] mt-1">{{ number_format($counts['capacity']) }}</p><p class="text-xs text-[#94A3B8] mt-0.5">Recorded capacity figures</p></div>
    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4"><p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Total Population</p><p class="text-2xl font-bold text-[#0F172A] mt-1">{{ number_format($counts['population']) }}</p></div>
</div>

{{-- Filters --}}
<x-card class="mb-4">
    <div class="flex flex-wrap gap-3 items-center">
        <div class="relative flex-1 min-w-48">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3" aria-hidden="true"><svg class="h-4 w-4 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></div>
            <input type="search" x-model="search" @input="applyFilters()" placeholder="Search by name, code, region, district…" class="w-full pl-9 pr-4 py-2 text-sm border border-[#E2E8F0] rounded-lg placeholder-[#94A3B8] focus:outline-none focus:ring-2 focus:ring-brand">
        </div>
        <select x-model="filterRegion" @change="applyFilters()" class="px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand" aria-label="Filter by region">
            <option value="">All Regions</option>
            @foreach($regions as $r)<option>{{ $r }}</option>@endforeach
        </select>
        <select x-model="filterType" @change="applyFilters()" class="px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand" aria-label="Filter by type">
            <option value="">All Types</option>
            @foreach($types as $t)<option>{{ $t }}</option>@endforeach
        </select>
        <select x-model="filterStatus" @change="applyFilters()" class="px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand" aria-label="Filter by status">
            <option value="">All Statuses</option>
            @foreach($statuses as $s)<option>{{ $s }}</option>@endforeach
        </select>
        <button @click="clearFilters()" x-show="hasFilters" class="px-3 py-2 text-xs font-medium text-[#DC2626] bg-red-50 border border-red-200 rounded-lg hover:bg-red-100">Clear</button>
        <span class="text-sm text-[#64748B]"><span class="font-semibold text-[#0F172A]" x-text="filtered.length"></span> of <span x-text="all.length"></span></span>
    </div>
</x-card>

{{-- Table --}}
<div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="w-full text-sm text-left" aria-label="Facility directory">
            <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0]">
                <tr>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide cursor-pointer select-none" @click="sort('code')"><div class="flex items-center gap-1">Code <span x-text="sortIcon('code')"></span></div></th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide cursor-pointer select-none" @click="sort('name')"><div class="flex items-center gap-1">Name <span x-text="sortIcon('name')"></span></div></th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden sm:table-cell">Region</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden md:table-cell">Type</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden lg:table-cell">Capacity</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden lg:table-cell">Population</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden lg:table-cell">Occupancy</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F1F5F9]">
                <template x-for="f in paginated" :key="f.id">
                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                        <td class="px-4 py-3 font-mono text-xs text-[#2563EB] font-semibold" x-text="f.code"></td>
                        <td class="px-4 py-3">
                            <a :href="`/facilities/${f.id}`" class="text-sm font-medium text-[#0F172A] hover:text-[#2563EB]" x-text="f.name"></a>
                        </td>
                        <td class="px-4 py-3 text-sm text-[#64748B] hidden sm:table-cell" x-text="`${f.region} / ${f.district}`"></td>
                        <td class="px-4 py-3 text-sm text-[#64748B] hidden md:table-cell" x-text="f.type"></td>
                        <td class="px-4 py-3 text-sm text-[#64748B] hidden lg:table-cell" x-text="f.capacity === null ? 'Unknown' : f.capacity.toLocaleString()"></td>
                        <td class="px-4 py-3 text-sm text-[#64748B] hidden lg:table-cell" x-text="f.current_population.toLocaleString()"></td>
                        <td class="px-4 py-3 hidden lg:table-cell">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-[#F1F5F9] rounded-full h-1.5" title="Occupancy">
                                    <div class="h-1.5 rounded-full"
                                        :class="f.occupancy_pct>=100?'bg-red-600':f.occupancy_pct>=90?'bg-red-500':f.occupancy_pct>=75?'bg-amber-500':'bg-[#2563EB]'"
                                        :style="`width: ${Math.min(f.occupancy_pct,100)}%`"></div>
                                </div>
                                <span class="text-xs font-medium tabular-nums"
                                    :class="f.occupancy_pct>=90?'text-red-600':f.occupancy_pct>=75?'text-amber-600':'text-[#64748B]'"
                                    x-text="`${f.occupancy_pct}%`"></span>
                                <span x-show="f.occupancy_pct>=100" class="text-xs font-bold text-red-600" title="Over capacity" aria-label="Over capacity">⚠</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                :class="{'badge-active':f.status==='Active','badge-inactive':f.status==='Inactive'||f.status==='Decommissioned','badge-warning':f.status==='Under Renovation'}"
                                x-text="f.status"></span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a :href="`/facilities/${f.id}`" class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-[#2563EB] hover:bg-[#DBEAFE] rounded-lg">View</a>
                                @permission('facilities.manage')
<template x-if="f.can_manage"><a :href="`/facilities/${f.id}/edit`" class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-[#64748B] hover:bg-[#F1F5F9] rounded-lg">Edit</a></template>
@endpermission
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="filtered.length===0"><td colspan="9" class="px-6 py-16 text-center"><p class="text-sm font-semibold text-[#0F172A] mb-1">No facilities found</p><button @click="clearFilters()" class="text-xs text-[#2563EB] hover:underline">Clear filters</button></td></tr>
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
function facilityDir(){
    return{
        all:[],filtered:[],paginated:[],
        search:'',filterRegion:'',filterType:'',filterStatus:'',
        sortField:'code',sortDir:'asc',page:1,perPage:10,
        get pageStart(){return(this.page-1)*this.perPage;},
        get pageEnd(){return this.page*this.perPage;},
        get totalPages(){return Math.max(1,Math.ceil(this.filtered.length/this.perPage));},
        get hasFilters(){return this.search||this.filterRegion||this.filterType||this.filterStatus;},
        init(){this.all=window.__FACILITIES__||[];this.applyFilters();},
        applyFilters(){
            const q=this.search.toLowerCase().trim();
            this.filtered=this.all.filter(f=>{
                if(q){const h=`${f.code} ${f.name} ${f.region} ${f.district}`.toLowerCase();if(!h.includes(q))return false;}
                if(this.filterRegion&&f.region!==this.filterRegion)return false;
                if(this.filterType&&f.type!==this.filterType)return false;
                if(this.filterStatus&&f.status!==this.filterStatus)return false;
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
        clearFilters(){this.search='';this.filterRegion='';this.filterType='';this.filterStatus='';this.applyFilters();},
    };
}
</script>
@endsection
