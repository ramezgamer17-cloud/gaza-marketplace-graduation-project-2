@extends('layouts.app')

@section('title', 'الرئيسية')

@section('content')

    {{-- الهيرو (بعرض الشاشة كامل) --}}
    <div style="width:100vw; margin-inline:calc(50% - 50vw); margin-top:-24px">
        <section class="hero">
            <img src="{{ $settings->heroImageUrl() }}" alt="سوق غزة">
            <div class="hero-shade"></div>

            <div class="shell hero-content">
                <span class="hero-kicker">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path d="m12 3 1.4 4.6L18 9l-4.6 1.4L12 15l-1.4-4.6L6 9l4.6-1.4L12 3Z"/>
                    </svg>
                    {{ $settings->heroBadgeText() }}
                </span>

                <h1>
                    {{ $settings->heroTitleText() }}<br>
                    <em>{{ $settings->heroTitleAccentText() }}</em>
                </h1>

                <p>{{ $settings->heroSubtitleText() }}</p>

                <div class="hero-actions">
                    <a href="{{ route('stores.index') }}" class="primary-button">
                        تسوّق الآن
                        <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M19 12H5M11 18l-6-6 6-6"/>
                        </svg>
                    </a>
                    <span>
                        <svg width="19" height="19" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <path d="M3 6h11v11H3zM14 10h4l3 4v3h-7z"/><circle cx="7" cy="19" r="2"/><circle cx="18" cy="19" r="2"/>
                        </svg>
                        توصيل سريع إلى باب المنزل
                    </span>
                </div>
            </div>

            @if ($stores->isNotEmpty())
                <div class="hero-stat">
                    <b>{{ $stores->count() + $categories->sum('products_count') }}</b>
                    <span>متجر ومنتج<br>في مكان واحد</span>
                </div>
            @endif
        </section>
    </div>

    {{-- التصنيفات --}}
    @if ($categories->isNotEmpty())
        <section class="categories">
            <div class="simple-heading">
                <div>
                    <span class="eyebrow">تسوّق حسب الفئة</span>
                    <h2>كل ما تحتاجه في مكان واحد</h2>
                </div>
                <a href="{{ route('stores.index') }}">
                    عرض الكل
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </a>
            </div>

            <div class="category-row">
                @php($tones = ['mint', 'blue', 'ice', 'lilac', 'sand'])
                @foreach ($categories->take(5) as $i => $category)
                    <a href="{{ route('stores.index') }}" class="category-card">
                        <span class="category-icon {{ $tones[$i % 5] }}">{{ mb_substr($category->name, 0, 1) }}</span>
                        <span>
                            <b>{{ $category->name }}</b>
                            <small>{{ $category->products_count }} منتج</small>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- المتاجر --}}
    @if ($stores->isNotEmpty())
        <section id="stores" class="stores-section">
            <div class="shell">
                <div class="section-heading">
                    <div>
                        <span class="eyebrow">متاجر موثوقة</span>
                        <h2>من حولك، إلى بابك</h2>
                    </div>
                    <div class="carousel-controls">
                        <button type="button" data-scroll="stores-row" data-dir="-1" aria-label="السابق">
                            <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                        <button type="button" data-scroll="stores-row" data-dir="1" aria-label="التالي">
                            <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                    </div>
                </div>

                <div class="store-row snap-row" id="stores-row">
                    @php($logoClasses = ['green', 'navy', 'orange', 'teal'])
                    @foreach ($stores as $i => $store)
                        <a href="{{ route('stores.show', $store) }}" class="store-card">
                            @if ($store->is_featured)
                                <span class="offer-tag">مميز</span>
                            @endif

                            <span class="store-logo {{ $logoClasses[$i % 4] }}" style="border-radius:999px">
                                @if ($store->logo)
                                    <img src="{{ Storage::disk('public')->url($store->logo) }}" alt="{{ $store->store_name }}">
                                @elseif ($store->cover)
                                    <img src="{{ Storage::disk('public')->url($store->cover) }}" alt="{{ $store->store_name }}">
                                @else
                                    {{ mb_substr($store->store_name, 0, 1) }}
                                @endif
                            </span>

                            <span class="store-copy">
                                <h3>{{ $store->store_name }}</h3>
                                <p>{{ $store->description ?: ($store->zone?->name ?? 'متجر على سوق غزة') }}</p>
                                <span class="rating">
                                    <b>★</b>
                                    {{ ($store->user?->products_count ?? 0).' منتج' }}
                                </span>
                            </span>

                            <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#c8d6d1; transform: rotate(180deg)">
                                <path d="m9 18 6-6-6-6"/>
                            </svg>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- العروض --}}
    @if ($featuredProducts->isNotEmpty())
        <section class="deals-section">
            <div class="simple-heading">
                <div>
                    <span class="eyebrow">عروض اليوم</span>
                    <h2>وفر أكثر، استمتع أكثر</h2>
                </div>
                <a href="{{ route('stores.index') }}">
                    عرض الكل
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                </a>
            </div>

            <div class="product-row snap-row">
                @foreach ($featuredProducts as $product)
                    @include('partials.product-card', ['product' => $product, 'large' => $loop->index < 3])
                @endforeach
            </div>
        </section>
    @endif

    {{-- مختارات المتاجر --}}
    @if ($storeSections->isNotEmpty())
        <section id="picks" class="picks-section">
            <div class="shell">
                <div class="section-heading">
                    <div>
                        <span class="eyebrow">مختارات المتاجر</span>
                        <h2>اختيارات أصحاب المتاجر</h2>
                    </div>
                    <span style="font-size:11px;color:var(--muted)">كل متجر يختار منتجاته بنفسه</span>
                </div>

                @foreach ($storeSections as $store)
                    <div class="store-strip"
                         @if ($store->cover) style="background-image:linear-gradient(100deg, rgba(4,59,45,.94) 0%, rgba(6,95,70,.82) 45%, rgba(15,52,90,.72) 100%), url('{{ Storage::disk('public')->url($store->cover) }}'); background-size:cover; background-position:center" @endif>
                        <span class="store-logo" style="flex:0 0 46px;height:46px;border-radius:999px;font-size:16px;border:2px solid rgba(255,255,255,.55)">
                            @if ($store->logo)
                                <img src="{{ Storage::disk('public')->url($store->logo) }}" alt="{{ $store->store_name }}">
                            @elseif ($store->cover)
                                <img src="{{ Storage::disk('public')->url($store->cover) }}" alt="{{ $store->store_name }}">
                            @else
                                {{ mb_substr($store->store_name, 0, 1) }}
                            @endif
                        </span>

                        <div>
                            <span>اختيارات {{ $store->showcaseProducts->count() }} منتج</span>
                            <h2>{{ $store->store_name }}</h2>
                        </div>

                        <a href="{{ route('stores.show', $store) }}">زيارة المتجر</a>
                    </div>

                    <div class="carousel-controls" style="margin:0 0 10px; justify-content:flex-start">
                        <button type="button" data-scroll="picks-row-{{ $store->id }}" data-dir="-1" aria-label="السابق">
                            <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                        <button type="button" data-scroll="picks-row-{{ $store->id }}" data-dir="1" aria-label="التالي">
                            <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                    </div>

                    <div class="product-row snap-row picks-row" id="picks-row-{{ $store->id }}" style="margin-bottom:26px">
                        @foreach ($store->showcaseProducts as $product)
                            @include('partials.product-card', ['product' => $product])
                        @endforeach
                    </div>
                @endforeach
            </div>
        </section>
    @endif

@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-scroll]').forEach((button) => {
            button.addEventListener('click', () => {
                const row = document.getElementById(button.dataset.scroll);
                if (!row) return;
                const amount = Math.min(row.clientWidth * 0.8, 640);
                const direction = getComputedStyle(row).direction === 'rtl' ? -1 : 1;
                row.scrollBy({ left: direction * Number(button.dataset.dir) * amount, behavior: 'smooth' });
            });
        });
    </script>
@endpush
