@extends('layouts.app')

@section('title', 'إتمام الشراء')

@section('content')

    <h1 class="mb-5 text-xl font-extrabold">إتمام الشراء</h1>

    <form method="POST" action="{{ route('checkout.store') }}" class="grid gap-4 lg:grid-cols-3">
        @csrf

        <div class="space-y-4 lg:col-span-2">
            {{-- بيانات التوصيل --}}
            <section class="rounded-2xl border border-gray-100 bg-white p-4">
                <h2 class="mb-3 text-sm font-extrabold">بيانات التوصيل</h2>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label for="customer_name" class="mb-1 block text-xs font-semibold text-gray-600">الاسم</label>
                        <input id="customer_name" name="customer_name" value="{{ old('customer_name', $user->name) }}" required
                               class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                        @error('customer_name')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="customer_phone" class="mb-1 block text-xs font-semibold text-gray-600">رقم الجوال</label>
                        <input id="customer_phone" name="customer_phone" value="{{ old('customer_phone', $user->phone) }}" required dir="ltr"
                               class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                        @error('customer_phone')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="shipping_address" class="mb-1 block text-xs font-semibold text-gray-600">عنوان التوصيل</label>
                        <input id="shipping_address" name="shipping_address" value="{{ old('shipping_address', $user->address) }}" required
                               placeholder="مثال: غزة - الرمال - شارع الجلاء"
                               class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                        @error('shipping_address')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="notes" class="mb-1 block text-xs font-semibold text-gray-600">ملاحظات (اختياري)</label>
                        <textarea id="notes" name="notes" rows="2"
                                  class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </section>

            {{-- طريقة الدفع --}}
            <section class="rounded-2xl border border-gray-100 bg-white p-4">
                <h2 class="mb-3 text-sm font-extrabold">طريقة الدفع</h2>

                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-primary/30 bg-primary/5 px-3 py-3">
                    <input type="radio" name="payment_method" value="jawwal_pay" checked class="size-4 text-primary focus:ring-primary">
                    <span class="flex-1">
                        <span class="block text-sm font-bold text-ink">جوال بي (Jawwal Pay)</span>
                        <span class="block text-[11px] text-gray-500">سداد إلكتروني آمن عبر محفظتك.</span>
                    </span>
                    <span class="rounded-lg bg-primary px-2 py-1 text-[10px] font-bold text-white">موصى به</span>
                </label>

                <label class="mt-2 flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 px-3 py-3">
                    <input type="radio" name="payment_method" value="cash_on_delivery" class="size-4 text-primary focus:ring-primary">
                    <span class="flex-1">
                        <span class="block text-sm font-bold text-ink">الدفع عند الاستلام</span>
                        <span class="block text-[11px] text-gray-500">تدفع للمندوب عند وصول الطلب.</span>
                    </span>
                </label>
            </section>
        </div>

        {{-- ملخص الطلب --}}
        <aside class="h-fit rounded-2xl border border-gray-100 bg-white p-4">
            <h2 class="mb-3 text-sm font-extrabold">ملخص الطلب</h2>

            <div class="space-y-3">
                @foreach ($groups as $group)
                    <div class="rounded-xl bg-gray-50 p-3">
                        <p class="text-xs font-bold text-ink">{{ $group->vendor->vendorProfile->store_name ?? $group->vendor->name }}</p>
                        <ul class="mt-2 space-y-1">
                            @foreach ($group->items as $item)
                                <li class="flex items-center justify-between gap-2 text-[11px] text-gray-600">
                                    <span class="truncate">{{ $item->product->name }} × {{ $item->quantity }}</span>
                                    <span class="shrink-0 font-semibold">{{ number_format($item->line_total, 2) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3">
                <span class="text-xs text-gray-500">الإجمالي</span>
                <span class="text-lg font-extrabold text-primary">{{ number_format($grandTotal, 2) }} شيكل</span>
            </div>

            <p class="mt-1 text-[10px] text-gray-400">فاتورة مستقلة لكل متجر · {{ $groups->count() }} متجر</p>

            <button type="submit"
                    class="mt-4 w-full rounded-xl bg-primary py-3 text-sm font-bold text-white transition hover:bg-primary-600">
                تأكيد الطلب
            </button>
        </aside>
    </form>

@endsection
