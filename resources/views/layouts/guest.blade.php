<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Uganda Prisons Service — Prison Management System — Authorised Personnel Only">
    <title>@yield('title', 'Sign In') — UPS Prison Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('ups-theme.css') }}?v={{ filemtime(public_path('ups-theme.css')) }}">
</head>
<body class="h-full bg-app-bg font-sans antialiased">

<div class="min-h-screen flex">

    {{-- ===================================================================
         LEFT BRANDING PANEL (hidden on mobile)
         =================================================================== --}}
    <div class="hidden lg:flex lg:w-[45%] xl:w-[42%] flex-col bg-brand-dark relative overflow-hidden">

        {{-- Background texture dots --}}
        <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(circle, #ffffff 1px, transparent 1px); background-size: 28px 28px;"></div>

        {{-- Top accent line --}}
        <div class="absolute top-0 left-0 right-0 h-1 bg-brand-gold"></div>

        <div class="relative z-10 flex flex-col h-full px-10 py-10">

            {{-- Brand mark --}}
            <div class="flex items-center gap-3 mb-auto">
                <x-ups-logo :size="72" />
                <div>
                    <p class="text-xs font-bold text-brand-gold uppercase tracking-widest leading-tight">Uganda Prisons Service</p>
                    <p class="text-xs text-white/60 leading-tight">Prison Management System</p>
                </div>
            </div>

            {{-- Main message --}}
            <div class="py-16">
                <h1 class="text-3xl font-bold text-white leading-snug mb-4">
                    Secure Administration<br>for Uganda Prisons Service
                </h1>
                <p class="text-sm text-white/70 leading-relaxed max-w-sm">
                    A centralized management platform for inmate records, admissions, transfers, personnel, visitor oversight, and facility administration — available to authorized Uganda Prisons Service personnel only.
                </p>

                {{-- Feature highlights --}}
                <div class="mt-10 space-y-3">
                    @foreach([
                        ['icon' => 'users',    'text' => 'Inmate registry & admission management'],
                        ['icon' => 'arrows',   'text' => 'Inter-facility transfer coordination'],
                        ['icon' => 'building', 'text' => 'Multi-facility administration'],
                        ['icon' => 'chart',    'text' => 'Operational reporting & analytics'],
                    ] as $f)
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-brand-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="text-sm text-white/70">{{ $f['text'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Bottom security notice --}}
            <div class="mt-auto">
                <div class="flex items-start gap-2.5 bg-white/5 border border-white/10 rounded-lg px-4 py-3">
                    <svg class="w-4 h-4 text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <p class="text-xs text-white/60 leading-relaxed">
                        Access is restricted to authorized Uganda Prisons Service personnel. Unauthorized access is a criminal offence under applicable Ugandan law.
                    </p>
                </div>
                <p class="text-xs text-white/30 mt-4">&copy; {{ date('Y') }} Uganda Prisons Service. All rights reserved.</p>
            </div>
        </div>
    </div>

    {{-- ===================================================================
         RIGHT FORM PANEL
         =================================================================== --}}
    <div class="flex-1 flex flex-col">

        {{-- Mobile brand header --}}
        <div class="lg:hidden flex items-center gap-3 px-6 py-5 bg-brand-dark">
            <x-ups-logo :size="48" />
            <div>
                <p class="text-xs font-bold text-brand-gold uppercase tracking-widest leading-tight">Uganda Prisons Service</p>
                <p class="text-xs text-white/60 leading-tight">Prison Management System</p>
            </div>
        </div>

        {{-- Form content --}}
        <div class="flex-1 flex flex-col items-center justify-center px-6 py-10 sm:px-10">
            <div class="w-full max-w-md">
                {{ $slot ?? '' }}
                @yield('form')
            </div>
        </div>

        {{-- Form footer --}}
        <div class="px-6 py-4 border-t border-[#E2E8F0]">
            <p class="text-center text-xs text-[#94A3B8]">
                Uganda Prisons Service &mdash; Prison Management System &mdash; &copy; {{ date('Y') }}
            </p>
        </div>

    </div>

</div>

</body>
</html>
