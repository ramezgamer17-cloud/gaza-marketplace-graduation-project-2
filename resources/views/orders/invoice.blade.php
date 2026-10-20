@extends('layouts.app')

@section('title', 'فاتورة '.$order->order_number)

@section('content')

    <div class="mx-auto max-w-3xl">

        <div class="mb-4 flex items-center justify-between print:hidden">
            <a href="{{ route('orders.index') }}" class="text-xs font-semibold text-gray-500 hover:text-primary">← عودة إلى طلباتي</a>
            <button onclick="window.print()" class="rounded-xl border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-600 hover:border-primary hover:text-primary">
                طباعة الفاتورة
            </button>
        </div>

        <article class="overflow-hidden rounded-2xl border border-gray-100 bg-white">
            {{-- رأس الفاتورة --}}
            <header class="flex flex-wrap items-start justify-between gap-4 border-b border-gray-100 bg-gray-50/60 p-5">
                <div>
                    <p class="text-[11px] font-semibold text-gray-500">فاتورة إلكترونية</p>
                    <h1 class="mt-1 text-lg font-extrabold text-ink" dir="ltr">{{ $order->order_number }}</h1>
                    <p class="mt-1 text-[11px] text-gray-500">{{ $order->created_at?->format('Y-m-d H:i') }}</p>
                </div>

                <div class="text-end">
                    <span class="inline-block rounded-lg bg-primary/10 px-3 py-1.5 text-xs font-bold text-primary">
                        {{ \App\Models\Order::statusLabels()[$order->status] ?? $order->status }}
                    </span>
                    <p class="mt-2 text-[11px] text-gray-500">
                        {{ $order->payment_method === 'jawwal_pay' ? 'جوال بي' : 'الدفع عند الاستلام' }}
                        · {{ $order->payment_status === 'paid' ? 'مدفوع' : 'غير مدفوع' }}
                    </p>
                </div>
            </header>

            {{-- البائع والزبون --}}
            <div class="grid gap-4 border-b border-gray-100 p-5 sm:grid-cols-2">
                <div>
                    <p class="mb-1 text-[11px] font-bold text-gray-400">المتجر (البائع)</p>
                    <p class="text-sm font-bold text-ink">{{ $order->vendor->vendorProfile->store_name ?? $order->vendor->name }}</p>
                    <p class="mt-0.5 text-[11px] text-gray-500">{{ $order->vendor->vendorProfile->address ?? 'غزة' }}</p>
                    @if ($order->vendor->vendorProfile->phone)
                        <p class="text-[11px] text-gray-500" dir="ltr">{{ $order->vendor->vendorProfile->phone }}</p>
                    @endif
                </div>

                <div>
                    <p class="mb-1 text-[11px] font-bold text-gray-400">الزبون وعنوان التوصيل</p>
                    <p class="text-sm font-bold text-ink">{{ $order->customer_name }}</p>
                    <p class="mt-0.5 text-[11px] text-gray-500" dir="ltr">{{ $order->customer_phone }}</p>
                    <p class="mt-0.5 text-[11px] text-gray-500">{{ $order->shipping_address }}</p>
                    @if ($order->deliveryUser)
                        <p class="mt-1 text-[11px] text-gray-500">المندوب: {{ $order->deliveryUser->name }}</p>
                    @endif
                </div>
            </div>

            {{-- العناصر --}}
            <div class="p-5">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-[11px] text-gray-400">
                            <th class="pb-2 text-start font-semibold">المنتج</th>
                            <th class="pb-2 text-center font-semibold">الكمية</th>
                            <th class="pb-2 text-end font-semibold">السعر</th>
                            <th class="pb-2 text-end font-semibold">المجموع</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="py-2.5 pe-2">{{ $item->product_name }}</td>
                                <td class="py-2.5 text-center">{{ $item->quantity }}</td>
                                <td class="py-2.5 text-end">{{ number_format((float) $item->unit_price, 2) }}</td>
                                <td class="py-2.5 text-end font-semibold">{{ number_format((float) $item->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- الإجماليات --}}
            <div class="border-t border-gray-100 p-5">
                <div class="ms-auto max-w-xs space-y-1.5 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>المجموع</span>
                        <span>{{ number_format((float) $order->subtotal, 2) }} شيكل</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>رسوم التوصيل</span>
                        <span>{{ number_format((float) $order->delivery_fee, 2) }} شيكل</span>
                    </div>
                    <div class="flex justify-between border-t border-gray-100 pt-2 text-base font-extrabold text-primary">
                        <span>الإجمالي</span>
                        <span>{{ number_format((float) $order->total, 2) }} شيكل</span>
                    </div>
                </div>

                @if ($order->notes)
                    <p class="mt-4 rounded-xl bg-gray-50 p-3 text-[11px] text-gray-600">ملاحظات: {{ $order->notes }}</p>
                @endif
            </div>

            <footer class="border-t border-gray-100 bg-gray-50/60 p-4 text-center text-[10px] text-gray-400">
                سوق غزة — فاتورة إلكترونية · رقم الطلب {{ $order->order_number }}
            </footer>
        </article>
    </div>

@endsection
