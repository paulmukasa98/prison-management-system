@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

{{-- Alpine dashboard component wraps the entire page content --}}
<div x-data="dashboard({{ Js::from($periodsJson) }}, {{ Js::from($facilitiesJson) }})">

{{-- =====================================================================
     DASHBOARD HEADER
     ===================================================================== --}}
<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <h1 class="text-xl font-bold text-[#0F172A]">Operational Dashboard</h1>
            <x-badge variant="inactive">{{ now()->format('l, d F Y') }}</x-badge>
        </div>
        <p class="text-sm text-[#64748B]">
            {{ auth()->user()->primaryFacilityName() }} &mdash; <em class="not-italic text-[#2563EB] font-medium">Authorized database statistics</em>
        </p>
    </div>

    {{-- Reporting period selector + actions --}}
    <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
        {{-- Period selector --}}
        <div class="relative">
            <select
                @change="changePeriod($event.target.value)"
                class="pl-3 pr-8 py-2 text-sm font-medium text-[#0F172A] bg-white border border-[#E2E8F0] rounded-lg
                       focus:outline-none focus:ring-2 focus:ring-brand appearance-none cursor-pointer"
                aria-label="Select reporting period"
            >
                @foreach($demoContext['period_options'] as $key => $label)
                    <option value="{{ $key }}" {{ $key === 'this_month' ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <div class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2">
                <svg class="w-4 h-4 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </div>

        @permission('reports.view')
<x-button.secondary size="sm" href="{{ route('reports.index') }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Reports
        </x-button.secondary>
@endpermission

        @permission('inmates.admit')
<x-button.primary size="sm" href="{{ route('inmates.create') }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Admission
        </x-button.primary>
@endpermission
    </div>
</div>

{{-- =====================================================================
     PRIMARY KPI CARDS
     ===================================================================== --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

    {{-- Active Inmates --}}
    @permission('inmates.read')
<div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-5">
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide mb-1" title="Total inmates currently held across all facilities">Active Inmates</p>
                <p class="text-2xl font-bold text-[#0F172A] tabular-nums" x-text="fmt(activeInmates)">0</p>
                <p class="text-xs text-[#64748B] mt-1">Across all facilities</p>
                <p class="text-xs mt-1 text-green-600">
                    ↑ <span x-text="fmt(newAdmissions)">0</span> admissions this period
                </p>
            </div>
            <div class="flex-shrink-0 w-10 h-10 bg-blue-50 ring-1 ring-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-[#2563EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>
    </div>
@endpermission

    {{-- Total Personnel --}}
    @permission('personnel.read')
<div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-5">
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide mb-1" title="All active prison service officers and staff">Total Personnel</p>
                <p class="text-2xl font-bold text-[#0F172A] tabular-nums" x-text="fmt(totalPersonnel)">0</p>
                <p class="text-xs text-[#64748B] mt-1">Officers &amp; staff</p>
                <p class="text-xs mt-1 text-[#64748B]">Across all facilities</p>
            </div>
            <div class="flex-shrink-0 w-10 h-10 bg-green-50 ring-1 ring-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-[#16A34A]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
        </div>
    </div>
@endpermission

    {{-- Pending Transfers --}}
    @permissionAny(['transfers.request', 'transfers.review', 'transfers.authorize', 'transfers.dispatch', 'transfers.receive'])
<div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-5">
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide mb-1" title="Transfer requests awaiting approval or processing">Pending Transfers</p>
                <p class="text-2xl font-bold text-[#0F172A] tabular-nums" x-text="fmt(pendingTransfers)">0</p>
                <p class="text-xs text-[#64748B] mt-1">Awaiting approval</p>
                @permissionAny(['transfers.request', 'transfers.review', 'transfers.authorize', 'transfers.dispatch', 'transfers.receive'])
<a href="{{ route('transfers.index') }}" class="text-xs mt-1 text-[#2563EB] hover:underline inline-block">Review requests →</a>
@endpermissionAny
            </div>
            <div class="flex-shrink-0 w-10 h-10 bg-amber-50 ring-1 ring-amber-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-[#D97706]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
            </div>
        </div>
    </div>
@endpermissionAny

    {{-- Visits --}}
    @permission('visitors.read')
<div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-5">
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide mb-1" title="Authorized visitor appointments during this reporting period">Visits This Period</p>
                <p class="text-2xl font-bold text-[#0F172A] tabular-nums" x-text="fmt(visits)">0</p>
                <p class="text-xs text-[#64748B] mt-1" x-text="period.label">This Month</p>
                <p class="text-xs mt-1 text-[#64748B]">Authorized visits conducted</p>
            </div>
            <div class="flex-shrink-0 w-10 h-10 bg-purple-50 ring-1 ring-purple-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
        </div>
    </div>
@endpermission

</div>

{{-- =====================================================================
     CHARTS ROW 1 — Admissions/Discharges + Gender Breakdown
     ===================================================================== --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

    {{-- Admissions & Discharges Chart --}}
    @permission('inmates.read')
<div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm h-full">
            <div class="flex items-center justify-between px-5 pt-5 pb-4 border-b border-[#E2E8F0]">
                <div>
                    <h2 class="text-sm font-semibold text-[#0F172A]">Admissions &amp; Discharges</h2>
                    <p class="text-xs text-[#64748B] mt-0.5">Monthly comparison — <span x-text="period.label">This Month</span></p>
                </div>
                <div class="flex items-center gap-2 text-xs text-[#64748B]">
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-[#2563EB] inline-block"></span> Admissions</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-[#16A34A] inline-block"></span> Discharges</span>
                </div>
            </div>
            <div class="px-5 py-5">
                <div class="relative" style="height: 220px;">
                    <canvas id="chart-admissions" role="img" aria-label="Bar chart showing monthly admissions and discharges"></canvas>
                </div>
                
            </div>
        </div>
    </div>
@endpermission

    {{-- Gender Demographics --}}
    @permission('inmates.read')
<div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm">
        <div class="px-5 pt-5 pb-4 border-b border-[#E2E8F0]">
            <h2 class="text-sm font-semibold text-[#0F172A]">Population by Gender</h2>
            <p class="text-xs text-[#64748B] mt-0.5">Current active population</p>
        </div>
        <div class="px-5 py-5">
            <div class="relative mx-auto" style="max-width: 180px; height: 180px;">
                <canvas id="chart-gender" role="img" aria-label="Doughnut chart showing inmate population by gender"></canvas>
                {{-- Centre label --}}
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <p class="text-lg font-bold text-[#0F172A] tabular-nums leading-tight" x-text="fmt(genderStats.total)">0</p>
                    <p class="text-[10px] text-[#64748B] leading-tight">Total</p>
                </div>
            </div>

            {{-- Accessible text summary --}}
            <div class="mt-4 space-y-2" aria-label="Gender breakdown summary">
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#2563EB] inline-block" aria-hidden="true"></span>
                        <span class="text-[#64748B]">Male</span>
                    </div>
                    <div class="text-right">
                        <span class="font-semibold text-[#0F172A] tabular-nums" x-text="fmt(genderStats.male)">0</span>
                        <span class="text-[#94A3B8] ml-1" x-text="'(' + genderStats.malePct + '%)'"></span>
                    </div>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-purple-500 inline-block" aria-hidden="true"></span>
                        <span class="text-[#64748B]">Female</span>
                    </div>
                    <div class="text-right">
                        <span class="font-semibold text-[#0F172A] tabular-nums" x-text="fmt(genderStats.female)">0</span>
                        <span class="text-[#94A3B8] ml-1" x-text="'(' + genderStats.femalePct + '%)'"></span>
                    </div>
                </div>
                <div class="flex items-center justify-between text-xs" x-show="genderStats.unspecified > 0">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-slate-400 inline-block" aria-hidden="true"></span>
                        <span class="text-[#64748B]">Unspecified</span>
                    </div>
                    <div class="text-right">
                        <span class="font-semibold text-[#0F172A] tabular-nums" x-text="fmt(genderStats.unspecified)">0</span>
                        <span class="text-[#94A3B8] ml-1" x-text="'(' + genderStats.unspecPct + '%)'"></span>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
@endpermission

</div>

{{-- =====================================================================
     CHARTS ROW 2 — Facility Population + Custody Classification
     ===================================================================== --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

    {{-- Facility Population Chart --}}
    @permission('inmates.read')
<div class="lg:col-span-2 bg-white rounded-xl border border-[#E2E8F0] shadow-sm">
        <div class="flex items-center justify-between px-5 pt-5 pb-4 border-b border-[#E2E8F0]">
            <div>
                <h2 class="text-sm font-semibold text-[#0F172A]">Population by Facility</h2>
                <p class="text-xs text-[#64748B] mt-0.5">Current population and recorded capacity</p>
            </div>
        </div>
        <div class="px-5 py-5">
            <div class="relative" style="height: 220px;">
                <canvas id="chart-facility" role="img" aria-label="Horizontal bar chart showing inmate population by facility"></canvas>
            </div>
            
        </div>
    </div>
@endpermission

    {{-- Custody Classification --}}
    @permission('inmates.read')
<div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm">
        <div class="px-5 pt-5 pb-4 border-b border-[#E2E8F0]">
            <h2 class="text-sm font-semibold text-[#0F172A]">Custody Classification</h2>
            
        </div>
        <div class="px-5 py-5 space-y-4">
            <template x-for="(item, idx) in custodyStats" :key="idx">
                <div>
                    <div class="flex justify-between text-xs mb-1.5">
                        <span class="font-medium text-[#0F172A]" x-text="item.label"></span>
                        <div>
                            <span class="font-semibold tabular-nums" x-text="fmt(item.count)"></span>
                            <span class="text-[#94A3B8] ml-1" x-text="'(' + item.pct + '%)'"></span>
                        </div>
                    </div>
                    <div class="w-full bg-[#F1F5F9] rounded-full h-2">
                        <div
                            class="h-2 rounded-full transition-all duration-500"
                            :class="idx === 0 ? 'bg-[#2563EB]' : idx === 1 ? 'bg-amber-500' : 'bg-purple-500'"
                            :style="'width: ' + item.pct + '%'"
                            :aria-label="item.label + ': ' + item.pct + '%'"
                            role="progressbar"
                            :aria-valuenow="item.pct"
                            aria-valuemin="0"
                            aria-valuemax="100"
                        ></div>
                    </div>
                </div>
            </template>

            <div class="pt-3 border-t border-[#F1F5F9]">
                <p class="text-xs text-[#64748B]">Total represented</p>
                <p class="text-xl font-bold text-[#0F172A] tabular-nums mt-0.5" x-text="fmt(activeInmates)">0</p>
            </div>
            
        </div>
    </div>
@endpermission

</div>

{{-- =====================================================================
     BOTTOM ROW — Quick Actions + Recent Activity
     ===================================================================== --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Quick Actions --}}
    @permissionAny(['inmates.admit', 'transfers.request', 'transfers.review', 'transfers.authorize', 'transfers.dispatch', 'transfers.receive', 'visits.schedule', 'reports.view'])
<div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-5">
        <h2 class="text-sm font-semibold text-[#0F172A] mb-4">Quick Actions</h2>
        <div class="space-y-2">

            @permission('inmates.admit')
<a href="{{ route('inmates.create') }}"
               class="flex items-center gap-3 p-3 rounded-lg border border-[#E2E8F0] hover:border-brand hover:bg-brand-soft transition-colors group">
                <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center group-hover:bg-brand transition-colors flex-shrink-0">
                    <svg class="w-5 h-5 text-[#2563EB] group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-[#0F172A]">New Admission</p>
                    <p class="text-xs text-[#64748B]">Register an incoming inmate</p>
                </div>
            </a>
@endpermission

            @permissionAny(['transfers.request', 'transfers.review', 'transfers.authorize', 'transfers.dispatch', 'transfers.receive'])
<a href="{{ route('transfers.index') }}"
               class="flex items-center gap-3 p-3 rounded-lg border border-[#E2E8F0] hover:border-amber-400 hover:bg-amber-50 transition-colors group">
                <div class="w-9 h-9 bg-amber-50 rounded-lg flex items-center justify-center group-hover:bg-[#D97706] transition-colors flex-shrink-0">
                    <svg class="w-5 h-5 text-[#D97706] group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-[#0F172A]">View Transfers</p>
                    <p class="text-xs text-[#64748B]">Review transfer records</p>
                </div>
            </a>
@endpermissionAny

            @permission('visits.schedule')
<a href="{{ route('visitors.schedule') }}"
               class="flex items-center gap-3 p-3 rounded-lg border border-[#E2E8F0] hover:border-green-400 hover:bg-green-50 transition-colors group">
                <div class="w-9 h-9 bg-green-50 rounded-lg flex items-center justify-center group-hover:bg-[#16A34A] transition-colors flex-shrink-0">
                    <svg class="w-5 h-5 text-[#16A34A] group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-[#0F172A]">Schedule Visit</p>
                    <p class="text-xs text-[#64748B]">Schedule a visitor appointment</p>
                </div>
            </a>
@endpermission

            @permission('reports.view')
<a href="{{ route('reports.index') }}"
               class="flex items-center gap-3 p-3 rounded-lg border border-[#E2E8F0] hover:border-purple-400 hover:bg-purple-50 transition-colors group">
                <div class="w-9 h-9 bg-purple-50 rounded-lg flex items-center justify-center group-hover:bg-purple-600 transition-colors flex-shrink-0">
                    <svg class="w-5 h-5 text-purple-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-[#0F172A]">View Reports</p>
                    <p class="text-xs text-[#64748B]">Analytics and operational reports</p>
                </div>
            </a>
@endpermission

        </div>
    </div>
@endpermissionAny

    {{-- Recent Activity --}}
    @permission('audit.view')
<div class="lg:col-span-2 bg-white rounded-xl border border-[#E2E8F0] shadow-sm">
        <div class="flex items-center justify-between px-5 pt-5 pb-4 border-b border-[#E2E8F0]">
            <h2 class="text-sm font-semibold text-[#0F172A]">Recent Activity</h2>
            @permission('audit.view')
<a href="{{ route('audit.index') }}" class="text-xs text-[#2563EB] hover:underline">View full audit log</a>
@endpermission
        </div>
        <div class="divide-y divide-[#F8FAFC]">
            @foreach($demoContext['recent_activities'] as $activity)
            @php
            $dotColors = [
                'blue'   => 'bg-blue-100 text-blue-600',
                'yellow' => 'bg-amber-100 text-amber-600',
                'green'  => 'bg-green-100 text-green-600',
                'purple' => 'bg-purple-100 text-purple-600',
                'gray'   => 'bg-slate-100 text-slate-500',
            ];
            $icons = [
                'admission'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>',
                'transfer'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>',
                'visit'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
                'discharge'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>',
                'personnel'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
            ];
            $dc  = $dotColors[$activity['color']] ?? $dotColors['gray'];
            $svg = $icons[$activity['type']]     ?? $icons['personnel'];
            @endphp
            <div class="flex items-start gap-3 px-5 py-3.5 hover:bg-[#FAFAFA] transition-colors">
                <div class="flex-shrink-0 w-8 h-8 rounded-lg {{ $dc }} flex items-center justify-center mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        {!! $svg !!}
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-[#0F172A]">{{ $activity['text'] }}</p>
                    <p class="text-xs text-[#64748B] mt-0.5">{{ $activity['detail'] }}</p>
                </div>
                <div class="flex-shrink-0 text-right">
                    <p class="text-xs text-[#94A3B8] whitespace-nowrap">{{ $activity['time'] }}</p>
                    <x-badge :variant="$activity['status']" class="mt-1">{{ $activity['outcome'] }}</x-badge>
                </div>
            </div>
            @endforeach
        </div>
        <div class="px-5 py-3 border-t border-[#F1F5F9]">
            
        </div>
    </div>
@endpermission

</div>

</div>{{-- end x-data="dashboard" --}}

@endsection
