@extends('layouts.app')

@section('title', 'الرئيسية')

@section('content')

    {{-- الترحيب --}}
    <section class="mb-8 overflow-hidden rounded-3xl bg-gradient-to-l from-primary-600 via-primary to-primary-400 p-6 text-white sm:p-9">
        <div class="max-w-xl">
            <p class="mb-2 text-xs font-semibold text-white/80">سوق غزة الإلكتروني</p>
            <h1 class="text-2xl font-extrabold leading-snug sm:text-3xl">
                كل ما تحتاجه من متاجر غزة<br class="hidden sm:block"> في مكان واحد
            </h1>
            <p class="mt-3 text-sm text-white/85">
                تسوّق من عدة بائعين في سلة واحدة، وتابع طلبك حتى باب البيت.
            </p>

            <div class="mt-5 flex flex-wrap gap-2">
                <a href="{{ route('stores.index') }}" class="rounded-xl bg-white px-4 py-2 text-sm font-bold text-primary transition hover:bg-white/90">
                    تصفّح المتاجر
                </a>
                <a href="#showcase" class="rounded-xl border border-white/30 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10">
                    مختارات المتاجر
                </a>
            </div>
        </div>
    </section>

    {{-- التصنيفات --}}
    @if ($categories->isNotEmpty())
        <section class="mb-9">
            <h2 class="mb-3 text-lg font-extrabold">تصفّح حسب التصنيف</h2>

            <div class="grid grid-cols-3 gap-3 sm:grid-cols-4 lg:grid-cols-8">
                @foreach ($categories as $category)
                    <a href="#" class="flex flex-col items-center gap-2 rounded-2xl border border-gray-100 bg-white p-3 text-center transition hover:border-primary/30 hover:shadow-sm">
                        <span class="grid size-11 place-items-center rounded-xl bg-primary/10 text-base font-extrabold text-primary">
                            {{ mb_substr($category->name, 0, 1) }}
                        </span>
                        <span class="line-clamp-1 text-xs font-semibold text-ink">{{ $category->name }}</span>
                        <span class="text-[10px] text-gray-400">{{ $category->products_count }} منتج</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- المتاجر --}}
    @if ($stores->isNotEmpty())
        <section id="stores" class="mb-9 scroll-mt-20">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-lg font-extrabold">متاجر مميزة</h2>
                <span class="text-xs text-gray-400">{{ $stores->count() }} متجر</span>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($stores as $store)
                    <a href="{{ route('stores.show', $store) }}"
                       class="group flex items-center gap-3 rounded-2xl border border-gray-100 bg-white p-3 transition hover:border-primary/30 hover:shadow-md">
                        <span class="grid size-14 shrink-0 place-items-center overflow-hidden rounded-2xl bg-primary/10 text-lg font-extrabold text-primary"
                              @if ($store->logo) style="background-image:url('{{ Storage::disk('public')->url($store->logo) }}');background-size:cover;background-position:center" @endif>
                            @unless ($store->logo){{ mb_substr($store->store_name, 0, 1) }}@endunless
                        </span>

                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-bold text-ink group-hover:text-primary">{{ $store->store_name }}</span>
                            <span class="mt-0.5 block text-[11px] text-gray-500">
                                {{ $store->user?->products_count ?? 0 }} منتج
                                @if ($store->delivery_time) · {{ $store->delivery_time }} دقيقة @endif
                            </span>
                        </span>

                        <svg class="size-4 shrink-0 text-gray-300 transition group-hover:text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m15 19-7-7 7-7"/>
                        </svg>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- عروض عامة --}}
    @if ($featuredProducts->isNotEmpty())
        <section class="mb-9">
            <h2 class="mb-3 text-lg font-extrabold">عروض ومنتجات مميزة</h2>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($featuredProducts as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </section>
    @endif

    {{-- مختارات كل متجر --}}
    @if ($storeSections->isNotEmpty())
        <section id="showcase" class="scroll-mt-20">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-lg font-extrabold">مختارات المتاجر</h2>
                <span class="text-xs text-gray-400">كل متجر يختار منتجاته بنفسه</span>
            </div>

            <div class="space-y-4">
                @foreach ($storeSections as $store)
                    <div class="overflow-hidden rounded-3xl border border-gray-100 bg-white">
                        <header class="flex items-center justify-between gap-3 border-b border-gray-50 px-4 py-3">
                            <a href="{{ route('stores.show', $store) }}" class="group flex min-w-0 items-center gap-3">
                                <span class="grid size-11 shrink-0 place-items-center overflow-hidden rounded-xl bg-primary/10 text-base font-extrabold text-primary"
                                      @if ($store->logo) style="background-image:url('{{ Storage::disk('public')->url($store->logo) }}');background-size:cover;background-position:center" @endif>
                                    @unless ($store->logo){{ mb_substr($store->store_name, 0, 1) }}@endunless
                                </span>

                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-extrabold text-ink group-hover:text-primary">{{ $store->store_name }}</span>
                                    <span class="block text-[11px] text-gray-500">{{ $store->showcaseProducts->count() }} منتج مميز</span>
                                </span>
                            </a>

                            <a href="{{ route('stores.show', $store) }}"
                               class="shrink-0 rounded-xl border border-gray-200 px-3 py-1.5 text-[11px] font-bold text-gray-600 transition hover:border-primary hover:text-primary">
                                زيارة المتجر
                            </a>
                        </header>

                        <div class="grid grid-cols-2 gap-3 p-3 sm:grid-cols-3 lg:grid-cols-4">
                            @foreach ($store->showcaseProducts as $product)
                                @include('partials.product-card', ['product' => $product])
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

@endsection
