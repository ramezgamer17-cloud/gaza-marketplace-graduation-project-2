@php
    /** @var \App\Models\Product $product */
    $store = $product->vendor?->vendorProfile;
    $hasDiscount = $product->compare_at_price && $product->compare_at_price > $product->price;
    $initial = mb_substr($product->name, 0, 1);
@endphp

<article class="group overflow-hidden rounded-2xl border border-gray-100 bg-white transition hover:-translate-y-0.5 hover:shadow-md">
    <a href="#" class="block">
        <div class="relative aspect-square overflow-hidden bg-gradient-to-br from-primary-50 to-secondary-50">
            @if ($product->thumbnail)
                <img src="{{ Storage::disk('public')->url($product->thumbnail) }}" alt="{{ $product->name }}"
                     class="size-full object-cover transition duration-300 group-hover:scale-105" loading="lazy">
            @else
                <div class="grid size-full place-items-center text-4xl font-extrabold text-primary/30">
                    {{ $initial }}
                </div>
            @endif

            @if ($hasDiscount)
                <span class="absolute start-2 top-2 rounded-lg bg-red-500 px-2 py-1 text-[11px] font-bold text-white">
                    -{{ $product->discount_percentage }}%
                </span>
            @endif

            @if ($product->stock === 0)
                <span class="absolute inset-0 grid place-items-center bg-white/70 text-sm font-bold text-gray-700">نفدت الكمية</span>
            @endif
        </div>
    </a>

    <div class="space-y-1.5 p-3">
        <a href="#" class="line-clamp-1 text-sm font-semibold text-ink hover:text-primary">{{ $product->name }}</a>

        @if ($store)
            <a href="{{ route('stores.show', $store) }}" class="line-clamp-1 block text-[11px] text-gray-500 hover:text-primary">
                {{ $store->store_name }}
            </a>
        @endif

        <div class="flex items-baseline gap-1.5 pt-0.5">
            <span class="text-sm font-extrabold text-primary">{{ number_format((float) $product->price, 2) }}</span>
            <span class="text-[11px] text-gray-500">شيكل</span>
            @if ($hasDiscount)
                <span class="text-[11px] text-gray-400 line-through">{{ number_format((float) $product->compare_at_price, 2) }}</span>
            @endif
        </div>

        @auth
            <form method="POST" action="{{ route('cart.add', $product) }}" class="mt-1">
                @csrf
                <button type="submit"
                        class="w-full rounded-xl bg-primary/10 py-2 text-xs font-bold text-primary transition hover:bg-primary hover:text-white">
                    أضف إلى السلة
                </button>
            </form>
        @else
            <a href="{{ route('login') }}"
               class="mt-1 block w-full rounded-xl bg-primary/10 py-2 text-center text-xs font-bold text-primary transition hover:bg-primary hover:text-white">
                أضف إلى السلة
            </a>
        @endauth
    </div>
</article>
