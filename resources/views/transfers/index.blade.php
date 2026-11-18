@extends('layouts.app')

@section('title', 'Inmate Transfers')

@section('content')

<script>
    window.__TRANSFERS__  = @json($transfers);
    window.__TRF_STATUSES__ = @json($statuses);
    window.__TRF_FACILITIES__ = @json($facilities);
</script>

<div x-data="transferDirectory()" x-init="init()">

<x-page-heading
    title="Inmate Transfers"
    description="Manage and monitor inmate movement between prison facilities."
    :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Transfers', 'route' => 'transfers.index'],
        ['label' => 'All Transfers'],
    ]"
>
    @permissionAny(['transfers.request', 'transfers.review', 'transfers.authorize', 'transfers.dispatch', 'transfers.receive'])
<x-button.secondary size="sm" href="{{ route('transfers.history') }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Transfer History
    </x-button.secondary>
@endpermissionAny
    @permission('transfers.request')
<x-button.primary size="sm" href="{{ route('transfers.create') }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Transfer Request
    </x-button.primary>
@endpermission
</x-page-heading>

{{-- ===================================================================
     SUMMARY CARDS
     =================================================================== --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4">
        <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Total Requests</p>
        <p class="text-2xl font-bold text-[#0F172A] tabular-nums mt-1">{{ $counts['total'] ?? 0 }}</p>
        <p class="text-xs text-[#64748B] mt-0.5">All transfer records</p>
    </div>
    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4">
        <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Pending Approval</p>
        <p class="text-2xl font-bold text-amber-600 tabular-nums mt-1">{{ $counts['pending'] ?? 0 }}</p>
        <p class="text-xs text-[#64748B] mt-0.5">Awaiting review</p>
    </div>
    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4">
        <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">In Transit</p>
        <p class="text-2xl font-bold text-purple-600 tabular-nums mt-1">{{ $counts['in_transit'] ?? 0 }}</p>
        <p class="text-xs text-[#64748B] mt-0.5">Currently moving</p>
    </div>
    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4">
        <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Completed</p>
        <p class="text-2xl font-bold text-green-600 tabular-nums mt-1">{{ $counts['completed'] ?? 0 }}</p>
        <p class="text-xs text-[#64748B] mt-0.5">Successfully closed</p>
    </div>
</div>

{{-- ===================================================================
     SEARCH & FILTERS
     =================================================================== --}}
<x-card class="mb-4">
    <div class="flex flex-col gap-3">
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
            <div class="relative flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3" aria-hidden="true">
                    <svg class="h-4 w-4 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="search" x-model="search" @input="applyFilters()"
                    placeholder="Search by reference, prisoner number, or name…"
                    class="w-full pl-9 pr-4 py-2 text-sm text-[#0F172A] bg-white border border-[#E2E8F0] rounded-lg
                           placeholder-[#94A3B8] focus:outline-none focus:ring-2 focus:ring-brand"
                    aria-label="Search transfers">
            </div>
            <span class="text-sm text-[#64748B] flex-shrink-0">
                <span class="font-semibold text-[#0F172A]" x-text="filtered.length"></span> of
                <span x-text="all.length"></span> records
            </span>
        </div>
        <div class="flex flex-wrap gap-2">
            <select x-model="filterStatus" @change="applyFilters()"
                class="px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand"
                aria-label="Filter by status">
                <option value="">All Statuses</option>
                @foreach($statuses as $s)
                    <option>{{ $s }}</option>
                @endforeach
            </select>
            <select x-model="filterOrigin" @change="applyFilters()"
                class="px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand"
                aria-label="Filter by origin facility">
                <option value="">All Origins</option>
                @foreach($facilities as $f)
                    <option>{{ $f }}</option>
                @endforeach
            </select>
            <select x-model="filterDestination" @change="applyFilters()"
                class="px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand"
                aria-label="Filter by destination facility">
                <option value="">All Destinations</option>
                @foreach($facilities as $f)
                    <option>{{ $f }}</option>
                @endforeach
            </select>
            <select x-model="perPage" @change="applyFilters()"
                class="px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand"
                aria-label="Records per page">
                <option value="10">10 / page</option>
                <option value="25">25 / page</option>
            </select>
            <button @click="clearFilters()" x-show="hasFilters"
                class="px-3 py-2 text-xs font-medium text-[#DC2626] bg-red-50 border border-red-200 rounded-lg hover:bg-red-100">
                Clear Filters
            </button>
        </div>
    </div>
</x-card>

{{-- ===================================================================
     TABLE
     =================================================================== --}}
<div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="w-full text-sm text-left" aria-label="Transfer directory">
            <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0]">
                <tr>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide cursor-pointer select-none" @click="sort('transfer_ref')">
                        <div class="flex items-center gap-1">Ref <span x-text="sortIcon('transfer_ref')" class="text-[#94A3B8]"></span></div>
                    </th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden sm:table-cell">Prisoner No.</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide cursor-pointer select-none" @click="sort('inmate_name')">
                        <div class="flex items-center gap-1">Inmate <span x-text="sortIcon('inmate_name')" class="text-[#94A3B8]"></span></div>
                    </th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden lg:table-cell">Movement</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden md:table-cell cursor-pointer select-none" @click="sort('request_date')">
                        <div class="flex items-center gap-1">Requested <span x-text="sortIcon('request_date')" class="text-[#94A3B8]"></span></div>
                    </th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F1F5F9]">
                <template x-for="t in paginated" :key="t.id">
                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                        <td class="px-4 py-3">
                            <a :href="`/transfers/${t.id}`" class="font-mono text-xs text-[#2563EB] font-semibold hover:underline" x-text="t.transfer_ref"></a>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell">
                            <span class="font-mono text-xs text-[#64748B]" x-text="t.prisoner_number"></span>
                        </td>
                        <td class="px-4 py-3">
                            <div>
                                <a :href="`/transfers/${t.id}`" class="text-sm font-medium text-[#0F172A] hover:text-[#2563EB]" x-text="t.inmate_name"></a>
                                <p class="text-xs text-[#94A3B8]" x-text="t.classification"></p>
                            </div>
                        </td>
                        <td class="px-4 py-3 hidden lg:table-cell">
                            <div class="flex items-center gap-1.5 text-xs">
                                <span class="text-[#64748B] truncate max-w-28" x-text="t.origin_facility"></span>
                                <svg class="w-3 h-3 text-[#2563EB] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                                <span class="text-[#64748B] truncate max-w-28" x-text="t.destination_facility"></span>
                            </div>
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell text-sm text-[#64748B]" x-text="t.request_date"></td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium"
                                :class="{
                                    'bg-slate-100 text-slate-600':   t.status === 'Draft' || t.status === 'Cancelled',
                                    'bg-amber-100 text-amber-700':   t.status === 'Pending Approval' || t.status === 'Arrival Confirmation Pending',
                                    'bg-blue-100 text-blue-700':     t.status === 'Approved',
                                    'bg-red-100 text-red-700':       t.status === 'Rejected',
                                    'bg-orange-100 text-orange-700': t.status === 'Awaiting Clarification',
                                    'bg-indigo-100 text-indigo-700': t.status === 'Ready for Dispatch',
                                    'bg-purple-100 text-purple-700': t.status === 'In Transit',
                                    'bg-green-100 text-green-700':   t.status === 'Completed',
                                }"
                                x-text="t.status">
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a :href="`/transfers/${t.id}`"
                                   class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-[#2563EB] hover:bg-[#DBEAFE] rounded-lg transition-colors"
                                   :aria-label="`View transfer ${t.transfer_ref}`">View</a>
                                <template x-if="t.status === 'Pending Approval' || t.status === 'Awaiting Clarification'">
                                    @permissionAny(['transfers.review', 'transfers.authorize'])
<template x-if="t.can_review"><a :href="`/transfers/${t.id}/review`"
                                       class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-amber-700 hover:bg-amber-50 rounded-lg transition-colors">Review</a></template>
@endpermissionAny
                                </template>
                                <template x-if="t.status === 'Ready for Dispatch'">
                                    @permission('transfers.dispatch')
<template x-if="t.can_dispatch"><a :href="`/transfers/${t.id}/dispatch`"
                                       class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-50 rounded-lg transition-colors">Dispatch</a></template>
@endpermission
                                </template>
                                <template x-if="t.status === 'Arrival Confirmation Pending'">
                                    @permission('transfers.receive')
<template x-if="t.can_receive"><a :href="`/transfers/${t.id}/receive`"
                                       class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-purple-700 hover:bg-purple-50 rounded-lg transition-colors">Receive</a></template>
@endpermission
                                </template>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="filtered.length === 0">
                    <td colspan="7" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 bg-[#F1F5F9] rounded-full flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-[#0F172A] mb-1">No transfers found</p>
                            <p class="text-xs text-[#64748B] mb-3">No transfer records match the current criteria.</p>
                            <button @click="clearFilters()" class="text-xs text-[#2563EB] hover:underline font-medium">Clear all filters</button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="flex items-center justify-between px-4 py-3 border-t border-[#E2E8F0]" x-show="filtered.length > 0">
        <p class="text-xs text-[#64748B]">
            Showing <span class="font-semibold text-[#0F172A]" x-text="pageStart + 1"></span>–<span class="font-semibold text-[#0F172A]" x-text="Math.min(pageEnd, filtered.length)"></span>
            of <span class="font-semibold text-[#0F172A]" x-text="filtered.length"></span>
        </p>
        <div class="flex items-center gap-1">
            <button @click="prevPage()" :disabled="page === 1"
                class="px-3 py-1.5 text-xs border border-[#E2E8F0] rounded-lg disabled:opacity-40 disabled:cursor-not-allowed hover:bg-[#F8FAFC]">← Prev</button>
            <template x-for="p in totalPages" :key="p">
                <button @click="page = p; updatePaginated()"
                    :class="p === page ? 'bg-brand text-white border-brand' : 'border-[#E2E8F0] hover:bg-[#F8FAFC]'"
                    class="px-3 py-1.5 text-xs border rounded-lg min-w-[32px]" x-text="p"></button>
            </template>
            <button @click="nextPage()" :disabled="page === totalPages"
                class="px-3 py-1.5 text-xs border border-[#E2E8F0] rounded-lg disabled:opacity-40 disabled:cursor-not-allowed hover:bg-[#F8FAFC]">Next →</button>
        </div>
    </div>
</div>



</div>

<script>
function transferDirectory() {
    return {
        all: [], filtered: [], paginated: [],
        search: '', filterStatus: '', filterOrigin: '', filterDestination: '',
        sortField: 'request_date', sortDir: 'desc',
        page: 1, perPage: 10,

        get pageStart()  { return (this.page - 1) * parseInt(this.perPage); },
        get pageEnd()    { return this.page * parseInt(this.perPage); },
        get totalPages() { return Math.max(1, Math.ceil(this.filtered.length / parseInt(this.perPage))); },
        get hasFilters() { return this.search || this.filterStatus || this.filterOrigin || this.filterDestination; },

        init() { this.all = window.__TRANSFERS__ || []; this.applyFilters(); },

        applyFilters() {
            const q = this.search.toLowerCase().trim();
            this.filtered = this.all.filter(t => {
                if (q) {
                    const hay = `${t.transfer_ref} ${t.prisoner_number} ${t.inmate_name}`.toLowerCase();
                    if (!hay.includes(q)) return false;
                }
                if (this.filterStatus      && t.status               !== this.filterStatus)      return false;
                if (this.filterOrigin      && t.origin_facility      !== this.filterOrigin)      return false;
                if (this.filterDestination && t.destination_facility !== this.filterDestination) return false;
                return true;
            });
            this.sortData();
            this.page = 1;
            this.updatePaginated();
        },

        sort(field) {
            if (this.sortField === field) {
                this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortField = field; this.sortDir = 'asc';
            }
            this.sortData(); this.updatePaginated();
        },

        sortData() {
            const dir = this.sortDir === 'asc' ? 1 : -1;
            this.filtered.sort((a, b) => {
                const av = (a[this.sortField] || '').toString().toLowerCase();
                const bv = (b[this.sortField] || '').toString().toLowerCase();
                return av < bv ? -dir : av > bv ? dir : 0;
            });
        },

        sortIcon(f) { if (this.sortField !== f) return '↕'; return this.sortDir === 'asc' ? '↑' : '↓'; },
        updatePaginated() { this.paginated = this.filtered.slice(this.pageStart, this.pageEnd); },
        prevPage() { if (this.page > 1) { this.page--; this.updatePaginated(); } },
        nextPage() { if (this.page < this.totalPages) { this.page++; this.updatePaginated(); } },
        clearFilters() { this.search = ''; this.filterStatus = ''; this.filterOrigin = ''; this.filterDestination = ''; this.applyFilters(); },
    };
}
</script>

@endsection
