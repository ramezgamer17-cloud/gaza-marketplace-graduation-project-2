@extends('layouts.app')

@section('title', 'إتمام الشراء')

@section('content')

    <div class="page-head">
        <div>
            <span class="eyebrow">الخطوة الأخيرة</span>
            <h1>إتمام الشراء</h1>
            <p>أكمل بيانات التوصيل ثم انتقل للدفع الآمن.</p>
        </div>
        <a href="{{ route('cart.index') }}" class="btn-ghost">← رجوع إلى السلة</a>
    </div>

    <form method="POST" action="{{ route('checkout.store') }}" class="grid gap-4 lg:grid-cols-3">
        @csrf

        <div class="space-y-4 lg:col-span-2">
            {{-- بيانات التوصيل --}}
            <section class="panel p-5">
                <h2 class="mb-3 text-sm font-extrabold">بيانات التوصيل</h2>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label for="customer_name" class="mb-1 block text-xs font-semibold text-gray-600">الاسم <span class="text-red-500">*</span></label>
                        <input id="customer_name" name="customer_name" value="{{ old('customer_name', $user->name) }}" required
                               class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                        @error('customer_name')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="customer_phone" class="mb-1 block text-xs font-semibold text-gray-600">رقم الجوال <span class="text-red-500">*</span></label>
                        <input id="customer_phone" name="customer_phone" value="{{ old('customer_phone', $user->phone) }}" required dir="ltr"
                               inputmode="numeric" maxlength="10" placeholder="0597784541"
                               pattern="05[0-9]{8}"
                               class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                        <p class="mt-1 text-[10px] text-gray-400">10 أرقام تبدأ بـ 05 — مثال: 0597784541</p>
                        @error('customer_phone')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="delivery_zone_id" class="mb-1 block text-xs font-semibold text-gray-600">منطقة التوصيل <span class="text-red-500">*</span></label>
                        <select id="delivery_zone_id" name="delivery_zone_id" required
                                class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                            <option value="">اختر المنطقة…</option>
                            @foreach ($regions as $region)
                                <option value="{{ $region->id }}" @selected((string) old('delivery_zone_id', $selectedZoneId) === (string) $region->id)>{{ $region->name }}</option>
                            @endforeach
                        </select>
                        @error('delivery_zone_id')<p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="shipping_address" class="mb-1 block text-xs font-semibold text-gray-600">تفاصيل العنوان <span class="text-red-500">*</span></label>
                        <input id="shipping_address" name="shipping_address" value="{{ old('shipping_address') }}" required
                               placeholder="الحي - الشارع - علامة مميزة"
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
            <section class="panel p-5">
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
        <aside class="panel h-fit p-5 lg:sticky lg:top-24">
            <h2 class="mb-3 text-sm font-extrabold">ملخص الطلب</h2>

            <div class="space-y-3">
                @foreach ($rows as $row)
                    <div class="rounded-xl bg-gray-50 p-3" data-vendor-row="{{ $row->vendor->id }}">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-xs font-bold text-ink">{{ $row->vendor->vendorProfile->store_name ?? $row->vendor->name }}</p>
                            <span class="text-[10px] text-gray-400">{{ $row->vendor->vendorProfile?->zone?->name ?? 'بلا منطقة' }}</span>
                        </div>

                        <ul class="mt-2 space-y-1">
                            @foreach ($row->items as $item)
                                <li class="flex items-center justify-between gap-2 text-[11px] text-gray-600">
                                    <span class="truncate">{{ $item->product->name }} × {{ $item->quantity }}</span>
                                    <span class="flex shrink-0 items-center gap-2">
                                        <span class="font-semibold">{{ number_format($item->line_total, 2) }}</span>
                                        <button type="button" data-remove-item="{{ route('cart.remove.post', $item) }}" title="إزالة من السلة"
                                                class="grid size-5 place-items-center rounded-full text-red-400 transition hover:bg-red-50 hover:text-red-600">×</button>
                                    </span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-2 flex items-center justify-between border-t border-gray-100 pt-1.5 text-[11px]">
                            <span class="text-gray-500">التوصيل</span>
                            <span class="font-bold text-gray-700" data-fee="{{ $row->vendor->id }}">
                                @if (! $row->has_zone)
                                    —
                                @elseif ($row->fee === null)
                                    {{ $selectedZoneId ? 'غير متاح' : 'حدد المنطقة' }}
                                @else
                                    {{ number_format($row->fee, 2) }} شيكل
                                @endif
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 space-y-1.5 border-t border-gray-100 pt-3 text-xs">
                <div class="flex items-center justify-between text-gray-500">
                    <span>المنتجات</span>
                    <span data-subtotal>{{ number_format($subtotal, 2) }} شيكل</span>
                </div>
                <div class="flex items-center justify-between text-gray-500">
                    <span>التوصيل</span>
                    <span data-delivery-total>{{ number_format($deliveryTotal, 2) }} شيكل</span>
                </div>
                <div class="flex items-center justify-between pt-1 text-base font-extrabold text-primary">
                    <span>الإجمالي</span>
                    <span data-grand-total>{{ number_format($grandTotal, 2) }} شيكل</span>
                </div>
            </div>

            <p class="mt-1 text-[10px] text-gray-400">فاتورة مستقلة لكل متجر · {{ $rows->count() }} متجر</p>

            <button type="submit" class="btn-green mt-4 w-full">
                تأكيد الطلب
            </button>
        </aside>
    </form>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const zoneSelect = document.getElementById('delivery_zone_id');
            if (!zoneSelect) return;

            const money = (value) => Number(value).toFixed(2) + ' شيكل';

            const refresh = async () => {
                if (!zoneSelect.value) return;

                try {
                    const response = await fetch('{{ route('checkout.fees') }}?zone_id=' + encodeURIComponent(zoneSelect.value), {
                        headers: { 'Accept': 'application/json' },
                    });

                    if (!response.ok) return;
                    const data = await response.json();

                    data.rows.forEach((row) => {
                        const feeEl = document.querySelector('[data-fee="' + row.vendor_id + '"]');
                        if (!feeEl) return;
                        feeEl.textContent = row.fee === null ? 'غير متاح' : money(row.fee);
                    });

                    document.querySelector('[data-delivery-total]').textContent = money(data.delivery_total);
                    document.querySelector('[data-grand-total]').textContent = money(data.grand_total);
                } catch (e) {
                    // تجاهل أخطاء الشبكة؛ تبقى القيم المحسوبة على الخادم عند الإرسال
                }
            };

            zoneSelect.addEventListener('change', refresh);
            if (zoneSelect.value) refresh();

            // حفظ بيانات النموذج محليًا حتى لا يضيع ما أدخله المستخدم عند الرجوع
            const fields = ['customer_name', 'customer_phone', 'delivery_zone_id', 'shipping_address', 'notes'];
            const storageKey = 'checkout-form';

            try {
                const saved = JSON.parse(localStorage.getItem(storageKey) || '{}');

                fields.forEach((id) => {
                    const el = document.getElementById(id);
                    if (!el) return;
                    if (saved[id] !== undefined && saved[id] !== '') {
                        el.value = saved[id];
                        if (id === 'delivery_zone_id') {
                            el.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }
                });
            } catch (e) {
                // تجاهل
            }

            const persist = () => {
                const data = {};
                fields.forEach((id) => {
                    const el = document.getElementById(id);
                    if (el) data[id] = el.value;
                });
                try { localStorage.setItem(storageKey, JSON.stringify(data)); } catch (e) {}
            };

            fields.forEach((id) => {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('input', persist);
                    el.addEventListener('change', persist);
                }
            });

            // حذف منتج من الملخص بدون كسر نموذج الدفع (لا نماذج متداخلة)
            document.querySelectorAll('[data-remove-item]').forEach((button) => {
                button.addEventListener('click', async () => {
                    const url = button.getAttribute('data-remove-item');
                    button.disabled = true;

                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'text/html',
                            },
                        });

                        if (response.ok) {
                            window.location.reload();
                        } else {
                            button.disabled = false;
                        }
                    } catch (e) {
                        button.disabled = false;
                    }
                });
            });
        });
    </script>
@endpush
