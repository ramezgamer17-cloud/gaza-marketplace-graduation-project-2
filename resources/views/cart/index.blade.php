@extends('layouts.app')

@section('title', 'سلة الشراء')

@section('content')

    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-xl font-extrabold">سلة الشراء</h1>
        @if ($groups->isNotEmpty())
            <form method="POST" action="{{ route('cart.clear') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs font-semibold text-red-500 hover:text-red-600">تفريغ السلة</button>
            </form>
        @endif
    </div>

    @if ($groups->isEmpty())
        <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-12 text-center">
            <p class="text-sm font-semibold text-gray-600">سلتك فارغة حاليًا.</p>
            <a href="{{ route('home') }}" class="mt-4 inline-block rounded-xl bg-primary px-4 py-2 text-sm font-bold text-white">
                تصفّح المنتجات
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($groups as $group)
                {{-- مجموعة كل بائع --}}
                <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white">
                    <header class="flex items-center justify-between gap-3 border-b border-gray-50 bg-gray-50/60 px-4 py-3">
                        <a href="{{ route('stores.show', $group->vendor->vendorProfile) }}" class="flex items-center gap-2">
                            <span class="grid size-9 place-items-center overflow-hidden rounded-xl bg-primary/10 text-sm font-extrabold text-primary"
                                  @if ($group->vendor->vendorProfile?->logo) style="background-image:url('{{ Storage::disk('public')->url($group->vendor->vendorProfile->logo) }}');background-size:cover;background-position:center" @endif>
                                @unless ($group->vendor->vendorProfile?->logo){{ mb_substr($group->vendor->vendorProfile->store_name ?? $group->vendor->name, 0, 1) }}@endunless
                            </span>
                            <span>
                                <span class="block text-sm font-bold text-ink">{{ $group->vendor->vendorProfile->store_name ?? $group->vendor->name }}</span>
                                <span class="block text-[11px] text-gray-500">{{ $group->items->count() }} منتج</span>
                            </span>
                        </a>

                        <span class="text-sm font-extrabold text-primary">{{ number_format($group->subtotal, 2) }} شيكل</span>
                    </header>

                    <ul class="divide-y divide-gray-50">
                        @foreach ($group->items as $item)
                            <li class="flex items-center gap-3 px-4 py-3">
                                <span class="grid size-14 shrink-0 place-items-center overflow-hidden rounded-xl bg-primary-50 text-lg font-extrabold text-primary/40"
                                      @if ($item->product->thumbnail) style="background-image:url('{{ Storage::disk('public')->url($item->product->thumbnail) }}');background-size:cover;background-position:center" @endif>
                                    @unless ($item->product->thumbnail){{ mb_substr($item->product->name, 0, 1) }}@endunless
                                </span>

                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-ink">{{ $item->product->name }}</p>
                                    <p class="mt-0.5 text-[11px] text-gray-500">{{ number_format((float) $item->product->price, 2) }} شيكل للوحدة</p>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <form method="POST" action="{{ route('cart.update', $item) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="quantity" value="{{ $item->quantity - 1 }}">
                                        <button type="submit" class="grid size-7 place-items-center rounded-lg border border-gray-200 text-gray-600 hover:border-primary hover:text-primary">−</button>
                                    </form>

                                    <span class="min-w-8 text-center text-sm font-bold">{{ $item->quantity }}</span>

                                    <form method="POST" action="{{ route('cart.update', $item) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="quantity" value="{{ $item->quantity + 1 }}">
                                        <button type="submit" class="grid size-7 place-items-center rounded-lg border border-gray-200 text-gray-600 hover:border-primary hover:text-primary">+</button>
                                    </form>
                                </div>

                                <div class="w-20 text-end">
                                    <p class="text-sm font-extrabold text-ink">{{ number_format($item->line_total, 2) }}</p>
                                    <form method="POST" action="{{ route('cart.remove', $item) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[11px] font-semibold text-red-500 hover:text-red-600">حذف</button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <footer class="flex items-center justify-between border-t border-gray-50 px-4 py-2.5 text-[11px] text-gray-500">
                        <span>التوصيل تُحدده شركة التوصيل</span>
                        <span>إجمالي المتجر: <strong class="text-ink">{{ number_format($group->total, 2) }} شيكل</strong></span>
                    </footer>
                </section>
            @endforeach
        </div>

        {{-- الإجمالي --}}
        <div class="mt-5 rounded-2xl border border-gray-100 bg-white p-4">
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600">الإجمالي النهائي</span>
                <span class="text-lg font-extrabold text-primary">{{ number_format($cart->grand_total, 2) }} شيكل</span>
            </div>
            <p class="mt-1 text-[11px] text-gray-400">ستُصدر فاتورة مستقلة لكل متجر عند إتمام الشراء.</p>

            <a href="{{ route('checkout.index') }}"
               class="mt-4 block rounded-xl bg-primary py-3 text-center text-sm font-bold text-white transition hover:bg-primary-600">
                متابعة إلى الدفع
            </a>
        </div>
    @endif

@endsection
