@php
    $pendingStore = auth()->user()?->vendorProfile;
@endphp

@if ($pendingStore && ! $pendingStore->is_active)
    <div class="mx-4 mt-4 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800 sm:mx-6">
        <svg style="width:18px;height:18px" class="shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
        </svg>
        <p class="text-xs font-semibold leading-6">
            متجرك «{{ $pendingStore->store_name }}» بانتظار موافقة الإدارة — يمكنك تجهيز منتجاتك الآن،
            لكنه لن يظهر في السوق حتى يتم اعتماده.
        </p>
    </div>
@endif
