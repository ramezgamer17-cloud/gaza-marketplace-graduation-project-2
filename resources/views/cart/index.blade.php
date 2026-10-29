@extends('layouts.app')

@section('title', 'سلة الشراء')

@section('content')

    <div class="page-head">
        <div>
            <span class="eyebrow">خطوة واحدة تفصلك عن طلبك</span>
            <h1>سلة الشراء</h1>
            <p>{{ $groups->count() }} متجر · {{ $groups->sum(fn ($g) => $g->items->sum('quantity')) }} قطعة</p>
        </div>

        @if ($groups->isNotEmpty())
            <form method="POST" action="{{ route('cart.clear') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs font-semibold text-red-500 hover:text-red-600">تفريغ السلة</button>
            </form>
        @endif
    </div>

    @if ($groups->isEmpty())
        <div class="panel" style="padding:48px 20px;text-align:center">
            <span style="font-size:44px">🛒</span>
            <p class="mt-3 text-sm font-bold text-ink">سلتك فارغة حاليًا</p>
            <p class="mt-1 text-xs text-gray-500">ابدأ التسوق من متاجر غزة القريبة منك.</p>
            <a href="{{ route('home') }}" class="btn-green" style="display:inline-flex;margin-top:18px">تصفّح المنتجات</a>
        </div>
    @else
        <div class="grid gap-4 lg:grid-cols-3">
            {{-- المتاجر --}}
            <div class="space-y-4 lg:col-span-2">
                @foreach ($groups as $group)
                    <section class="panel overflow-hidden" data-store="{{ $group->vendor->id }}">
                        <header class="flex items-center justify-between gap-3 px-4 py-3.5" style="border-bottom:1px solid #eef3f0">
                            <a href="{{ route('stores.show', $group->vendor->vendorProfile) }}" class="flex items-center gap-2.5 no-underline">
                                <span class="store-logo green" style="flex:0 0 40px;height:40px;border-radius:13px;font-size:16px"
                                      @if ($group->vendor->vendorProfile?->logo) data-has-logo @endif>
                                    @if ($group->vendor->vendorProfile?->logo)
                                        <img src="{{ Storage::disk('public')->url($group->vendor->vendorProfile->logo) }}" alt="">
                                    @else
                                        {{ mb_substr($group->vendor->vendorProfile->store_name ?? $group->vendor->name, 0, 1) }}
                                    @endif
                                </span>
                                <span>
                                    <span class="block text-sm font-extrabold text-ink">{{ $group->vendor->vendorProfile->store_name ?? $group->vendor->name }}</span>
                                    <span class="block text-[11px] text-gray-500">{{ $group->vendor->vendorProfile?->zone?->name ?? 'غزة' }} · {{ $group->items->count() }} منتج</span>
                                </span>
                            </a>

                            <span class="text-sm font-extrabold text-primary" data-store-subtotal>{{ number_format($group->subtotal, 2) }} شيكل</span>
                        </header>

                        <ul>
                            @foreach ($group->items as $item)
                                <li class="flex items-center gap-3 px-4 py-3" style="border-top:1px solid #f6f9f7"
                                    data-item="{{ $item->id }}"
                                    data-max="{{ (int) $item->product->stock }}"
                                    data-name="{{ $item->product->name }}"
                                    data-update-url="{{ route('cart.update', $item) }}"
                                    data-remove-url="{{ route('cart.remove', $item) }}">
                                    <span class="grid size-16 shrink-0 place-items-center overflow-hidden rounded-2xl bg-primary-50 text-lg font-extrabold text-primary/40">
                                        @if ($item->product->thumbnail)
                                            <img src="{{ Storage::disk('public')->url($item->product->thumbnail) }}" alt="{{ $item->product->name }}" class="size-full object-cover">
                                        @else
                                            {{ mb_substr($item->product->name, 0, 1) }}
                                        @endif
                                    </span>

                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-bold text-ink">{{ $item->product->name }}</p>
                                        <p class="mt-0.5 text-[11px] text-gray-500">{{ number_format((float) $item->product->price, 2) }} شيكل للوحدة</p>
                                        <button type="button" class="mt-1 text-[11px] font-semibold text-red-400 hover:text-red-600" data-cart-remove>إزالة</button>
                                    </div>

                                    <div class="flex items-center gap-1.5">
                                        <button type="button" class="qty-btn" data-cart-step="-1" aria-label="إنقاص">−</button>
                                        <span class="qty-value" data-qty>{{ $item->quantity }}</span>
                                        <button type="button" class="qty-btn" data-cart-step="1" aria-label="زيادة">+</button>
                                    </div>

                                    <p class="w-20 text-end text-sm font-extrabold text-ink" data-line-total>{{ number_format($item->line_total, 2) }}</p>
                                </li>
                            @endforeach
                        </ul>

                        <footer class="flex items-center justify-between px-4 py-2.5 text-[11px] text-gray-500" style="border-top:1px solid #f6f9f7">
                            <span>تُحسب حسب منطقتك عند إتمام الطلب</span>
                            <span>إجمالي المتجر: <strong class="text-ink" data-store-subtotal>{{ number_format($group->subtotal, 2) }} شيكل</strong></span>
                        </footer>
                    </section>
                @endforeach
            </div>

            {{-- الملخص --}}
            <aside class="panel h-fit p-5 lg:sticky lg:top-24">
                <h2 class="text-sm font-extrabold text-ink">ملخص السلة</h2>

                <div class="mt-4 space-y-2 text-xs">
                    <div class="flex justify-between text-gray-500"><span>المنتجات</span><span data-cart-subtotal>{{ number_format($cart->grand_total, 2) }} شيكل</span></div>
                    <div class="flex justify-between text-gray-500"><span>التوصيل</span><span class="text-gray-400">يُحدد عند اختيار المنطقة</span></div>
                </div>

                <div class="mt-3 flex items-center justify-between border-t pt-3" style="border-color:#eef3f0">
                    <span class="text-sm font-bold">الإجمالي</span>
                    <span class="text-xl font-extrabold text-primary" data-cart-total>{{ number_format($cart->grand_total, 2) }} شيكل</span>
                </div>

                <a href="{{ route('checkout.index') }}" class="btn-green mt-4 w-full">متابعة إلى الدفع</a>

                <p class="mt-3 text-center text-[10px] text-gray-400">فاتورة مستقلة لكل متجر · دفع آمن عبر جوال بي</p>
                <a href="{{ route('home') }}" class="mt-2 block text-center text-[11px] font-semibold text-gray-500 hover:text-primary">← متابعة التسوق</a>
            </aside>
        </div>
    @endif

@endsection

@push('scripts')
    <script>
        (function () {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            const money = (value) => Number(value).toFixed(2) + ' شيكل';

            function toastMessage(message, isError) {
                window.dispatchEvent(new CustomEvent('cart-toast', { detail: { message, error: isError } }));
            }

            function setBadge(count) {
                const cartLink = document.querySelector('.cart-action');
                if (!cartLink) return;
                let b = cartLink.querySelector('b');
                if (count > 0) {
                    if (!b) { b = document.createElement('b'); cartLink.appendChild(b); }
                    b.textContent = count;
                } else if (b) { b.remove(); }
            }

            function flash(el) {
                if (!el) return;
                el.classList.remove('flash-update');
                void el.offsetWidth;
                el.classList.add('flash-update');
            }

            function applyState(data, row) {
                if (data.item) {
                    row.querySelector('[data-qty]').textContent = data.item.quantity;
                    const line = row.querySelector('[data-line-total]');
                    line.textContent = money(data.item.line_total);
                    flash(line);
                }

                const store = row.closest('[data-store]');
                if (store && data.store) {
                    store.querySelectorAll('[data-store-subtotal]').forEach((el) => {
                        el.textContent = money(data.store.subtotal);
                    });
                }

                document.querySelectorAll('[data-cart-total], [data-cart-subtotal]').forEach((el) => {
                    el.textContent = money(data.total);
                });
                flash(document.querySelector('[data-cart-total]'));
                setBadge(data.count);
            }

            function collapse(row) {
                row.style.maxHeight = row.offsetHeight + 'px';
                row.style.overflow = 'hidden';
                row.style.transition = 'opacity .22s, transform .22s, max-height .28s, padding .28s';
                requestAnimationFrame(() => {
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(-30px)';
                    row.style.maxHeight = '0px';
                    row.style.paddingTop = '0px';
                    row.style.paddingBottom = '0px';
                });
                setTimeout(() => {
                    row.remove();
                    if (!document.querySelector('[data-item]')) window.location.reload();
                }, 320);
            }

            async function send(url, fields) {
                const body = new FormData();
                body.append('_token', token);
                Object.entries(fields).forEach(([key, value]) => body.append(key, value));

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body,
                });

                return response.json().catch(() => ({ ok: false, message: 'حدث خطأ غير متوقع، حاول مجددًا.' }));
            }

            async function removeRow(row) {
                const data = await send(row.dataset.removeUrl, { _method: 'DELETE' });

                if (!data.ok) { toastMessage(data.message || 'تعذّر الحذف', true); return; }

                const store = row.closest('[data-store]');
                if (store && data.store) {
                    store.querySelectorAll('[data-store-subtotal]').forEach((el) => el.textContent = money(data.store.subtotal));
                }

                document.querySelectorAll('[data-cart-total], [data-cart-subtotal]').forEach((el) => {
                    el.textContent = money(data.total);
                });
                setBadge(data.count);
                collapse(row);
                toastMessage('تم حذف المنتج من السلة');
            }

            document.querySelectorAll('[data-cart-step]').forEach((button) => {
                button.addEventListener('click', async () => {
                    const row = button.closest('[data-item]');
                    if (!row) return;

                    const current = parseInt(row.querySelector('[data-qty]').textContent, 10) || 0;
                    const max = parseInt(row.dataset.max, 10) || 0;
                    const delta = parseInt(button.dataset.cartStep, 10);
                    const target = current + delta;

                    if (target < 1) {
                        if (current <= 1 && window.confirm('هل أنت متأكد من إزالة هذا المنتج من السلة؟')) {
                            row.querySelectorAll('button').forEach((b) => (b.disabled = true));
                            await removeRow(row);
                        }
                        return;
                    }

                    if (max > 0 && target > max) {
                        toastMessage('وصلت إلى الحد الأقصى المتوفر من «' + row.dataset.name + '» (' + max + ' قطعة).', true);
                        return;
                    }

                    row.querySelectorAll('[data-cart-step]').forEach((b) => (b.disabled = true));

                    const data = await send(row.dataset.updateUrl, { _method: 'PATCH', quantity: target });

                    if (data.ok) {
                        applyState(data, row);
                    } else {
                        toastMessage(data.message || 'تعذّر تحديث الكمية', true);
                    }

                    row.querySelectorAll('[data-cart-step]').forEach((b) => (b.disabled = false));
                });
            });

            document.querySelectorAll('[data-cart-remove]').forEach((button) => {
                button.addEventListener('click', async () => {
                    const row = button.closest('[data-item]');
                    if (!row) return;
                    if (!window.confirm('هل أنت متأكد من إزالة هذا المنتج من السلة؟')) return;

                    button.disabled = true;
                    await removeRow(row);
                });
            });

            window.addEventListener('cart-toast', (event) => {
                const toast = document.getElementById('mini-toast');
                if (!toast) return;
                toast.textContent = event.detail.message;
                toast.style.background = event.detail.error ? '#b3261e' : '#043b2d';
                toast.style.opacity = '1';
                toast.style.transform = 'translate(-50%, 0)';
                clearTimeout(window.__cartToastTimer);
                window.__cartToastTimer = setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translate(-50%, 20px)';
                }, 2600);
            });
        })();
    </script>
@endpush