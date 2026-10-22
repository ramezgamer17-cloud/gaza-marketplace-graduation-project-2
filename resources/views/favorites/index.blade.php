@extends('layouts.app')

@section('title', 'المفضلة')

@section('content')

    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-xl font-extrabold">المفضلة</h1>
        <span class="text-xs text-gray-400">{{ $products->total() }} منتج</span>
    </div>

    @if ($products->isEmpty())
        <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-12 text-center">
            <p class="text-sm font-semibold text-gray-600">لا توجد منتجات في المفضلة بعد.</p>
            <a href="{{ route('home') }}" class="mt-4 inline-block rounded-xl bg-primary px-4 py-2 text-sm font-bold text-white">تصفّح المنتجات</a>
        </div>
    @else
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($products as $product)
                @include('partials.product-card', ['product' => $product])
            @endforeach
        </div>

        <div class="mt-5">{{ $products->links() }}</div>
    @endif

@endsection
