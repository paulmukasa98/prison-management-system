@extends('layouts.guest')

@section('title', 'Forgot Password')

@section('form')

<div x-data="{
    email: '{{ old('email', '') }}',
    emailError: '{{ $errors->first('email') }}',
    loading: false,

    validateEmail() {
        if (!this.email) { this.emailError = 'Email address is required.'; return false; }
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!re.test(this.email)) { this.emailError = 'Please enter a valid email address.'; return false; }
        this.emailError = '';
        return true;
    },

    submitForm(event) {
        if (!this.validateEmail()) {
            event.preventDefault();
            return;
        }
        this.loading = true;
    },
}">

    {{-- Back link --}}
    <div class="mb-6">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-sm text-[#2563EB] hover:underline">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Sign In
        </a>
    </div>

    {{-- Page heading --}}
    <div class="mb-8">
        <div class="w-12 h-12 bg-[#DBEAFE] rounded-xl flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-[#2563EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-[#0F172A]">Forgot your password?</h1>
        <p class="text-sm text-[#64748B] mt-1 leading-relaxed">
            Enter your registered email address and we will send a secure password-reset link to your inbox.
        </p>
    </div>

    {{-- Status message --}}
    @if(session('status'))
        <div class="mb-5 flex items-start gap-3 bg-green-50 border border-green-200 rounded-lg px-4 py-3" role="status">
            <svg class="w-4 h-4 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-xs text-green-700 leading-relaxed">{{ session('status') }}</p>
        </div>
    @endif

    <form action="{{ route('password.email') }}" method="POST" @submit="submitForm($event)" class="space-y-5">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                Email Address
                <span class="text-[#DC2626] ml-0.5" aria-hidden="true">*</span>
            </label>
            <input
                id="email"
                name="email"
                type="email"
                autocomplete="email"
                required
                x-model="email"
                @input="emailError = ''"
                @blur="validateEmail()"
                :class="emailError ? 'border-[#DC2626] focus:ring-red-500' : 'border-[#E2E8F0] focus:ring-brand'"
                class="w-full px-3 py-2.5 text-sm text-[#0F172A] bg-white border rounded-lg
                       placeholder-[#94A3B8] transition-colors
                       focus:outline-none focus:ring-2 focus:border-transparent"
                placeholder="you@ups.go.ug"
                :aria-invalid="emailError ? 'true' : 'false'"
                aria-describedby="email-error"
            >
            <p id="email-error" class="mt-1.5 text-xs text-[#DC2626]" role="alert" x-show="emailError" x-text="emailError" style="{{ $errors->has('email') ? '' : 'display:none' }}">
                {{ $errors->first('email') }}
            </p>
        </div>

        <button
            type="submit"
            :disabled="loading"
            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white
                   bg-brand hover:bg-brand-hover active:bg-brand-active rounded-lg transition-colors
                   focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand
                   disabled:opacity-70 disabled:cursor-not-allowed"
        >
            <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true" style="display:none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span x-text="loading ? 'Sending link…' : 'Send Reset Link'">Send Reset Link</span>
        </button>
    </form>

    {{-- Security note --}}
    <div class="mt-8 flex items-start gap-2 text-xs text-[#94A3B8]">
        <svg class="w-3.5 h-3.5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        <span>Password reset links are valid for a single use and expire after 60 minutes.</span>
    </div>

</div>

@endsection
