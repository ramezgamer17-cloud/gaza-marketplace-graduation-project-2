@extends('layouts.app')

@section('title', 'طلباتي')

@section('content')

    <div class="page-head">
        <div>
            <span class="eyebrow">تابع كل طلباتك</span>
            <h1>طلباتي وفواتيري</h1>
            <p>{{ $orders->total() }} طلب · فاتورة مستقلة لكل متجر</p>
        </div>
        <a href="{{ route('home') }}" class="btn-ghost">← متابعة التسوق</a>
    </div>

    @if ($orders->isEmpty())
        <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-12 text-center">
            <p class="text-sm font-semibold text-gray-600">لا توجد طلبات بعد.</p>
            <a href="{{ route('home') }}" class="mt-4 inline-block rounded-xl bg-primary px-4 py-2 text-sm font-bold text-white">ابدأ التسوّق</a>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($orders as $order)
                @php
                    $colors = [
                        'pending' => 'bg-amber-50 text-amber-700',
                        'confirmed' => 'bg-sky-50 text-sky-700',
                        'preparing' => 'bg-sky-50 text-sky-700',
                        'shipped' => 'bg-indigo-50 text-indigo-700',
                        'delivered' => 'bg-emerald-50 text-emerald-700',
                        'cancelled' => 'bg-red-50 text-red-600',
                    ];
                @endphp

                <a href="{{ route('orders.show', $order) }}"
                   class="panel flex items-center gap-3 p-4 transition hover:-translate-y-0.5 hover:shadow-md no-underline">
                    <span class="store-logo green" style="flex:0 0 48px;height:48px;border-radius:15px;font-size:18px"
                          @if ($order->vendor->vendorProfile?->logo) style="background-image:url('{{ Storage::disk('public')->url($order->vendor->vendorProfile->logo) }}');background-size:cover;background-position:center" @endif>
                        @if ($order->vendor->vendorProfile?->logo)
                            <img src="{{ Storage::disk('public')->url($order->vendor->vendorProfile->logo) }}" alt="" class="size-full object-cover">
                        @else
                            {{ mb_substr($order->vendor->vendorProfile->store_name ?? $order->vendor->name, 0, 1) }}
                        @endif
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-ink">{{ $order->vendor->vendorProfile->store_name ?? $order->vendor->name }}</p>
                        <p class="mt-0.5 text-[11px] text-gray-500" dir="ltr">{{ $order->order_number }}</p>
                    </div>

                    <div class="text-end">
                        <p class="text-sm font-extrabold text-primary">{{ number_format((float) $order->total, 2) }} شيكل</p>
                        <span class="mt-1 inline-block rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $colors[$order->status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ \App\Models\Order::statusLabels()[$order->status] ?? $order->status }}
                        </span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-5">{{ $orders->links() }}</div>
    @endif

@endsection
