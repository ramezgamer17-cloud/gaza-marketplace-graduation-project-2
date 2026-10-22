@extends('layouts.app')

@section('title', 'حسابك قيد المراجعة')

@section('content')

    @php
        $profile = auth()->user()->deliveryProfile;
        $isManager = $profile?->type === 'manager';
    @endphp

    <div class="mx-auto max-w-lg">
        <div class="rounded-3xl border border-gray-100 bg-white p-8 text-center">
            <span class="mx-auto grid size-16 place-items-center rounded-full bg-amber-50 text-amber-600">
                <svg class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </span>

            <h1 class="mt-4 text-xl font-extrabold">حسابك بانتظار موافقة الإدارة</h1>
            <p class="mt-2 text-sm leading-6 text-gray-500">
                تم إنشاء حساب {{ $isManager ? 'مدير شركة التوصيل' : 'مندوب التوصيل' }} بنجاح.
                ستقوم الإدارة بمراجعة بياناتك وتفعيل الحساب، ثم يمكنك استلام الطلبات.
            </p>

            @if ($profile)
                <dl class="mt-5 space-y-2 rounded-2xl bg-gray-50 p-4 text-start text-xs">
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500">نوع الحساب</dt>
                        <dd class="font-bold">{{ $profile->type_label }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500">منطقة العمل</dt>
                        <dd class="font-bold">{{ $profile->zone?->name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500">الشركة</dt>
                        <dd class="font-bold">{{ $profile->company?->name ?? 'بدون شركة' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500">الحالة</dt>
                        <dd class="font-bold text-amber-600">قيد المراجعة</dd>
                    </div>
                </dl>
            @endif

            <div class="mt-6 flex flex-col gap-2">
                <a href="{{ route('home') }}" class="rounded-xl bg-primary py-2.5 text-sm font-bold text-white">تصفّح السوق</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-xl border border-gray-200 py-2.5 text-sm font-semibold text-gray-600">تسجيل الخروج</button>
                </form>
            </div>
        </div>
    </div>

@endsection
