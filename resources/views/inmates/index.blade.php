@extends('layouts.app')

@section('title', 'Inmate Directory')

@section('content')

{{-- Pass PHP data to Alpine --}}
<script>
    window.__INMATES__ = @json($inmates);
    window.__FACILITIES__ = @json($facilities);
</script>

<div x-data="inmateDirectory()" x-init="init()">

<x-page-heading
    title="Inmate Directory"
    description="Manage and review inmate records across authorized prison facilities."
    :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Inmates',   'route' => 'inmates.index'],
        ['label' => 'Inmate Directory'],
    ]"
>
    @if(auth()->user()->hasPermission('reports.export') && config('reporting.csv_exports_enabled'))
    <x-button.secondary size="sm" @click="exportRecords()">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Export
    </x-button.secondary>
    @endif
    @permission('inmates.admit')
<x-button.primary size="sm" href="{{ route('inmates.create') }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Admission
    </x-button.primary>
@endpermission
</x-page-heading>

{{-- ===================================================================
     SUMMARY CARDS
     =================================================================== --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4">
        <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Total Records</p>
        <p class="text-2xl font-bold text-[#0F172A] tabular-nums mt-1">{{ $counts['total'] ?? 0 }}</p>
        <p class="text-xs text-[#64748B] mt-0.5">All admission records</p>
    </div>
    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4">
        <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">In Custody</p>
        <p class="text-2xl font-bold text-[#0F172A] tabular-nums mt-1">{{ $counts['active'] ?? $counts['in_custody'] ?? 0 }}</p>
        <p class="text-xs text-[#64748B] mt-0.5">Currently detained</p>
    </div>
    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4">
        <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">On Remand</p>
        <p class="text-2xl font-bold text-[#0F172A] tabular-nums mt-1">{{ $counts['remand'] ?? $counts['remanded'] ?? 0 }}</p>
        <p class="text-xs text-[#64748B] mt-0.5">Pending trial</p>
    </div>
    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4">
        <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Convicted</p>
        <p class="text-2xl font-bold text-[#0F172A] tabular-nums mt-1">{{ $counts['convicted'] ?? 0 }}</p>
        <p class="text-xs text-[#64748B] mt-0.5">Serving sentence</p>
    </div>
</div>

{{-- ===================================================================
     SEARCH & FILTERS
     =================================================================== --}}
<x-card class="mb-4">
    <div class="flex flex-col gap-3">
        {{-- Row 1: search + result count --}}
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
            <div class="relative flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3" aria-hidden="true">
                    <svg class="h-4 w-4 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input
                    type="search"
                    x-model="search"
                    @input="applyFilters()"
                    placeholder="Search by prisoner number, name…"
                    class="w-full pl-9 pr-4 py-2 text-sm text-[#0F172A] bg-white border border-[#E2E8F0] rounded-lg
                           placeholder-[#94A3B8] focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand"
                    aria-label="Search inmates"
                >
            </div>
            <span class="text-sm text-[#64748B] flex-shrink-0">
                Showing <span class="font-semibold text-[#0F172A]" x-text="filtered.length"></span>
                of <span class="font-semibold" x-text="all.length"></span> records
            </span>
        </div>
        {{-- Row 2: filters --}}
        <div class="flex flex-wrap gap-2">
            <select x-model="filterFacility" @change="applyFilters()"
                class="px-3 py-2 text-sm text-[#0F172A] bg-white border border-[#E2E8F0] rounded-lg
                       focus:outline-none focus:ring-2 focus:ring-brand"
                aria-label="Filter by facility">
                <option value="">All Facilities</option>
                @foreach($facilities as $f)
                    <option>{{ $f }}</option>
                @endforeach
            </select>

            <select x-model="filterClassification" @change="applyFilters()"
                class="px-3 py-2 text-sm text-[#0F172A] bg-white border border-[#E2E8F0] rounded-lg
                       focus:outline-none focus:ring-2 focus:ring-brand"
                aria-label="Filter by custody classification">
                <option value="">All Classifications</option>
                <option>Convicted</option>
                <option>Remand</option>
                <option>Civil Debtor</option>
            </select>

            <select x-model="filterStatus" @change="applyFilters()"
                class="px-3 py-2 text-sm text-[#0F172A] bg-white border border-[#E2E8F0] rounded-lg
                       focus:outline-none focus:ring-2 focus:ring-brand"
                aria-label="Filter by custody status">
                <option value="">All Status</option>
                <option>In Custody</option>
                <option>Remanded</option>
                <option>Released</option>
                <option>Transferred</option>
            </select>

            <select x-model="filterGender" @change="applyFilters()"
                class="px-3 py-2 text-sm text-[#0F172A] bg-white border border-[#E2E8F0] rounded-lg
                       focus:outline-none focus:ring-2 focus:ring-brand"
                aria-label="Filter by gender">
                <option value="">All Genders</option>
                <option>Male</option>
                <option>Female</option>
            </select>

            <select x-model="perPage" @change="applyFilters()"
                class="px-3 py-2 text-sm text-[#0F172A] bg-white border border-[#E2E8F0] rounded-lg
                       focus:outline-none focus:ring-2 focus:ring-brand"
                aria-label="Records per page">
                <option value="10">10 / page</option>
                <option value="20">20 / page</option>
                <option value="50">50 / page</option>
            </select>

            <button @click="clearFilters()"
                x-show="hasActiveFilters"
                class="px-3 py-2 text-sm font-medium text-[#DC2626] bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors"
                aria-label="Clear all filters">
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
        <table class="w-full text-sm text-left text-[#0F172A]" aria-label="Inmate directory">
            <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0]">
                <tr>
                    <th scope="col" class="px-4 py-3 cursor-pointer select-none" @click="sort('prisoner_number')">
                        <div class="flex items-center gap-1 text-xs font-semibold text-[#64748B] uppercase tracking-wide">
                            Prisoner No.
                            <span x-text="sortIcon('prisoner_number')" class="text-[#94A3B8]"></span>
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 cursor-pointer select-none" @click="sort('last_name')">
                        <div class="flex items-center gap-1 text-xs font-semibold text-[#64748B] uppercase tracking-wide">
                            Inmate Name
                            <span x-text="sortIcon('last_name')" class="text-[#94A3B8]"></span>
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden sm:table-cell">Gender</th>
                    <th scope="col" class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden md:table-cell">Classification</th>
                    <th scope="col" class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden lg:table-cell cursor-pointer select-none" @click="sort('facility')">
                        <div class="flex items-center gap-1">
                            Facility
                            <span x-text="sortIcon('facility')" class="text-[#94A3B8]"></span>
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden lg:table-cell cursor-pointer select-none" @click="sort('admission_date')">
                        <div class="flex items-center gap-1">
                            Admitted
                            <span x-text="sortIcon('admission_date')" class="text-[#94A3B8]"></span>
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide">Status</th>
                    <th scope="col" class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F1F5F9]">
                <template x-for="inmate in paginated" :key="inmate.id">
                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                        <td class="px-4 py-3">
                            <a :href="`/inmates/${inmate.id}`"
                               class="font-mono text-xs text-[#2563EB] font-semibold hover:underline"
                               x-text="inmate.prisoner_number"></a>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-[#DBEAFE] flex items-center justify-center flex-shrink-0" aria-hidden="true">
                                    <template x-if="inmate.photo"><img :src="inmate.photo" alt="" loading="lazy" class="w-8 h-8 rounded-full object-cover"></template>
                                    <span x-show="!inmate.photo" class="text-xs font-bold text-[#2563EB]" x-text="inmate.initials"></span>
                                </div>
                                <div>
                                    <a :href="`/inmates/${inmate.id}`"
                                       class="text-sm font-medium text-[#0F172A] hover:text-[#2563EB]"
                                       x-text="inmate.full_name"></a>
                                    <p class="text-xs text-[#94A3B8]" x-text="`Age ${inmate.age} · ${inmate.district}`"></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell">
                            <span class="text-sm text-[#64748B]" x-text="inmate.gender"></span>
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                :class="{
                                    'bg-red-50 text-red-700': inmate.classification === 'Convicted',
                                    'bg-amber-50 text-amber-700': inmate.classification === 'Remand',
                                    'bg-purple-50 text-purple-700': inmate.classification === 'Civil Debtor',
                                }"
                                x-text="inmate.classification">
                            </span>
                        </td>
                        <td class="px-4 py-3 hidden lg:table-cell">
                            <span class="text-sm text-[#64748B]" x-text="inmate.facility"></span>
                        </td>
                        <td class="px-4 py-3 hidden lg:table-cell">
                            <span class="text-sm text-[#64748B]" x-text="inmate.admission_date"></span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium"
                                :class="{
                                    'badge-active':   inmate.status === 'In Custody',
                                    'badge-pending':  inmate.status === 'Remanded',
                                    'badge-released': inmate.status === 'Released',
                                    'badge-warning':  inmate.status === 'Transferred',
                                    'badge-inactive': inmate.status === 'Deceased',
                                }"
                                x-text="inmate.status">
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a :href="`/inmates/${inmate.id}`"
                                   class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-[#2563EB] hover:bg-[#DBEAFE] rounded-lg transition-colors"
                                   :aria-label="`View profile for ${inmate.full_name}`">View</a>
                                @permission('inmates.edit')
<template x-if="inmate.can_edit"><a :href="`/inmates/${inmate.id}/edit`"
                                   class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-[#64748B] hover:bg-[#F1F5F9] rounded-lg transition-colors"
                                   :aria-label="`Edit details for ${inmate.full_name}`">Edit</a></template>
@endpermission
                            </div>
                        </td>
                    </tr>
                </template>

                {{-- Empty state --}}
                <tr x-show="filtered.length === 0">
                    <td colspan="8" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 bg-[#F1F5F9] rounded-full flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-[#0F172A] mb-1">No records found</p>
                            <p class="text-xs text-[#64748B] mb-3">No inmates match the current search and filter criteria.</p>
                            <button @click="clearFilters()"
                                class="text-xs text-[#2563EB] hover:underline font-medium">
                                Clear all filters
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="flex items-center justify-between px-4 py-3 border-t border-[#E2E8F0]" x-show="filtered.length > 0">
        <p class="text-xs text-[#64748B]">
            Showing
            <span class="font-semibold text-[#0F172A]" x-text="pageStart + 1"></span>–<span class="font-semibold text-[#0F172A]" x-text="Math.min(pageEnd, filtered.length)"></span>
            of <span class="font-semibold text-[#0F172A]" x-text="filtered.length"></span>
        </p>
        <div class="flex items-center gap-1">
            <button @click="prevPage()" :disabled="page === 1"
                class="px-3 py-1.5 text-xs border border-[#E2E8F0] rounded-lg disabled:opacity-40 disabled:cursor-not-allowed hover:bg-[#F8FAFC]"
                aria-label="Previous page">← Prev</button>
            <template x-for="p in totalPages" :key="p">
                <button @click="page = p; updatePaginated()"
                    :class="p === page ? 'bg-brand text-white border-brand' : 'border-[#E2E8F0] hover:bg-[#F8FAFC]'"
                    class="px-3 py-1.5 text-xs border rounded-lg min-w-[32px]"
                    :aria-current="p === page ? 'page' : 'false'"
                    :aria-label="`Page ${p}`"
                    x-text="p">
                </button>
            </template>
            <button @click="nextPage()" :disabled="page === totalPages"
                class="px-3 py-1.5 text-xs border border-[#E2E8F0] rounded-lg disabled:opacity-40 disabled:cursor-not-allowed hover:bg-[#F8FAFC]"
                aria-label="Next page">Next →</button>
        </div>
    </div>
</div>



</div>{{-- end x-data --}}

<script>
function inmateDirectory() {
    return {
        all: [],
        filtered: [],
        paginated: [],
        search: '',
        filterFacility: '',
        filterClassification: '',
        filterStatus: '',
        filterGender: '',
        sortField: 'prisoner_number',
        sortDir: 'asc',
        page: 1,
        perPage: 10,

        get pageStart() { return (this.page - 1) * parseInt(this.perPage); },
        get pageEnd()   { return this.page * parseInt(this.perPage); },
        get totalPages() {
            return Math.max(1, Math.ceil(this.filtered.length / parseInt(this.perPage)));
        },
        get hasActiveFilters() {
            return this.search || this.filterFacility || this.filterClassification || this.filterStatus || this.filterGender;
        },

        init() {
            this.all = window.__INMATES__ || [];
            this.applyFilters();
        },

        applyFilters() {
            const q = this.search.toLowerCase().trim();
            this.filtered = this.all.filter(i => {
                if (q) {
                    const hay = `${i.prisoner_number} ${i.full_name} ${i.first_name} ${i.last_name}`.toLowerCase();
                    if (!hay.includes(q)) return false;
                }
                if (this.filterFacility       && i.facility       !== this.filterFacility)       return false;
                if (this.filterClassification && i.classification !== this.filterClassification) return false;
                if (this.filterStatus         && i.status         !== this.filterStatus)         return false;
                if (this.filterGender         && i.gender         !== this.filterGender)         return false;
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
                this.sortField = field;
                this.sortDir   = 'asc';
            }
            this.sortData();
            this.updatePaginated();
        },

        sortData() {
            const dir = this.sortDir === 'asc' ? 1 : -1;
            this.filtered.sort((a, b) => {
                const av = (a[this.sortField] || '').toString().toLowerCase();
                const bv = (b[this.sortField] || '').toString().toLowerCase();
                return av < bv ? -dir : av > bv ? dir : 0;
            });
        },

        sortIcon(field) {
            if (this.sortField !== field) return '↕';
            return this.sortDir === 'asc' ? '↑' : '↓';
        },

        updatePaginated() {
            this.paginated = this.filtered.slice(this.pageStart, this.pageEnd);
        },

        prevPage() { if (this.page > 1) { this.page--; this.updatePaginated(); } },
        nextPage() { if (this.page < this.totalPages) { this.page++; this.updatePaginated(); } },

        clearFilters() {
            this.search = '';
            this.filterFacility = '';
            this.filterClassification = '';
            this.filterStatus = '';
            this.filterGender = '';
            this.applyFilters();
        },

        exportRecords() {
            const url = new URL(@json(route('inmates.export')));
            const filters = { search: this.search, facility: this.filterFacility, classification: this.filterClassification, status: this.filterStatus, gender: this.filterGender, sort: this.sortField, direction: this.sortDir };
            Object.entries(filters).forEach(([key, value]) => { if (value) url.searchParams.set(key, value); });
            window.location.assign(url.toString());
        },
    };
}
</script>

@endsection
