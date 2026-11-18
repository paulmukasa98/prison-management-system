@extends('layouts.app')

@section('title', 'Discharge Records')

@section('content')

<script>
    window.__DISCHARGED__ = @json($inmates);
</script>

<div x-data="dischargeDirectory()" x-init="init()">

<x-page-heading
    title="Discharge Records"
    description="Searchable directory of inmates who have been discharged or released."
    :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Inmates',   'route' => 'inmates.index'],
        ['label' => 'Discharge Records'],
    ]"
>
    @permission('inmates.read')
<x-button.secondary href="{{ route('inmates.index') }}">← Inmate Directory</x-button.secondary>
@endpermission
</x-page-heading>

{{-- Filters --}}
<x-card class="mb-4">
    <div class="flex flex-wrap gap-3">
        <div class="relative flex-1 min-w-48">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3" aria-hidden="true">
                <svg class="h-4 w-4 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="search" x-model="search" @input="applyFilters()" placeholder="Search discharged inmates…"
                class="w-full pl-9 pr-4 py-2 text-sm text-[#0F172A] bg-white border border-[#E2E8F0] rounded-lg
                       placeholder-[#94A3B8] focus:outline-none focus:ring-2 focus:ring-brand"
                aria-label="Search discharged inmates">
        </div>
        <select x-model="filterFacility" @change="applyFilters()"
            class="px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand"
            aria-label="Filter by facility">
            <option value="">All Facilities</option>
            @foreach($facilities as $f)
                <option>{{ $f }}</option>
            @endforeach
        </select>
        <select x-model="filterReason" @change="applyFilters()"
            class="px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand"
            aria-label="Filter by discharge reason">
            <option value="">All Reasons</option>
            <option>Sentence Served</option>
            <option>Bail Granted</option>
            <option>Court Acquittal</option>
            <option>Presidential Pardon</option>
            <option>Appeal Upheld</option>
            <option>Civil Order Lifted</option>
        </select>
        <button @click="clearFilters()" x-show="search || filterFacility || filterReason"
            class="px-3 py-2 text-xs font-medium text-[#DC2626] bg-red-50 border border-red-200 rounded-lg hover:bg-red-100">
            Clear Filters
        </button>
        <span class="self-center text-sm text-[#64748B]">
            <span class="font-semibold text-[#0F172A]" x-text="filtered.length"></span> record(s)
        </span>
    </div>
</x-card>

{{-- Table --}}
<div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="w-full text-sm text-left" aria-label="Discharged inmates">
            <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0]">
                <tr>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide">Prisoner No.</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide">Name</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden sm:table-cell">Facility</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden md:table-cell">Admission Ref</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide">Discharge Date</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden lg:table-cell">Reason</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden lg:table-cell">Auth Ref</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F1F5F9]">
                <template x-for="inmate in filtered" :key="inmate.id">
                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                        <td class="px-4 py-3">
                            <a :href="`/inmates/${inmate.id}`" class="font-mono text-xs text-[#2563EB] font-semibold hover:underline" x-text="inmate.prisoner_number"></a>
                        </td>
                        <td class="px-4 py-3">
                            <a :href="`/inmates/${inmate.id}`" class="text-sm font-medium text-[#0F172A] hover:text-[#2563EB]" x-text="inmate.full_name"></a>
                        </td>
                        <td class="px-4 py-3 text-sm text-[#64748B] hidden sm:table-cell" x-text="inmate.facility"></td>
                        <td class="px-4 py-3 font-mono text-xs text-[#64748B] hidden md:table-cell" x-text="inmate.admission_ref"></td>
                        <td class="px-4 py-3 text-sm text-[#64748B]" x-text="inmate.actual_discharge || '—'"></td>
                        <td class="px-4 py-3 hidden lg:table-cell">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-50 text-purple-700" x-text="inmate.discharge_reason || '—'"></span>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-[#64748B] hidden lg:table-cell" x-text="inmate.discharge_auth_ref || '—'"></td>
                        <td class="px-4 py-3 text-right">
                            <a :href="`/inmates/${inmate.id}`" class="text-xs text-[#2563EB] hover:underline font-medium" :aria-label="`View profile for ${inmate.full_name}`">View</a>
                        </td>
                    </tr>
                </template>
                <tr x-show="filtered.length === 0">
                    <td colspan="8" class="px-6 py-12 text-center text-sm text-[#64748B]">No discharge records match your search criteria.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>



</div>

<script>
function dischargeDirectory() {
    return {
        all: [], filtered: [],
        search: '', filterFacility: '', filterReason: '',
        init() { this.all = window.__DISCHARGED__ || []; this.applyFilters(); },
        applyFilters() {
            const q = this.search.toLowerCase().trim();
            this.filtered = this.all.filter(i => {
                if (q) {
                    const hay = `${i.prisoner_number} ${i.full_name}`.toLowerCase();
                    if (!hay.includes(q)) return false;
                }
                if (this.filterFacility && i.facility !== this.filterFacility) return false;
                if (this.filterReason   && i.discharge_reason !== this.filterReason) return false;
                return true;
            });
        },
        clearFilters() { this.search = ''; this.filterFacility = ''; this.filterReason = ''; this.applyFilters(); },
    };
}
</script>

@endsection
