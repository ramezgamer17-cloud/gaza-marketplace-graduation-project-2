@extends('layouts.app')

@section('title', 'رمز التحقق — جوال بي')

@section('content')
    <div class="mx-auto max-w-lg">
        <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="mb-4 text-center">
                <span class="mx-auto grid size-14 place-items-center rounded-full bg-primary/10 text-2xl text-primary">📩</span>
                <h1 class="mt-3 text-lg font-extrabold">أدخل رمز التحقق</h1>
                <p class="mt-1 text-xs text-gray-500">
                    أرسلنا رمزًا إلى محفظة <span dir="ltr" class="font-bold">{{ $order->payment_phone }}</span>
                </p>
            </div>

            @if (session('test_code'))
                <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-center text-xs text-amber-800">
                    <p class="font-bold">وضع التجربة — الرمز هو:</p>
                    <p class="mt-1 text-2xl font-extrabold tracking-widest" dir="ltr">{{ session('test_code') }}</p>
                    <p class="mt-1 text-[10px]">في الإنتاج يُرسل عبر رسالة SMS.</p>
                </div>
            @endif

            <form method="POST" action="{{ route('payment.verify.submit', $order) }}" class="space-y-3">
                @csrf

                <div>
                    <label for="code" class="mb-1 block text-xs font-semibold text-gray-600">رمز التحقق <span class="text-red-500">*</span></label>
                    <input id="code" name="code" inputmode="numeric" maxlength="10" required autofocus
                           dir="ltr" placeholder="••••"
                           class="w-full rounded-xl border border-gray-200 px-3 py-3 text-center text-xl font-extrabold tracking-[0.4em] outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                    @error('code')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="w-full rounded-xl bg-primary py-3 text-sm font-bold text-white hover:bg-primary-600">
                    تأكيد الدفع
                </button>
            </form>

            <div class="mt-4 flex items-center justify-between text-[11px]">
                <a href="{{ route('payment.show', $order) }}" class="text-gray-500 hover:text-primary">← تغيير رقم المحفظة</a>
                <span class="text-gray-400">{{ $order->order_number }}</span>
            </div>
        </div>
    </div>
@endsection
