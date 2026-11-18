@extends('layouts.guest')

@section('title', 'Sign In')

@section('form')

<div x-data="{
    email: '{{ old('email', '') }}',
    password: '',
    showPassword: false,
    rememberMe: {{ old('remember') ? 'true' : 'false' }},
    loading: false,
    emailError: '{{ $errors->first('email') }}',
    passwordError: '{{ $errors->first('password') }}',

    validateEmail() {
        if (!this.email) {
            this.emailError = 'Email address is required.';
            return false;
        }
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!re.test(this.email)) {
            this.emailError = 'Please enter a valid email address.';
            return false;
        }
        this.emailError = '';
        return true;
    },

    validatePassword() {
        if (!this.password) {
            this.passwordError = 'Password is required.';
            return false;
        }
        this.passwordError = '';
        return true;
    },

    submitForm(event) {
        const validEmail = this.validateEmail();
        const validPassword = this.validatePassword();
        if (!validEmail || !validPassword) {
            event.preventDefault();
            return;
        }
        this.loading = true;
    },
}">

    {{-- Page heading --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-[#0F172A]">Welcome back</h1>
        <p class="text-sm text-[#64748B] mt-1">Sign in to your Uganda Prisons Service account.</p>
    </div>

    {{-- Session status / success message --}}
    @if(session('status'))
        <div class="mb-5 flex items-start gap-3 bg-green-50 border border-green-200 rounded-lg px-4 py-3" role="status">
            <svg class="w-4 h-4 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-xs text-green-700 leading-relaxed">{{ session('status') }}</p>
        </div>
    @endif

    {{-- Session error placeholder --}}
    @if(session('error'))
        <div class="mb-5 flex items-start gap-3 bg-red-50 border border-red-200 rounded-lg px-4 py-3" role="alert">
            <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-xs text-red-700">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Login form --}}
    <form action="{{ route('login') }}" method="POST" @submit="submitForm($event)" class="space-y-5">
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

        {{-- Password --}}
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-sm font-medium text-[#0F172A]">
                    Password
                    <span class="text-[#DC2626] ml-0.5" aria-hidden="true">*</span>
                </label>
                <a href="{{ route('password.request') }}" class="text-xs text-[#2563EB] hover:underline">
                    Forgot password?
                </a>
            </div>
            <div class="relative">
                <input
                    id="password"
                    name="password"
                    :type="showPassword ? 'text' : 'password'"
                    autocomplete="current-password"
                    required
                    x-model="password"
                    @input="passwordError = ''"
                    @blur="validatePassword()"
                    :class="passwordError ? 'border-[#DC2626] focus:ring-red-500' : 'border-[#E2E8F0] focus:ring-brand'"
                    class="w-full px-3 py-2.5 pr-11 text-sm text-[#0F172A] bg-white border rounded-lg
                           placeholder-[#94A3B8] transition-colors
                           focus:outline-none focus:ring-2 focus:border-transparent"
                    placeholder="Enter your password"
                    :aria-invalid="passwordError ? 'true' : 'false'"
                    aria-describedby="password-error"
                >
                {{-- Visibility toggle --}}
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
            <p id="password-error" class="mt-1.5 text-xs text-[#DC2626]" role="alert" x-show="passwordError" x-text="passwordError" style="{{ $errors->has('password') ? '' : 'display:none' }}">
                {{ $errors->first('password') }}
            </p>
        </div>

        {{-- Remember me --}}
        <div class="flex items-center gap-2.5">
            <input
                id="remember"
                name="remember"
                type="checkbox"
                value="1"
                x-model="rememberMe"
                class="w-4 h-4 rounded border-[#E2E8F0] text-[#2563EB] focus:ring-brand cursor-pointer"
            >
            <label for="remember" class="text-sm text-[#64748B] cursor-pointer select-none">
                Keep me signed in for 30 days
            </label>
        </div>

        {{-- Submit --}}
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
            <span x-text="loading ? 'Signing in…' : 'Sign In'">Sign In</span>
        </button>

    </form>

    {{-- Security note --}}
    <div class="mt-8 flex items-start gap-2 text-xs text-[#94A3B8]">
        <svg class="w-3.5 h-3.5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        <span>This system is for authorized Uganda Prisons Service personnel only. All access and activity is logged and monitored.</span>
    </div>

</div>

@endsection
