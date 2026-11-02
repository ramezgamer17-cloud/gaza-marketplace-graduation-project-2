@extends('layouts.app')

@section('title', 'تتبّع الطلب '.$order->order_number)

@section('content')

    @php
        $steps = [
            'pending' => ['label' => 'تم إنشاء الطلب', 'icon' => '🧾', 'hint' => 'بانتظار تأكيد المتجر'],
            'confirmed' => ['label' => 'تم تأكيد الطلب', 'icon' => '✅', 'hint' => 'المتجر استلم طلبك'],
            'preparing' => ['label' => 'قيد التجهيز عند البائع', 'icon' => '👨‍🍳', 'hint' => 'يتم تجهيز طلبك الآن'],
            'ready' => ['label' => 'تم تجهيز الطلب', 'icon' => '📦', 'hint' => 'الطلب جاهز وبانتظار المندوب'],
            'shipped' => ['label' => 'في الطريق إليك', 'icon' => '🚚', 'hint' => 'المندوب في طريقه للتوصيل'],
            'delivered' => ['label' => 'تم التوصيل', 'icon' => '🏠', 'hint' => 'وصل طلبك بنجاح'],
        ];

        $status = $order->status;
        $isCancelled = $status === 'cancelled';
        $keys = array_keys($steps);
        $currentIndex = array_search($status, $keys, true);
        $currentIndex = $currentIndex === false ? 0 : $currentIndex;
    @endphp

    <div class="page-head">
        <div>
            <span class="eyebrow">تتبّع مباشر</span>
            <h1>طلب {{ $order->order_number }}</h1>
            <p>من {{ $order->vendor->vendorProfile?->store_name ?? $order->vendor->name }} · {{ number_format((float) $order->total, 2) }} شيكل</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('orders.show', $order) }}" class="btn-ghost">الفواتير</a>
            <a href="{{ route('orders.index') }}" class="btn-ghost">← طلباتي</a>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        {{-- الخط الزمني --}}
        <section class="panel p-5 lg:col-span-2">
            @if ($isCancelled)
                <div class="mb-5 rounded-xl bg-red-50 px-4 py-3 text-sm font-bold text-red-600">
                    تم إلغاء هذا الطلب.
                </div>
            @else
                <div class="mb-5 flex items-center justify-between gap-2">
                    <h2 class="text-sm font-extrabold text-ink">حالة الطلب</h2>
                    <span class="rounded-full bg-primary/10 px-3 py-1 text-[11px] font-extrabold text-primary">
                        {{ \App\Models\Order::statusLabels()[$status] ?? $status }}
                    </span>
                </div>
            @endif

            <ol class="tracking-steps">
                @foreach ($steps as $key => $step)
                    @php
                        $index = $loop->index;
                        $done = $index <= $currentIndex && ! $isCancelled;
                        $active = $index === $currentIndex && ! $isCancelled;
                    @endphp

                    <li class="tracking-step {{ $done ? 'is-done' : '' }} {{ $active ? 'is-active' : '' }}">
                        <span class="tracking-node">{{ $step['icon'] }}</span>
                        <div class="tracking-copy">
                            <p class="tracking-label">{{ $step['label'] }}</p>
                            <p class="tracking-hint">{{ $step['hint'] }}</p>
                            @if ($active)
                                <span class="tracking-badge">الحالة الحالية</span>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>

            <div class="mt-6 rounded-xl bg-gray-50 p-4 text-[11px] text-gray-500">
                <p>تاريخ إنشاء الطلب: <strong class="text-ink">{{ $order->created_at?->format('Y-m-d H:i') }}</strong></p>
                <p class="mt-1">آخر تحديث: <strong class="text-ink">{{ $order->updated_at?->format('Y-m-d H:i') }}</strong></p>
            </div>
        </section>

        {{-- بطاقة التوصيل --}}
        <aside class="space-y-4">
            <section class="panel p-5">
                <h2 class="text-sm font-extrabold text-ink">عنوان التوصيل</h2>
                <p class="mt-2 text-xs leading-6 text-gray-600">
                    {{ $order->shipping_address }}
                    @if ($order->deliveryZone)
                        <br><span class="text-gray-400">المنطقة: {{ $order->deliveryZone->name }}</span>
                    @endif
                </p>

                @if ($order->delivery_company_id || $order->delivery_user_id)
                    <div class="mt-4 space-y-2 border-t pt-3 text-xs" style="border-color:#eef3f0">
                        @if ($order->deliveryCompany)
                            <p class="flex justify-between"><span class="text-gray-500">شركة التوصيل</span><strong class="text-ink">{{ $order->deliveryCompany->name }}</strong></p>
                        @endif
                        @if ($order->deliveryUser)
                            <p class="flex justify-between"><span class="text-gray-500">المندوب</span><strong class="text-ink">{{ $order->deliveryUser->name }}</strong></p>
                            @if ($order->deliveryUser->phone)
                                <p class="flex justify-between"><span class="text-gray-500">جوال المندوب</span><a dir="ltr" href="tel:{{ $order->deliveryUser->phone }}" class="font-bold text-primary">{{ $order->deliveryUser->phone }}</a></p>
                            @endif
                        @endif
                    </div>
                @else
                    <p class="mt-4 rounded-lg bg-amber-50 px-3 py-2 text-[11px] font-semibold text-amber-700">
                        لم يُسند مندوب لهذا الطلب بعد.
                    </p>
                @endif
            </section>

            <section class="panel p-5">
                <h2 class="text-sm font-extrabold text-ink">ملخص الطلب</h2>

                <ul class="mt-3 space-y-2">
                    @foreach ($order->items as $item)
                        <li class="flex items-center justify-between gap-2 text-[11px] text-gray-600">
                            <span class="truncate">{{ $item->product_name }} × {{ $item->quantity }}</span>
                            <span class="shrink-0 font-bold">{{ number_format((float) $item->total, 2) }}</span>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-3 space-y-1 border-t pt-3 text-[11px]" style="border-color:#eef3f0">
                    <div class="flex justify-between text-gray-500"><span>المنتجات</span><span>{{ number_format((float) $order->subtotal, 2) }} شيكل</span></div>
                    <div class="flex justify-between text-gray-500"><span>التوصيل</span><span>{{ number_format((float) $order->delivery_fee, 2) }} شيكل</span></div>
                    <div class="flex justify-between pt-1 text-sm font-extrabold text-primary"><span>الإجمالي</span><span>{{ number_format((float) $order->total, 2) }} شيكل</span></div>
                </div>
            </section>
        </aside>
    </div>

@endsection
