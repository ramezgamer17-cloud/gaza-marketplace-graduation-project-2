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
            <div class="panel" style="padding:22px;text-align:center">
                <p class="text-xs text-gray-500">لم تُضف أي متجر للمفضلة بعد.</p>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
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
            <div class="panel" style="padding:40px 20px;text-align:center">
                <p class="text-sm font-semibold text-gray-600">لا توجد منتجات في المفضلة بعد.</p>
                <a href="{{ route('home') }}" class="btn-green" style="display:inline-flex;margin-top:16px">تصفّح المنتجات</a>
            </div>
        @else
            <div class="product-grid">
                @foreach ($products as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>

            <div class="mt-5">{{ $products->links() }}</div>
        @endif
    </section>

@endsection
