@extends('layouts.app')

@section('title', 'طلباتي')

@section('content')

    <h1 class="mb-5 text-xl font-extrabold">طلباتي وفواتيري</h1>

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
                   class="flex items-center gap-3 rounded-2xl border border-gray-100 bg-white p-4 transition hover:border-primary/30 hover:shadow-sm">
                    <span class="grid size-12 shrink-0 place-items-center overflow-hidden rounded-xl bg-primary/10 text-base font-extrabold text-primary"
                          @if ($order->vendor->vendorProfile?->logo) style="background-image:url('{{ Storage::disk('public')->url($order->vendor->vendorProfile->logo) }}');background-size:cover;background-position:center" @endif>
                        @unless ($order->vendor->vendorProfile?->logo){{ mb_substr($order->vendor->vendorProfile->store_name ?? $order->vendor->name, 0, 1) }}@endunless
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-ink">{{ $order->vendor->vendorProfile->store_name ?? $order->vendor->name }}</p>
                        <p class="mt-0.5 text-[11px] text-gray-500" dir="ltr">{{ $order->order_number }}</p>
                    </div>

                    <div class="text-end">
                        <p class="text-sm font-extrabold text-primary">{{ number_format((float) $order->total, 2) }} شيكل</p>
                        <span class="mt-1 inline-block rounded-lg px-2 py-0.5 text-[10px] font-bold {{ $colors[$order->status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ \App\Models\Order::statusLabels()[$order->status] ?? $order->status }}
                        </span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-5">{{ $orders->links() }}</div>
    @endif

@endsection
