@extends('layouts.app')

@section('title', 'المتاجر')

@section('content')

    <div class="page-head">
        <div>
            <span class="eyebrow">كل متاجر غزة</span>
            <h1>المتاجر</h1>
            <p>{{ $stores->total() }} متجر موثوق · منتجات طازجة تصل إلى بابك</p>
        </div>
    </div>

    <form method="GET" action="{{ route('stores.index') }}" class="panel mb-6 flex flex-wrap gap-2 p-3">
        <input type="search" name="q" value="{{ $query }}" placeholder="ابحث باسم المتجر…"
               class="min-w-0 flex-1 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
        <button type="submit" class="btn-green" style="min-height:42px;padding:0 22px">بحث</button>
    </form>

    @if ($stores->isEmpty())
        <div class="panel" style="padding:48px 20px;text-align:center">
            <p class="text-sm font-semibold text-gray-600">لا توجد متاجر مطابقة.</p>
        </div>
    @else
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
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

                        <div class="flex flex-wrap gap-1.5">
                            <span class="store-chip">{{ $store->user?->products_count ?? 0 }} منتج</span>
                            @if ($store->zone)
                                <span class="store-chip gray">{{ $store->zone->name }}</span>
                            @endif
                            @if ($store->is_featured)
                                <span class="store-chip gold">مميز</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">{{ $stores->links() }}</div>
    @endif

@endsection