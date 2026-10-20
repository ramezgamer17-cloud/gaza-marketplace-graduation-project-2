@extends('layouts.app')

@section('title', $vendorProfile->store_name)

@section('content')

    {{-- غلاف المتجر --}}
    <section class="mb-6 overflow-hidden rounded-3xl border border-gray-100 bg-white">
        <div class="h-40 w-full bg-gradient-to-l from-primary-600 via-primary to-primary-400 sm:h-56"
             @if ($vendorProfile->cover) style="background-image:url('{{ Storage::disk('public')->url($vendorProfile->cover) }}');background-size:cover;background-position:center" @endif>
        </div>

        <div class="px-5 pb-5">
            <div class="flex items-end gap-4">
                <span class="-mt-10 grid size-20 shrink-0 place-items-center overflow-hidden rounded-2xl border-4 border-white bg-primary text-2xl font-extrabold text-white shadow-sm"
                      @if ($vendorProfile->logo) style="background-image:url('{{ Storage::disk('public')->url($vendorProfile->logo) }}');background-size:cover;background-position:center" @endif>
                    @unless ($vendorProfile->logo){{ mb_substr($vendorProfile->store_name, 0, 1) }}@endunless
                </span>

                <div class="min-w-0 flex-1 pb-1 pt-3">
                    <h1 class="truncate text-xl font-extrabold text-ink">{{ $vendorProfile->store_name }}</h1>
                    <p class="mt-0.5 text-xs text-gray-500">
                        {{ $vendorProfile->address ?? 'غزة' }}
                        @if ($vendorProfile->delivery_time) · التجهيز خلال {{ $vendorProfile->delivery_time }} دقيقة @endif
                    </p>
                </div>
            </div>

            @if ($vendorProfile->description)
                <p class="mt-4 text-sm leading-6 text-gray-600">{{ $vendorProfile->description }}</p>
            @endif

            <div class="mt-4 flex flex-wrap gap-2 text-[11px] font-semibold">
                <span class="rounded-lg bg-primary/10 px-2.5 py-1 text-primary">{{ $products->count() }} منتج</span>
                @if ($vendorProfile->phone)
                    <a href="tel:{{ $vendorProfile->phone }}" class="rounded-lg bg-gray-100 px-2.5 py-1 text-gray-600" dir="ltr">{{ $vendorProfile->phone }}</a>
                @endif
            </div>
        </div>
    </section>

    {{-- الأقسام --}}
    @forelse ($sections as $section)
        @if ($section->products->isNotEmpty())
            <section class="mb-8">
                <div class="mb-3 flex items-center gap-2">
                    <h2 class="text-lg font-extrabold">{{ $section->name }}</h2>
                    <span class="text-xs text-gray-400">{{ $section->products->count() }} منتج</span>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($section->products as $product)
                        @include('partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            </section>
        @endif
    @empty
        @if ($products->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-10 text-center">
                <p class="text-sm font-semibold text-gray-600">لا توجد منتجات معروضة في هذا المتجر بعد.</p>
            </div>
        @endif
    @endforelse

    {{-- منتجات بلا قسم --}}
    @if ($uncategorized->isNotEmpty())
        <section class="mb-8">
            <h2 class="mb-3 text-lg font-extrabold">منتجات أخرى</h2>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($uncategorized as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </section>
    @endif

@endsection
