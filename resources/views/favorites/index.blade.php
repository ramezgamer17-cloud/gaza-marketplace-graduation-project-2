@extends('layouts.app')

@section('title', 'المفضلة')

@section('content')

    <div class="page-head">
        <div>
            <span class="eyebrow">محفوظاتك</span>
            <h1>المفضلة</h1>
            <p>{{ $stores->count() }} متجر · {{ $products->total() }} منتج</p>
        </div>
        <a href="{{ route('stores.index') }}" class="btn-ghost">تصفّح المتاجر</a>
    </div>

    {{-- المتاجر المفضلة --}}
    <section class="mb-8">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-sm font-extrabold text-ink">المتاجر المفضلة</h2>
            <span class="text-[11px] text-gray-400">اضغط على المتجر لزيارته</span>
        </div>

        @if ($stores->isEmpty())
            <div class="panel empty-state" style="padding:30px 18px">
                <span class="empty-ico" style="width:64px;height:64px;border-radius:20px">
                    <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path d="M4 10v11h16V10M3 4h18l-2 6H5L3 4Z"/><path d="M8 21v-6h8v6"/></svg>
                </span>
                <h3 style="font-size:13px">لا متاجر في المفضلة بعد</h3>
                <p>اضغط على القلب في صفحة أي متجر ليظهر هنا.</p>
                <a href="{{ route('stores.index') }}" class="btn-ghost" style="margin-top:10px">تصفّح المتاجر</a>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-stagger>
                @foreach ($stores as $store)
                    <a href="{{ route('stores.show', $store) }}" class="store-tile">
                        <div class="store-tile-cover"
                             @if ($store->cover) style="background-image:url('{{ Storage::disk('public')->url($store->cover) }}')" @endif>
                        </div>

                        <div class="store-tile-body">
                            <div class="store-tile-logo">
                                @if ($store->logo)
                                    <img src="{{ Storage::disk('public')->url($store->logo) }}" alt="{{ $store->store_name }}">
                                @elseif ($store->cover)
                                    <img src="{{ Storage::disk('public')->url($store->cover) }}" alt="{{ $store->store_name }}">
                                @else
                                    {{ mb_substr($store->store_name, 0, 1) }}
                                @endif
                            </div>

                            <h2>{{ $store->store_name }}</h2>
                            <p>{{ $store->description ?: 'متجر على سوق غزة' }}</p>

                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="store-chip">{{ $store->user?->products_count ?? 0 }} منتج</span>
                                @if ($store->zone)
                                    <span class="store-chip gray">{{ $store->zone->name }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    {{-- المنتجات المفضلة --}}
    <section>
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-sm font-extrabold text-ink">المنتجات المفضلة</h2>
        </div>

        @if ($products->isEmpty())
            <div class="panel empty-state">
                <span class="empty-ico">
                    <svg width="34" height="34" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path d="M20.8 4.7a5.5 5.5 0 0 0-7.8 0L12 5.8l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.5a5.5 5.5 0 0 0 0-7.8Z"/></svg>
                </span>
                <h3>لا منتجات في المفضلة بعد</h3>
                <p>أضف ما يعجبك ليصلك بسهولة لاحقًا.</p>
                <a href="{{ route('home') }}" class="btn-green" style="display:inline-flex;margin-top:14px">تصفّح المنتجات</a>
            </div>
        @else
            <div class="product-grid" data-stagger>
                @foreach ($products as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>

            <div class="mt-5">{{ $products->links() }}</div>
        @endif
    </section>

@endsection
