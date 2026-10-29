@extends('layouts.app')

@section('title', $vendorProfile->store_name)

@section('content')

    {{-- غلاف المتجر بعرض الشاشة --}}
    <div style="width:100vw; margin-inline:calc(50% - 50vw); margin-top:-24px; height:clamp(190px,26vw,340px); background:linear-gradient(110deg,#064c3b,#08745c 55%,#173e83); {{ $vendorProfile->cover ? "background-image:url('".Storage::disk('public')->url($vendorProfile->cover)."');background-size:cover;background-position:center" : '' }}">
    </div>

    <section class="glass-card relative z-10 -mt-10 mb-6 rounded-3xl">
        <div class="px-5 pb-5">
            <div class="flex items-end gap-4">
                @php
                    $storeLogo = $vendorProfile->logo
                        ? Storage::disk('public')->url($vendorProfile->logo)
                        : ($vendorProfile->cover ? Storage::disk('public')->url($vendorProfile->cover) : null);
                @endphp

                <span class="profile-avatar -mt-16 block size-28 shrink-0 bg-primary text-2xl font-extrabold text-white {{ $storeLogo ? 'cursor-zoom-in' : '' }}"
                      @if ($storeLogo) data-avatar="{{ $storeLogo }}" @endif>
                    @if ($storeLogo)
                        <img src="{{ $storeLogo }}" alt="{{ $vendorProfile->store_name }}">
                    @else
                        <span class="grid size-full place-items-center">{{ mb_substr($vendorProfile->store_name, 0, 1) }}</span>
                    @endif
                </span>

                <div class="min-w-0 flex-1 pb-1 pt-3">
                    <h1 class="truncate text-xl font-extrabold text-ink">{{ $vendorProfile->store_name }}</h1>
                    <p class="mt-0.5 text-xs text-gray-500">
                        {{ $vendorProfile->full_address ?: 'غزة' }}
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

            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t pt-4" style="border-color:rgba(6,95,70,.1)">
                {{-- تقييم المتجر --}}
                <div class="store-rating">
                    <div class="stars" data-stars data-average="{{ $ratingAverage }}">
                        @for ($i = 1; $i <= 5; $i++)
                            <form method="POST" action="{{ route('stores.rate', $vendorProfile) }}" data-rate-form>
                                @csrf
                                <input type="hidden" name="rating" value="{{ $i }}">
                                <button type="submit" class="star {{ $i <= round($ratingAverage) ? 'on' : '' }}"
                                        title="تقييم {{ $i }} من 5" aria-label="تقييم {{ $i }} من 5">★</button>
                            </form>
                        @endfor
                    </div>

                    <span class="rating-score" data-rating-average>{{ number_format($ratingAverage, 1) }}</span>
                    <span class="rating-count">(<span data-rating-count>{{ $ratingCount }}</span> تقييم)</span>

                    @guest
                        <a href="{{ route('login') }}" class="text-[10px] font-bold text-primary">سجّل لتقييم المتجر</a>
                    @endguest
                </div>

                {{-- المفضلة --}}
                @auth
                    <form method="POST" action="{{ route('stores.favorite', $vendorProfile) }}" data-favorite-form>
                        @csrf
                        <button type="submit" class="btn-ghost {{ $isFavorited ? 'is-fav' : '' }}" data-favorite-btn>
                            <svg width="16" height="16" fill="{{ $isFavorited ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path d="M20.8 4.7a5.5 5.5 0 0 0-7.8 0L12 5.8l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.5a5.5 5.5 0 0 0 0-7.8Z"/>
                            </svg>
                            <span data-favorite-label>{{ $isFavorited ? 'في المفضلة' : 'أضف للمفضلة' }}</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn-ghost">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M20.8 4.7a5.5 5.5 0 0 0-7.8 0L12 5.8l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.5a5.5 5.5 0 0 0 0-7.8Z"/></svg>
                        أضف للمفضلة
                    </a>
                @endauth
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

                <div class="product-grid">
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

            <div class="product-grid">
                @foreach ($uncategorized as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </section>
    @endif

@endsection

@push('scripts')
    <script>
        (function () {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            const toast = (message, error) => {
                const el = document.getElementById('mini-toast');
                if (!el) return;
                el.textContent = message;
                el.style.background = error ? '#b3261e' : '#043b2d';
                el.style.opacity = '1';
                el.style.transform = 'translate(-50%, 0)';
                clearTimeout(window.__storeToast);
                window.__storeToast = setTimeout(() => {
                    el.style.opacity = '0';
                    el.style.transform = 'translate(-50%, 20px)';
                }, 2400);
            };

            async function post(form, extra = {}) {
                const body = new FormData(form);
                Object.entries(extra).forEach(([k, v]) => body.set(k, v));
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body,
                });
                return res.json().catch(() => ({ ok: false }));
            }

            // المفضلة
            document.querySelectorAll('[data-favorite-form]').forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const btn = form.querySelector('[data-favorite-btn]');
                    const data = await post(form);
                    if (!data.ok) return;

                    btn.classList.toggle('is-fav', data.favorited);
                    btn.querySelector('[data-favorite-label]').textContent = data.favorited ? 'في المفضلة' : 'أضف للمفضلة';
                    btn.querySelector('svg').setAttribute('fill', data.favorited ? 'currentColor' : 'none');
                    toast(data.message);
                });
            });

            // التقييم
            document.querySelectorAll('[data-rate-form]').forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const value = parseInt(form.querySelector('input[name="rating"]').value, 10);
                    const data = await post(form, { rating: value });
                    if (!data.ok) { toast('تعذّر حفظ التقييم', true); return; }

                    const starsWrap = form.closest('[data-stars]');
                    starsWrap.querySelectorAll('.star').forEach((star, index) => {
                        const on = index < value;
                        star.classList.toggle('on', on);
                        if (on) {
                            star.classList.remove('pop');
                            void star.offsetWidth;
                            star.classList.add('pop');
                        }
                    });

                    document.querySelector('[data-rating-average]').textContent = Number(data.average).toFixed(1);
                    document.querySelector('[data-rating-count]').textContent = data.count;
                    toast(data.message);
                });
            });
        })();
    </script>
@endpush
