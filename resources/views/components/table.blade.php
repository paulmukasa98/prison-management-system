@props([
    'striped' => true,
])

<div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm overflow-hidden" {{ $attributes }}>
    <div class="table-responsive">
        <table class="w-full text-sm text-left text-[#0F172A]">
            {{ $slot }}
        </table>
    </div>
</div>
