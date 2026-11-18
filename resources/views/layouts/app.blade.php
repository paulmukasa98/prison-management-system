<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Uganda Prisons Service — Prison Management System">

    <title>@yield('title', 'Dashboard') — UPS Prison Management System</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('ups-theme.css') }}?v={{ filemtime(public_path('ups-theme.css')) }}">
</head>
<body class="h-full bg-app-bg font-sans antialiased" x-data="sidebar"
    @resize.window="if (window.innerWidth >= 1024) mobileOpen = false"
      @keydown.escape.window="if(mobileOpen) closeMobile()" @keydown.tab.window="if(mobileOpen) trapFocus($event)">

{{-- =====================================================================
     MOBILE SIDEBAR OVERLAY
     ===================================================================== --}}
<div
    x-show="mobileOpen"
    x-transition:enter="transition-opacity ease-linear duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-linear duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-40 bg-black/50 lg:hidden"
    @click="closeMobile()"
    style="display:none"
></div>

{{-- =====================================================================
     LEFT SIDEBAR
     ===================================================================== --}}
<aside
    id="sidebar"
    :class="mobileOpen ? 'translate-x-0 visible' : '-translate-x-full invisible lg:visible'"
    class="fixed inset-y-0 left-0 z-50 w-64 flex flex-col bg-brand-dark sidebar-transition lg:translate-x-0 sidebar-scroll overflow-y-auto"
>
    {{-- Brand --}}
    <div class="flex items-center gap-3 px-5 py-5 border-b border-white/10 flex-shrink-0">
        <x-ups-logo :size="48" />
        <div class="min-w-0">
            <p class="text-xs font-semibold text-brand-gold uppercase tracking-wider leading-tight truncate">Uganda Prisons Service</p>
            <p class="text-xs text-white/70 leading-tight truncate">Prison Management System</p>
        </div>
        {{-- Close button mobile --}}
        <button
            @click="closeMobile()"
            class="ml-auto lg:hidden text-white/50 hover:text-white p-1 rounded"
            x-ref="mobileNavClose" aria-label="Close sidebar"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 py-4 space-y-0.5" aria-label="Main navigation">

        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}"
           class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                  {{ request()->routeIs('dashboard') ? 'nav-active' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>

        @permissionAny(['inmates.read', 'inmates.admit'])
{{-- Inmates --}}
        <div x-data="navGroup('inmates', {{ request()->routeIs('inmates.*') ? 'true' : 'false' }})">
            <button
                @click="toggle()"
                class="nav-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                       {{ request()->routeIs('inmates.*') ? 'nav-active' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                :aria-expanded="expanded"
                aria-controls="nav-inmates"
            >
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="flex-1 text-left">Inmates</span>
                <svg class="w-4 h-4 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="expanded" id="nav-inmates" class="ml-8 mt-0.5 space-y-0.5">
                @permission('inmates.read')
<a href="{{ route('inmates.index') }}"
                   class="block px-3 py-2 rounded-lg text-sm transition-colors
                          {{ request()->routeIs('inmates.index') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                    All Inmates
                </a>
@endpermission
                @permission('inmates.admit')
<a href="{{ route('inmates.create') }}"
                   class="block px-3 py-2 rounded-lg text-sm transition-colors
                          {{ request()->routeIs('inmates.create') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                    New Admission
                </a>
@endpermission
                @permission('inmates.read')
<a href="{{ route('inmates.discharges') }}"
                   class="block px-3 py-2 rounded-lg text-sm transition-colors
                          {{ request()->routeIs('inmates.discharges') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                    Discharge Records
                </a>
@endpermission
            </div>
        </div>

        @endpermissionAny

        @permissionAny(['transfers.request', 'transfers.review', 'transfers.authorize', 'transfers.dispatch', 'transfers.receive'])
{{-- Transfers --}}
        <div x-data="navGroup('transfers', {{ request()->routeIs('transfers.*') ? 'true' : 'false' }})">
            <button
                @click="toggle()"
                class="nav-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                       {{ request()->routeIs('transfers.*') ? 'nav-active' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                :aria-expanded="expanded"
                aria-controls="nav-transfers"
            >
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                <span class="flex-1 text-left">Transfers</span>
                <svg class="w-4 h-4 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="expanded" id="nav-transfers" class="ml-8 mt-0.5 space-y-0.5">
                @permissionAny(['transfers.request', 'transfers.review', 'transfers.authorize', 'transfers.dispatch', 'transfers.receive'])
<a href="{{ route('transfers.index') }}"
                   class="block px-3 py-2 rounded-lg text-sm transition-colors
                          {{ request()->routeIs('transfers.index') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                    Transfer Requests
                </a>
@endpermissionAny
                @permissionAny(['transfers.request', 'transfers.review', 'transfers.authorize', 'transfers.dispatch', 'transfers.receive'])
<a href="{{ route('transfers.history') }}"
                   class="block px-3 py-2 rounded-lg text-sm transition-colors
                          {{ request()->routeIs('transfers.history') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                    Transfer History
                </a>
@endpermissionAny
            </div>
        </div>

        @endpermissionAny

        @permissionAny(['personnel.read', 'personnel.manage'])
{{-- Personnel --}}
        <div x-data="navGroup('personnel', {{ request()->routeIs('personnel.*') ? 'true' : 'false' }})">
            <button
                @click="toggle()"
                class="nav-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                       {{ request()->routeIs('personnel.*') ? 'nav-active' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                :aria-expanded="expanded"
                aria-controls="nav-personnel"
            >
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span class="flex-1 text-left">Personnel</span>
                <svg class="w-4 h-4 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="expanded" id="nav-personnel" class="ml-8 mt-0.5 space-y-0.5">
                @permission('personnel.manage')
                <a href="{{ route('personnel.create') }}" class="block px-3 py-2 rounded-lg text-sm text-white/60 hover:text-white hover:bg-white/10">New Personnel</a>
                @endpermission
                @permission('personnel.read')
<a href="{{ route('personnel.index') }}"
                   class="block px-3 py-2 rounded-lg text-sm transition-colors
                          {{ request()->routeIs('personnel.index') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                    All Personnel
                </a>
@endpermission
                @permission('personnel.read')
<a href="{{ route('personnel.assignments') }}"
                   class="block px-3 py-2 rounded-lg text-sm transition-colors
                          {{ request()->routeIs('personnel.assignments') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                    Facility Assignments
                </a>
@endpermission
            </div>
        </div>

        @endpermissionAny

        {{-- Prison Facilities --}}
        @permission('facilities.read')
<a href="{{ route('facilities.index') }}"
           class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                  {{ request()->routeIs('facilities.*') ? 'nav-active' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            Prison Facilities
        </a>
@endpermission

        @permissionAny(['visitors.read', 'visitors.register', 'visits.schedule'])
{{-- Visitors --}}
        <div x-data="navGroup('visitors', {{ request()->routeIs('visitors.*') ? 'true' : 'false' }})">
            <button
                @click="toggle()"
                class="nav-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                       {{ request()->routeIs('visitors.*') ? 'nav-active' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                :aria-expanded="expanded"
                aria-controls="nav-visitors"
            >
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span class="flex-1 text-left">Visitors</span>
                <svg class="w-4 h-4 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="expanded" id="nav-visitors" class="ml-8 mt-0.5 space-y-0.5">
                @permission('visitors.register')
                <a href="{{ route('visitors.create') }}" class="block px-3 py-2 rounded-lg text-sm text-white/60 hover:text-white hover:bg-white/10">Register Visitor</a>
                @endpermission
                @permission('visitors.read')
<a href="{{ route('visitors.index') }}"
                   class="block px-3 py-2 rounded-lg text-sm transition-colors
                          {{ request()->routeIs('visitors.index') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                    Visitor Directory
                </a>
@endpermission
                @permission('visitors.read')
<a href="{{ route('visitors.records') }}"
                   class="block px-3 py-2 rounded-lg text-sm transition-colors
                          {{ request()->routeIs('visitors.records') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                    Visit Records
                </a>
@endpermission
                @permission('visits.schedule')
<a href="{{ route('visitors.schedule') }}"
                   class="block px-3 py-2 rounded-lg text-sm transition-colors
                          {{ request()->routeIs('visitors.schedule') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                    Schedule Visit
                </a>
@endpermission
            </div>
        </div>

        @endpermissionAny

        {{-- Reports & Analytics --}}
        @permission('reports.view')
<a href="{{ route('reports.index') }}"
           class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                  {{ request()->routeIs('reports.*') ? 'nav-active' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Reports &amp; Analytics
        </a>
@endpermission

        {{-- Divider --}}
        <div class="my-2 border-t border-white/10"></div>

        {{-- Audit Logs --}}
        @permission('audit.view')
<a href="{{ route('audit.index') }}"
           class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                  {{ request()->routeIs('audit.*') ? 'nav-active' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            Audit Logs
        </a>
@endpermission

        @nationalPermission('users.manage')
{{-- User Management --}}
        <div x-data="navGroup('users', {{ request()->routeIs('users.*') ? 'true' : 'false' }})">
            <button
                @click="toggle()"
                class="nav-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                       {{ request()->routeIs('users.*') ? 'nav-active' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                :aria-expanded="expanded"
                aria-controls="nav-users"
            >
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span class="flex-1 text-left">User Management</span>
                <svg class="w-4 h-4 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="expanded" id="nav-users" class="ml-8 mt-0.5 space-y-0.5">
                @nationalPermission('users.manage')
<a href="{{ route('users.index') }}"
                   class="block px-3 py-2 rounded-lg text-sm transition-colors
                          {{ request()->routeIs('users.index') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                    System Users
                </a>
@endnationalPermission
                @nationalPermission('users.manage')
<a href="{{ route('users.roles.index') }}"
                   class="block px-3 py-2 rounded-lg text-sm transition-colors
                          {{ request()->routeIs('users.roles.index') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                    Roles &amp; Permissions
                </a>
@endnationalPermission
            </div>
        </div>

        @endnationalPermission

        {{-- Settings --}}
        @nationalPermission('users.manage')
<a href="{{ route('settings.index') }}"
           class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                  {{ request()->routeIs('settings.*') ? 'nav-active' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Settings
        </a>
@endnationalPermission

    </nav>

    {{-- Sidebar Footer — demo user info --}}
    <div class="flex-shrink-0 border-t border-white/10 px-4 py-3">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-brand flex items-center justify-center flex-shrink-0">
                <span class="text-xs font-bold text-white">{{ $demoContext['demo_user']['initials'] }}</span>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold text-white truncate">{{ $demoContext['demo_user']['name'] }}</p>
                <p class="text-xs text-white/50 truncate">{{ $demoContext['demo_user']['role'] }}</p>
            </div>
        </div>
        {{-- Connection status --}}
        <div class="mt-2" x-data="connectionStatus()">
            <span
                class="inline-flex items-center gap-1.5 text-xs px-2 py-0.5 rounded-full"
                :class="{
                    'conn-online':  status === 'online',
                    'conn-offline': status === 'offline',
                    'conn-pending': status === 'pending',
                    'conn-error':   status === 'error',
                }"
            >
                <span
                    class="w-1.5 h-1.5 rounded-full"
                    :class="{
                        'bg-green-500':  status === 'online',
                        'bg-red-500':    status === 'offline',
                        'bg-yellow-500': status === 'pending',
                        'bg-red-700':    status === 'error',
                    }"
                ></span>
                <span x-text="message">Online</span>
            </span>
        </div>
    </div>
</aside>

{{-- =====================================================================
     MAIN WRAPPER (offset by sidebar on desktop)
     ===================================================================== --}}
<div class="lg:pl-64 flex flex-col min-h-screen" :inert="mobileOpen">

    {{-- ===================================================================
         TOP NAVIGATION BAR
         =================================================================== --}}
    <header class="ups-app-header sticky top-0 z-30 bg-white border-b border-[#E2E8F0] no-print">
        <div class="flex items-center gap-3 px-4 sm:px-6 h-16">

            {{-- Mobile sidebar toggle --}}
            <button
                @click="mobileOpen = true; $nextTick(() => $refs.mobileNavClose.focus())"
                x-ref="mobileNavToggle" :aria-expanded="mobileOpen" aria-controls="sidebar"
                class="lg:hidden p-2 rounded-lg text-[#64748B] hover:bg-[#F1F5F9] hover:text-[#0F172A] transition-colors"
                aria-label="Open navigation menu"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Page title / breadcrumb --}}
            <div class="flex-1 min-w-0">
                @hasSection('breadcrumb')
                    @yield('breadcrumb')
                @else
                    <h1 class="text-base font-semibold text-[#0F172A] truncate">@yield('title', 'Dashboard')</h1>
                @endif
            </div>

            {{-- Facility context --}}
            <div class="hidden sm:flex items-center gap-1.5 text-xs text-[#64748B] bg-[#F8FAFC] border border-[#E2E8F0] rounded-lg px-3 py-1.5">
                <svg class="w-3.5 h-3.5 text-[#2563EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span class="font-medium text-[#0F172A]">{{ $demoContext['demo_user']['facility'] }}</span>
            </div>

            {{-- Search --}}
            <button
                class="p-2 rounded-lg text-[#64748B] hover:bg-[#F1F5F9] hover:text-[#0F172A] transition-colors"
                aria-label="Search"
                disabled title="Global search is not available in this preview"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>

            {{-- Notifications --}}
            <button
                class="relative p-2 rounded-lg text-[#64748B] hover:bg-[#F1F5F9] hover:text-[#0F172A] transition-colors"
                aria-label="{{ $demoContext['notification_count'] }} unread notifications"
                disabled title="Notifications are not implemented"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                @if($demoContext['notification_count'] > 0)
                <span class="absolute top-1 right-1 w-4 h-4 bg-[#DC2626] text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none" aria-hidden="true">
                    {{ $demoContext['notification_count'] }}
                </span>
                @endif
            </button>

            {{-- User dropdown --}}
            @php
                $currentUser = auth()->user();
                $displayName = $currentUser ? $currentUser->name : ($demoContext['demo_user']['name'] ?? 'Officer');
                $displayEmail = $currentUser ? $currentUser->email : ($demoContext['demo_user']['email'] ?? '');
                $displayRole = $currentUser ? $currentUser->primaryRoleName() : ($demoContext['demo_user']['role'] ?? 'Staff');
                $displayInitials = collect(explode(' ', $displayName))->filter()->map(fn($part) => strtoupper(substr($part, 0, 1)))->take(2)->implode('');
            @endphp
            <div class="relative" x-data="dropdown()">
                <button
                    @click="toggle()"
                    @click.outside="close()"
                    class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-[#F1F5F9] transition-colors"
                    aria-haspopup="true"
                    :aria-expanded="open"
                    aria-label="User menu"
                >
                    <div class="w-8 h-8 rounded-full bg-brand flex items-center justify-center">
                        <span class="text-xs font-bold text-white">{{ $displayInitials ?: 'UP' }}</span>
                    </div>
                    <span class="hidden sm:block text-sm font-medium text-[#0F172A] max-w-28 truncate">
                        {{ $displayName }}
                    </span>
                    <svg class="w-4 h-4 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-[#E2E8F0] py-1 z-50"
                    role="menu"
                    style="display:none"
                >
                    <div class="px-4 py-2.5 border-b border-[#E2E8F0]">
                        <p class="text-sm font-semibold text-[#0F172A]">{{ $displayName }}</p>
                        <p class="text-xs text-[#64748B]">{{ $displayRole }}</p>
                        <p class="text-xs text-[#2563EB] mt-0.5">{{ $displayEmail }}</p>
                    </div>
                    <div class="py-1">
                        <a href="{{ route('profile.show') }}" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-[#0F172A] hover:bg-[#F8FAFC]" role="menuitem"><svg class="w-4 h-4 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            My Profile
                        </a>
                        <a href="{{ route('profile.show') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-[#0F172A] hover:bg-[#F8FAFC]" role="menuitem">
                            <svg class="w-4 h-4 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Account Settings
                        </a>
                    </div>
                    <div class="border-t border-[#E2E8F0] py-1">
                        @auth
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-[#DC2626] hover:bg-red-50 text-left" role="menuitem">
                                    <svg class="w-4 h-4 text-[#DC2626]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Sign Out
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-[#2563EB] hover:bg-brand-soft text-left" role="menuitem">
                                <svg class="w-4 h-4 text-[#2563EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                Sign In
                            </a>
                        @endauth
                    </div>
                </div>
            </div>

        </div>
    </header>

    {{-- ===================================================================
         PAGE CONTENT
         =================================================================== --}}
    <main class="flex-1 px-4 sm:px-6 py-6" id="main-content">
        <div id="offline-write-warning" role="alert" hidden class="mb-5 rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">Connection unavailable. Changes require an online authorized session and are not queued or stored on this device.</div>
        @if(request()->is('inmates*', 'transfers*', 'personnel*', 'facilities*'))
            @if(session('status'))<x-alert type="success" class="mb-5">{{ session('status') }}</x-alert>@endif
            @if($errors->any())<x-alert type="error" class="mb-5"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></x-alert>@endif
        @endif
        @yield('content')
    </main>

    {{-- ===================================================================
         FOOTER
         =================================================================== --}}
    <footer class="border-t border-[#E2E8F0] bg-white px-6 py-4 no-print">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-[#64748B]">
            <span>Uganda Prisons Service &mdash; Prison Management System</span>
            <span>&copy; {{ date('Y') }} Uganda Prisons Service. All rights reserved.</span>
        </div>
        
    </footer>

</div>{{-- end main wrapper --}}

<script>
window.pmsSubmit = function (url, data, files = {}) {
    if (!navigator.onLine) {
        document.getElementById('offline-write-warning').hidden = false;
        return false;
    }
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    if (Object.values(files).some(input => input && input.files && input.files.length)) {
        form.enctype = 'multipart/form-data';
    }
    const payload = {...data, _token: {{ Js::from(csrf_token()) }}};
    for (const [name, value] of Object.entries(payload)) {
        if (value === null || value === undefined || typeof value === 'object') continue;
        const input = document.createElement('input');
        input.type = 'hidden'; input.name = name; input.value = typeof value === 'boolean' ? (value ? '1' : '0') : value;
        form.appendChild(input);
    }
    for (const [name, input] of Object.entries(files)) {
        if (input && input.files && input.files.length) {
            input.name = name;
            form.appendChild(input);
        }
    }
    document.body.appendChild(form);
    form.requestSubmit();
    return true;
};
document.addEventListener('submit', function (event) {
    if (event.target.method.toLowerCase() !== 'get' && !navigator.onLine) {
        event.preventDefault();
        const warning = document.getElementById('offline-write-warning');
        warning.hidden = false;
        warning.scrollIntoView({block: 'center'});
    }
});
window.addEventListener('online', function () { document.getElementById('offline-write-warning').hidden = true; });
window.addEventListener('offline', function () { document.getElementById('offline-write-warning').hidden = false; });
</script>
</body>
</html>
