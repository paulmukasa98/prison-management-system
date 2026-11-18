@extends('layouts.app')

@section('title', 'Transfer History')

@section('content')

<script>
    window.__TRF_HISTORY__ = @json($transfers);
</script>

<div x-data="historyDirectory()" x-init="init()">

<x-page-heading
    title="Transfer History"
    description="Completed, rejected, and cancelled transfer records."
    :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Transfers', 'route' => 'transfers.index'],
        ['label' => 'Transfer History'],
    ]"
>
    @permissionAny(['transfers.request', 'transfers.review', 'transfers.authorize', 'transfers.dispatch', 'transfers.receive'])
<x-button.secondary href="{{ route('transfers.index') }}">← Active Transfers</x-button.secondary>
@endpermissionAny
</x-page-heading>

{{-- Filters --}}
<x-card class="mb-4">
    <div class="flex flex-wrap gap-3 items-center">
        <div class="relative flex-1 min-w-48">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3" aria-hidden="true">
                <svg class="h-4 w-4 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="search" x-model="search" @input="applyFilters()"
                placeholder="Search by reference, prisoner, or name…"
                class="w-full pl-9 pr-4 py-2 text-sm text-[#0F172A] bg-white border border-[#E2E8F0] rounded-lg
                       placeholder-[#94A3B8] focus:outline-none focus:ring-2 focus:ring-brand"
                aria-label="Search historical transfers">
        </div>
        <select x-model="filterStatus" @change="applyFilters()"
            class="px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand"
            aria-label="Filter by final status">
            <option value="">All Statuses</option>
            @foreach($statuses as $s)
                <option>{{ $s }}</option>
            @endforeach
        </select>
        <select x-model="filterFacility" @change="applyFilters()"
            class="px-3 py-2 text-sm border border-[#E2E8F0] rounded-lg focus:outline-none focus:ring-2 focus:ring-brand"
            aria-label="Filter by facility">
            <option value="">All Facilities</option>
            @foreach($facilities as $f)
                <option>{{ $f }}</option>
            @endforeach
        </select>
        <button @click="clearFilters()" x-show="search || filterStatus || filterFacility"
            class="px-3 py-2 text-xs font-medium text-[#DC2626] bg-red-50 border border-red-200 rounded-lg hover:bg-red-100">
            Clear
        </button>
        <span class="text-sm text-[#64748B]">
            <span class="font-semibold text-[#0F172A]" x-text="filtered.length"></span> record(s)
        </span>
    </div>
</x-card>

{{-- Table --}}
<div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="w-full text-sm text-left" aria-label="Transfer history">
            <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0]">
                <tr>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide">Reference</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden sm:table-cell">Prisoner No.</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide">Inmate</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden lg:table-cell">Movement</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden md:table-cell">Request Date</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden md:table-cell">Closed</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F1F5F9]">
                <template x-for="t in filtered" :key="t.id">
                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                        <td class="px-4 py-3">
                            <a :href="`/transfers/${t.id}`" class="font-mono text-xs text-[#2563EB] font-semibold hover:underline" x-text="t.transfer_ref"></a>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell">
                            <span class="font-mono text-xs text-[#64748B]" x-text="t.prisoner_number"></span>
                        </td>
                        <td class="px-4 py-3">
                            <a :href="`/transfers/${t.id}`" class="text-sm font-medium text-[#0F172A] hover:text-[#2563EB]" x-text="t.inmate_name"></a>
                        </td>
                        <td class="px-4 py-3 hidden lg:table-cell">
                            <div class="flex items-center gap-1.5 text-xs text-[#64748B]">
                                <span x-text="t.origin_facility" class="truncate max-w-24"></span>
                                <svg class="w-3 h-3 text-[#2563EB] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                                <span x-text="t.destination_facility" class="truncate max-w-24"></span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-[#64748B] hidden md:table-cell" x-text="t.request_date"></td>
                        <td class="px-4 py-3 text-sm text-[#64748B] hidden md:table-cell" x-text="t.closed_date || '—'"></td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium"
                                :class="{
                                    'bg-green-100 text-green-700': t.status === 'Completed',
                                    'bg-red-100 text-red-700':     t.status === 'Rejected',
                                    'bg-slate-100 text-slate-500': t.status === 'Cancelled',
                                }"
                                x-text="t.status">
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a :href="`/transfers/${t.id}`"
                               class="text-xs text-[#2563EB] hover:underline font-medium">View</a>
                        </td>
                    </tr>
                </template>
                <tr x-show="filtered.length === 0">
                    <td colspan="8" class="px-6 py-10 text-center text-sm text-[#64748B]">No historical transfer records match your search.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>



</div>

<script>
function historyDirectory() {
    return {
        all: [], filtered: [],
        search: '', filterStatus: '', filterFacility: '',
        init() { this.all = window.__TRF_HISTORY__ || []; this.applyFilters(); },
        applyFilters() {
            const q = this.search.toLowerCase().trim();
            this.filtered = this.all.filter(t => {
                if (q) {
                    const hay = `${t.transfer_ref} ${t.prisoner_number} ${t.inmate_name}`.toLowerCase();
                    if (!hay.includes(q)) return false;
                }
                if (this.filterStatus  && t.status !== this.filterStatus) return false;
                if (this.filterFacility && t.origin_facility !== this.filterFacility && t.destination_facility !== this.filterFacility) return false;
                return true;
            });
        },
        clearFilters() { this.search = ''; this.filterStatus = ''; this.filterFacility = ''; this.applyFilters(); },
    };
}
</script>

@endsection
