@extends('layouts.guest')

@section('title', 'Reset Password')

@section('form')

<div x-data="{
    email: '{{ old('email', $email ?? '') }}',
    password: '',
    confirmPassword: '',
    showPassword: false,
    showConfirm: false,
    loading: false,
    emailError: '{{ $errors->first('email') }}',
    passwordError: '{{ $errors->first('password') }}',
    confirmError: '',

    get strength() {
        const p = this.password;
        if (!p) { return 0; }
        let s = 0;
        if (p.length >= 8)  { s++; }
        if (p.length >= 12) { s++; }
        if (/[A-Z]/.test(p)) { s++; }
        if (/[0-9]/.test(p)) { s++; }
        if (/[^A-Za-z0-9]/.test(p)) { s++; }
        return Math.min(s, 4);
    },

    get strengthLabel() {
        return ['', 'Weak', 'Fair', 'Good', 'Strong'][this.strength];
    },

    get strengthColor() {
        return ['', 'bg-red-500', 'bg-amber-500', 'bg-blue-500', 'bg-green-500'][this.strength];
    },

    validatePassword() {
        if (!this.password) { this.passwordError = 'New password is required.'; return false; }
        if (this.password.length < 8) { this.passwordError = 'Password must be at least 8 characters.'; return false; }
        this.passwordError = '';
        return true;
    },

    validateConfirm() {
        if (!this.confirmPassword) { this.confirmError = 'Please confirm your new password.'; return false; }
        if (this.confirmPassword !== this.password) { this.confirmError = 'Passwords do not match.'; return false; }
        this.confirmError = '';
        return true;
    },

    submitForm(event) {
        const vp = this.validatePassword();
        const vc = this.validateConfirm();
        if (!vp || !vc) {
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-[#0F172A]">Reset your password</h1>
        <p class="text-sm text-[#64748B] mt-1">Choose a new strong password for your account.</p>
    </div>

    {{-- Form --}}
    <form action="{{ route('password.update') }}" method="POST" @submit="submitForm($event)" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token ?? '' }}">

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-[#0F172A] mb-1.5">Email Address</label>
            <input
                id="email"
                name="email"
                type="email"
                autocomplete="email"
                required
                value="{{ old('email', $email ?? '') }}"
                x-model="email"
                class="w-full px-3 py-2.5 text-sm text-[#0F172A] bg-white border border-[#E2E8F0] rounded-lg
                       focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent"
                placeholder="you@ups.go.ug"
            >
            <p id="email-error" class="mt-1.5 text-xs text-[#DC2626]" role="alert" x-show="emailError" x-text="emailError" style="{{ $errors->has('email') ? '' : 'display:none' }}">
                {{ $errors->first('email') }}
            </p>
        </div>

        {{-- New Password --}}
        <div>
            <label for="password" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                New Password
                <span class="text-[#DC2626] ml-0.5" aria-hidden="true">*</span>
            </label>
            <div class="relative">
                <input
                    id="password"
                    name="password"
                    :type="showPassword ? 'text' : 'password'"
                    autocomplete="new-password"
                    required
                    x-model="password"
                    @input="passwordError = ''"
                    @blur="validatePassword()"
                    :class="passwordError ? 'border-[#DC2626] focus:ring-red-500' : 'border-[#E2E8F0] focus:ring-brand'"
                    class="w-full px-3 py-2.5 pr-11 text-sm text-[#0F172A] bg-white border rounded-lg
                           placeholder-[#94A3B8] transition-colors
                           focus:outline-none focus:ring-2 focus:border-transparent"
                    placeholder="At least 8 characters"
                >
                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-[#94A3B8] hover:text-[#64748B] transition-colors p-0.5"
                    :aria-label="showPassword ? 'Hide password' : 'Show password'"
                >
                    <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" style="display:none">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>

            {{-- Strength Meter --}}
            <div class="mt-2" x-show="password.length > 0">
                <div class="flex items-center justify-between text-xs mb-1">
                    <span class="text-[#64748B]">Password strength:</span>
                    <span class="font-medium" :class="{
                        'text-red-600': strength <= 1,
                        'text-amber-600': strength === 2,
                        'text-blue-600': strength === 3,
                        'text-green-600': strength >= 4
                    }" x-text="strengthLabel"></span>
                </div>
                <div class="w-full bg-[#E2E8F0] rounded-full h-1.5 overflow-hidden">
                    <div class="h-full transition-all duration-300" :class="strengthColor" :style="'width: ' + (strength * 25) + '%'"></div>
                </div>
            </div>

            <p id="password-error" class="mt-1.5 text-xs text-[#DC2626]" role="alert" x-show="passwordError" x-text="passwordError" style="{{ $errors->has('password') ? '' : 'display:none' }}">
                {{ $errors->first('password') }}
            </p>
        </div>

        {{-- Confirm Password --}}
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-[#0F172A] mb-1.5">
                Confirm New Password
                <span class="text-[#DC2626] ml-0.5" aria-hidden="true">*</span>
            </label>
            <div class="relative">
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    :type="showConfirm ? 'text' : 'password'"
                    autocomplete="new-password"
                    required
                    x-model="confirmPassword"
                    @input="confirmError = ''"
                    @blur="validateConfirm()"
                    :class="confirmError ? 'border-[#DC2626] focus:ring-red-500' : 'border-[#E2E8F0] focus:ring-brand'"
                    class="w-full px-3 py-2.5 pr-11 text-sm text-[#0F172A] bg-white border rounded-lg
                           placeholder-[#94A3B8] transition-colors
                           focus:outline-none focus:ring-2 focus:border-transparent"
                    placeholder="Repeat your new password"
                >
                <button
                    type="button"
                    @click="showConfirm = !showConfirm"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-[#94A3B8] hover:text-[#64748B] transition-colors p-0.5"
                    :aria-label="showConfirm ? 'Hide password' : 'Show password'"
                >
                    <svg x-show="!showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" style="display:none">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>
            <p id="confirm-error" class="mt-1.5 text-xs text-[#DC2626]" role="alert" x-show="confirmError" x-text="confirmError" style="display:none"></p>
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
            <span x-text="loading ? 'Updating…' : 'Reset Password'">Reset Password</span>
        </button>
    </form>

</div>

@endsection
