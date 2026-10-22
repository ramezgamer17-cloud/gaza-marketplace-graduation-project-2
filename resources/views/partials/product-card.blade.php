@php
    /** @var \App\Models\Product $product */
    $store = $product->vendor?->vendorProfile;
    $hasDiscount = $product->compare_at_price && $product->compare_at_price > $product->price;
    $initial = mb_substr($product->name, 0, 1);
    $imageUrl = $product->thumbnail ? Storage::disk('public')->url($product->thumbnail) : '';

    static $favoriteIds = null;
    if ($favoriteIds === null) {
        $favoriteIds = auth()->check() ? auth()->user()->favorites()->pluck('product_id')->all() : [];
    }
    $isFavorite = in_array($product->id, $favoriteIds, true);
@endphp

<article class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white transition hover:-translate-y-0.5 hover:shadow-md">

    @auth
        <form method="POST" action="{{ route('favorites.toggle', $product) }}" class="absolute end-2 bottom-3 z-10">
            @csrf
            <button type="submit" title="{{ $isFavorite ? 'إزالة من المفضلة' : 'إضافة إلى المفضلة' }}"
                    class="grid size-8 place-items-center rounded-full bg-white/90 text-base shadow-sm backdrop-blur transition hover:scale-110 {{ $isFavorite ? 'text-red-500' : 'text-gray-400' }}">
                {{ $isFavorite ? '♥' : '♡' }}
            </button>
        </form>
    @endauth

    {{-- الضغط على المنتج يفتح نافذة المعاينة --}}
    <button type="button" class="block w-full text-start"
            data-product
            data-name="{{ $product->name }}"
            data-price="{{ number_format((float) $product->price, 2) }}"
            data-compare="{{ $hasDiscount ? number_format((float) $product->compare_at_price, 2) : '' }}"
            data-image="{{ $imageUrl }}"
            data-description="{{ $product->description }}"
            data-store="{{ $store?->store_name }}"
            data-store-url="{{ $store ? route('stores.show', $store) : '' }}"
            data-add-url="{{ route('cart.add', $product) }}"
            data-authed="{{ auth()->check() ? '1' : '0' }}">

        <span class="relative block aspect-square overflow-hidden bg-gradient-to-br from-primary-50 to-secondary-50">
            @if ($imageUrl)
                <img src="{{ $imageUrl }}" alt="{{ $product->name }}"
                     class="size-full object-cover transition duration-300 group-hover:scale-105" loading="lazy">
            @else
                <span class="grid size-full place-items-center text-4xl font-extrabold text-primary/30">{{ $initial }}</span>
            @endif

            @if ($hasDiscount)
                <span class="absolute start-2 top-2 rounded-lg bg-red-500 px-2 py-1 text-[11px] font-bold text-white">
                    -{{ $product->discount_percentage }}%
                </span>
            @endif

            @if ($product->is_featured)
                <span class="absolute end-2 top-2 rounded-lg bg-primary/90 px-2 py-1 text-[10px] font-bold text-white">مميز</span>
            @endif

            @if ($product->stock === 0)
                <span class="absolute inset-0 grid place-items-center bg-white/70 text-sm font-bold text-gray-700">نفدت الكمية</span>
            @endif
        </span>

        <span class="block space-y-1.5 p-3">
            <span class="line-clamp-1 block text-sm font-semibold text-ink group-hover:text-primary">{{ $product->name }}</span>

            @if ($store)
                <span class="line-clamp-1 block text-[11px] text-gray-500">{{ $store->store_name }}</span>
            @endif

            <span class="flex items-baseline gap-1.5 pt-0.5">
                <span class="text-sm font-extrabold text-primary">{{ number_format((float) $product->price, 2) }}</span>
                <span class="text-[11px] text-gray-500">شيكل</span>
                @if ($hasDiscount)
                    <span class="text-[11px] text-gray-400 line-through">{{ number_format((float) $product->compare_at_price, 2) }}</span>
                @endif
            </span>
        </span>
    </button>

    <div class="px-3 pb-3">
        @auth
            <form method="POST" action="{{ route('cart.add', $product) }}">
                @csrf
                <button type="submit"
                        class="w-full rounded-xl bg-primary/10 py-2 text-xs font-bold text-primary transition hover:bg-primary hover:text-white">
                    أضف إلى السلة
                </button>
            </form>
        @else
            <a href="{{ route('login') }}"
               class="block w-full rounded-xl bg-primary/10 py-2 text-center text-xs font-bold text-primary transition hover:bg-primary hover:text-white">
                أضف إلى السلة
            </a>
        @endauth
    </div>
</article>
