@extends('layouts.app')

@section('title', 'المتاجر')

@section('content')

    <div class="mb-5">
        <h1 class="text-xl font-extrabold">المتاجر</h1>
        <p class="mt-1 text-xs text-gray-500">كل متاجر سوق غزة في مكان واحد.</p>
    </div>

    <form method="GET" action="{{ route('stores.index') }}" class="mb-5 flex gap-2">
        <input type="search" name="q" value="{{ $query }}" placeholder="ابحث باسم المتجر…"
               class="flex-1 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
        <button type="submit" class="rounded-xl bg-primary px-4 py-2.5 text-sm font-bold text-white hover:bg-primary-600">بحث</button>
    </form>

    @if ($stores->isEmpty())
        <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-12 text-center">
            <p class="text-sm font-semibold text-gray-600">لا توجد متاجر مطابقة.</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($stores as $store)
                <a href="{{ route('stores.show', $store) }}"
                   class="group overflow-hidden rounded-2xl border border-gray-100 bg-white transition hover:border-primary/30 hover:shadow-md">
                    <div class="relative h-24 bg-gradient-to-l from-primary-600 via-primary to-primary-400"
                         @if ($store->cover) style="background-image:url('{{ Storage::disk('public')->url($store->cover) }}');background-size:cover;background-position:center" @endif>
                    </div>

                    <div class="px-4 pb-4">
                        <div class="-mt-7 flex items-end gap-3">
                            <span class="grid size-14 shrink-0 place-items-center overflow-hidden rounded-2xl border-4 border-white bg-primary text-lg font-extrabold text-white"
                                  @if ($store->logo) style="background-image:url('{{ Storage::disk('public')->url($store->logo) }}');background-size:cover;background-position:center" @endif>
                                @unless ($store->logo){{ mb_substr($store->store_name, 0, 1) }}@endunless
                            </span>
                        </div>

                        <h2 class="mt-2 truncate text-sm font-extrabold text-ink group-hover:text-primary">{{ $store->store_name }}</h2>
                        <p class="mt-0.5 line-clamp-1 text-[11px] text-gray-500">
                            {{ $store->description ?: 'متجر على سوق غزة' }}
                        </p>

                        <div class="mt-2 flex flex-wrap gap-1.5 text-[10px] font-semibold">
                            <span class="rounded-lg bg-primary/10 px-2 py-0.5 text-primary">{{ $store->user?->products_count ?? 0 }} منتج</span>
                            @if ($store->delivery_time)
                                <span class="rounded-lg bg-gray-100 px-2 py-0.5 text-gray-600">{{ $store->delivery_time }} دقيقة</span>
                            @endif
                            @if ($store->is_featured)
                                <span class="rounded-lg bg-amber-50 px-2 py-0.5 text-amber-600">مميز</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-5">{{ $stores->links() }}</div>
    @endif

@endsection
