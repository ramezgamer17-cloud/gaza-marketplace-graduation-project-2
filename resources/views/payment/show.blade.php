@extends('layouts.app')

@section('title', 'الدفع — جوال بي')

@section('content')
    <div class="mx-auto max-w-lg">
        <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <p class="text-[11px] text-gray-400">الدفع عبر</p>
                    <p class="text-lg font-extrabold text-primary">جوال بي</p>
                </div>
                <span class="rounded-xl bg-primary/10 px-3 py-1.5 text-xs font-bold text-primary" dir="ltr">{{ $order->order_number }}</span>
            </div>

            <div class="mb-5 rounded-2xl bg-gray-50 p-4">
                <div class="flex justify-between text-xs text-gray-500"><span>المتجر</span><span class="font-bold text-ink">{{ $order->vendor->vendorProfile?->store_name ?? $order->vendor->name }}</span></div>
                <div class="mt-1 flex justify-between text-xs text-gray-500"><span>المنتجات</span><span class="font-bold text-ink">{{ number_format((float) $order->subtotal, 2) }} شيكل</span></div>
                <div class="mt-1 flex justify-between text-xs text-gray-500"><span>التوصيل</span><span class="font-bold text-ink">{{ number_format((float) $order->delivery_fee, 2) }} شيكل</span></div>
                <div class="mt-2 flex justify-between border-t border-gray-200 pt-2 text-sm font-extrabold"><span>الإجمالي</span><span class="text-primary">{{ number_format((float) $order->total, 2) }} شيكل</span></div>
            </div>

            @if ($order->payment_status === 'paid')
                <div class="rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">تم دفع هذه الفاتورة بالفعل.</div>
                <a href="{{ route('orders.show', $order) }}" class="mt-4 block rounded-xl bg-primary py-2.5 text-center text-sm font-bold text-white">عرض الفاتورة</a>
            @else
                <form method="POST" action="{{ route('payment.send', $order) }}" class="space-y-3">
                    @csrf

                    <div>
                        <label for="payment_phone" class="mb-1 block text-xs font-semibold text-gray-600">رقم محفظة جوال بي <span class="text-red-500">*</span></label>
                        <input id="payment_phone" name="payment_phone" value="{{ old('payment_phone', $order->payment_phone) }}"
                               dir="ltr" inputmode="numeric" maxlength="10" placeholder="0597784541" required
                               class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-left text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                        <p class="mt-1 text-[10px] text-gray-400">10 أرقام تبدأ بـ 05</p>
                        @error('payment_phone')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-primary py-3 text-sm font-bold text-white hover:bg-primary-600">
                        متابعة الدفع
                    </button>
                </form>

                <a href="{{ route('orders.index') }}" class="mt-3 block text-center text-[11px] font-semibold text-gray-500 hover:text-primary">
                    تخطّي الدفع الآن — عرض طلباتي
                </a>

                <p class="mt-3 text-center text-[10px] text-gray-400">صفحة تجريبية — لا يتم خصم أي مبالغ حقيقية.</p>
            @endif
        </div>
    </div>
@endsection
