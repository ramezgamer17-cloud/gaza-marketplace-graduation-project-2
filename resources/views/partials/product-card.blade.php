@php
    /** @var \App\Models\Product $product */
    $store = $product->vendor?->vendorProfile;
    $hasDiscount = $product->compare_at_price && $product->compare_at_price > $product->price;
    $initial = mb_substr($product->name, 0, 1);
    $imageUrl = $product->thumbnail ? Storage::disk('public')->url($product->thumbnail) : '';

    $favoriteIds = $favoriteIds ?? (auth()->check() ? auth()->user()->favorites()->pluck('product_id')->all() : []);
    $isFavorite = in_array($product->id, $favoriteIds, true);
    $large = $large ?? false;
@endphp

<article class="product-card {{ $large ? 'product-card-large' : '' }}">
    <div class="product-image-wrap">
        <button type="button" class="block h-full w-full" style="border:0;padding:0;background:none"
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

            @if ($imageUrl)
                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="product-image" loading="lazy">
            @else
                <span class="product-image-placeholder">{{ $initial }}</span>
            @endif

            @if ($hasDiscount)
                <span class="product-badge">خصم {{ $product->discount_percentage }}%</span>
            @elseif ($product->is_featured)
                <span class="product-badge">مميز</span>
            @endif
        </button>

        @auth
            <form method="POST" action="{{ route('favorites.toggle', $product) }}" style="margin:0">
                @csrf
                <button type="submit" class="heart-button" title="{{ $isFavorite ? 'إزالة من المفضلة' : 'إضافة إلى المفضلة' }}"
                        @if ($isFavorite) style="color:#c94242" @endif>
                    <svg width="17" height="17" fill="{{ $isFavorite ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                        <path d="M20.8 4.7a5.5 5.5 0 0 0-7.8 0L12 5.8l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.5a5.5 5.5 0 0 0 0-7.8Z"/>
                    </svg>
                </button>
            </form>
        @endauth
    </div>

    <div class="product-info">
        @if ($store)
            <a href="{{ route('stores.show', $store) }}" class="product-store">{{ $store->store_name }}</a>
        @endif

        <h3>{{ $product->name }}</h3>

        <div class="product-footer">
            <div class="price">
                <strong>{{ number_format((float) $product->price, 0) }} <small style="font-size:9px;font-weight:700">شيكل</small></strong>
                @if ($hasDiscount)
                    <del>{{ number_format((float) $product->compare_at_price, 0) }} شيكل</del>
                @endif
            </div>

            @auth
                <form method="POST" action="{{ route('cart.add', $product) }}" style="margin:0">
                    @csrf
                    <button type="submit" class="add-button">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        <span>أضف للسلة</span>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="add-button" style="text-decoration:none">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    <span>أضف للسلة</span>
                </a>
            @endauth
        </div>
    </div>
</article>
