<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'سوق غزة') — سوق غزة</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-ink antialiased">

    {{-- الشريط العلوي --}}
    <header class="navbar">
        <div class="nav-inner">
            <div class="nav-start">
                <button type="button" data-menu-toggle class="menu-btn" aria-label="القائمة">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                    </svg>
                </button>

                <div class="brand">
                    <a href="{{ auth()->check() ? route('profile.show') : route('login') }}" class="brand-mark" aria-label="حسابي">
                        @if (auth()->check() && auth()->user()->avatar)
                            <img src="{{ Storage::disk('public')->url(auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}">
                        @elseif (auth()->check())
                            {{ mb_substr(auth()->user()->name, 0, 1) }}
                        @else
                            س
                        @endif
                    </a>
                    <a href="{{ route('home') }}" class="brand-text">سوق غزة</a>
                </div>
            </div>

            <form action="{{ route('home') }}" method="GET" class="search-box">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>
                </svg>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="ابحث عن منتج، متجر أو تصنيف...">
                <button type="submit" aria-label="بحث">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 7h10M18 7h2M4 17h2M10 17h10"/><circle cx="16" cy="7" r="2"/><circle cx="8" cy="17" r="2"/>
                    </svg>
                </button>
            </form>

            <div class="nav-actions">
                @auth
                    <a href="{{ route('favorites.index') }}" class="nav-action">
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path d="M20.8 4.7a5.5 5.5 0 0 0-7.8 0L12 5.8l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.5a5.5 5.5 0 0 0 0-7.8Z"/>
                        </svg>
                        <span>المفضلة</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="nav-action">
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="8" r="4"/><path d="M4.5 21a7.5 7.5 0 0 1 15 0"/>
                        </svg>
                        <span>تسجيل الدخول</span>
                    </a>
                @endauth

                @auth
                    <form method="POST" action="{{ route('logout') }}" class="logout-form">
                        @csrf
                        <button type="submit" class="nav-action">
                            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3"/><path d="M10 17l-5-5 5-5"/><path d="M5 12h11"/>
                            </svg>
                            <span>خروج</span>
                        </button>
                    </form>
                @endauth

                <a href="{{ auth()->check() ? route('cart.index') : route('login') }}" class="nav-action cart-action">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path d="M5 8h14l-1 13H6L5 8Z"/><path d="M9 9V6a3 3 0 0 1 6 0v3"/>
                    </svg>
                    <span>السلة</span>
                    @auth
                        @php
                            $cartCount = auth()->user()->cart?->items()->sum('quantity') ?? 0;
                        @endphp
                        @if ($cartCount > 0)
                            <b>{{ $cartCount }}</b>
                        @endif
                    @endauth
                </a>
            </div>

            <form action="{{ route('home') }}" method="GET" class="search-box mobile-search">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>
                </svg>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="ابحث في سوق غزة...">
                <button type="submit" aria-label="بحث">
                    <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 7h10M18 7h2M4 17h2M10 17h10"/><circle cx="16" cy="7" r="2"/><circle cx="8" cy="17" r="2"/>
                    </svg>
                </button>
            </form>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-6 pb-24 lg:pb-10">
        @if (session('status'))
            <div class="mb-5 rounded-xl bg-primary/10 px-4 py-3 text-sm text-primary-700">{{ session('status') }}</div>
        @endif

        @if (session('error'))
            <div class="mb-5 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-600">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>

    <footer class="hidden border-t border-gray-100 bg-white py-6 text-center text-xs text-gray-400 lg:block">
        سوق غزة — منصة تجارة إلكترونية متعددة البائعين · مشروع تخرج
    </footer>

    {{-- إشعار: أنت تتصفح السوق كزبون --}}
    @php
        $toastUser = auth()->user();
        $toastPanelUrl = $toastUser ? \App\Support\RoleHome::url($toastUser) : null;
        $toastPanelLabel = match (true) {
            (bool) $toastUser?->hasRole('admin') => 'لوحة الأدمن',
            (bool) $toastUser?->hasRole('vendor') => 'لوحة البائع',
            $toastUser?->deliveryProfile?->type === 'manager' => 'لوحة الشركة',
            (bool) $toastUser?->hasRole('delivery') => 'لوحة التوصيل',
            default => 'لوحتي',
        };
        $refererPath = parse_url((string) request()->headers->get('referer', ''), PHP_URL_PATH) ?: '';
        $cameFromPanel = (bool) preg_match('#^/(admin|vendor|delivery|company|account)(/|$)#', $refererPath);

        $showRoleToast = $toastUser
            && $toastUser->hasAnyRole(['vendor', 'admin', 'delivery'])
            && ! request()->is('admin*', 'vendor*', 'delivery*', 'company*')
            && $cameFromPanel;
    @endphp

    @if ($showRoleToast)
        <div id="role-toast"
             class="fixed inset-x-4 bottom-24 z-50 translate-y-3 opacity-0 transition duration-300 lg:inset-x-auto lg:bottom-6 lg:end-6 lg:w-96">
            <div class="flex items-start gap-3 rounded-2xl border border-primary/20 bg-white p-4 shadow-xl shadow-black/5">
                <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-primary/10 text-primary">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272"/>
                    </svg>
                </span>

                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-ink">أنت تتصفّح السوق كزبون</p>
                    <p class="mt-0.5 text-[11px] leading-5 text-gray-500">
                        يمكنك الشراء من أي متجر، ولوحتك في انتظارك.
                    </p>

                    <a href="{{ $toastPanelUrl }}"
                       class="mt-2.5 inline-block rounded-lg bg-primary px-3 py-1.5 text-[11px] font-bold text-white transition hover:bg-primary-600">
                        العودة إلى {{ $toastPanelLabel }}
                    </a>
                </div>

                <button type="button" data-toast-close
                        class="grid size-7 shrink-0 place-items-center rounded-lg text-gray-400 transition hover:bg-gray-50 hover:text-gray-600">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    {{-- القائمة الجانبية --}}
    <div id="side-menu" style="display:none" class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/40" data-menu-close></div>

        <aside class="absolute inset-y-0 start-0 flex w-72 max-w-[85vw] flex-col bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-4">
                <span class="text-sm font-extrabold">القائمة</span>
                <button type="button" data-menu-close class="grid size-8 place-items-center rounded-lg text-gray-400 hover:bg-gray-50 hover:text-gray-600">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto p-3">
                @auth
                    <div class="mb-3 flex items-center gap-3 rounded-2xl bg-gray-50 p-3">
                        <span class="grid size-10 place-items-center overflow-hidden rounded-full bg-primary text-sm font-bold text-white"
                              @if (auth()->user()->avatar) data-avatar="{{ Storage::disk('public')->url(auth()->user()->avatar) }}" @endif>
                            @if (auth()->user()->avatar)
                                <img src="{{ Storage::disk('public')->url(auth()->user()->avatar) }}" alt="" class="size-full object-cover">
                            @else
                                {{ mb_substr(auth()->user()->name, 0, 1) }}
                            @endif
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-xs font-bold">{{ auth()->user()->name }}</p>
                            <a href="{{ route('profile.show') }}" class="text-[10px] text-primary hover:underline">عرض الحساب</a>
                        </div>
                    </div>
                @endauth

                <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    <span class="text-base">🏠</span> الرئيسية
                </a>
                <a href="{{ route('stores.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    <span class="text-base">🏬</span> المتاجر
                </a>

                @auth
                    <a href="{{ route('orders.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        <span class="text-base">🧾</span> طلباتي
                    </a>

                    @php
                        $menuDelivery = auth()->user()->deliveryProfile;
                        $menuVendor = auth()->user()->vendorProfile;
                    @endphp

                    @if ($menuVendor)
                        @php
                            $vendorPending = auth()->user()->vendorOrders()->where('status', 'pending')->count();
                        @endphp
                        <a href="{{ route('filament.vendor.resources.orders.index') }}"
                           class="flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            <span class="flex items-center gap-3"><span class="text-base">🏪</span> طلبات متجري</span>
                            @if ($vendorPending > 0)
                                <span class="rounded-full bg-primary px-2 py-0.5 text-[10px] font-bold text-white">{{ $vendorPending }}</span>
                            @endif
                        </a>
                    @endif

                    @if ($menuDelivery?->is_active && $menuDelivery->type === 'manager')
                        @php
                            $companyPending = \App\Models\Order::where('delivery_company_id', $menuDelivery->company_id)->whereNull('delivery_user_id')->count();
                        @endphp
                        <a href="{{ route('filament.company.resources.orders.index') }}"
                           class="flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            <span class="flex items-center gap-3"><span class="text-base">🚚</span> التوصيلات</span>
                            @if ($companyPending > 0)
                                <span class="rounded-full bg-amber-500 px-2 py-0.5 text-[10px] font-bold text-white">{{ $companyPending }}</span>
                            @endif
                        </a>
                    @elseif ($menuDelivery?->is_active)
                        @php
                            $driverActive = \App\Models\Order::where('delivery_user_id', auth()->id())->whereIn('status', ['confirmed', 'preparing', 'shipped'])->count();
                        @endphp
                        <a href="{{ route('filament.delivery.resources.orders.index') }}"
                           class="flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            <span class="flex items-center gap-3"><span class="text-base">🚚</span> توصيلاتي</span>
                            @if ($driverActive > 0)
                                <span class="rounded-full bg-primary px-2 py-0.5 text-[10px] font-bold text-white">{{ $driverActive }}</span>
                            @endif
                        </a>
                    @endif

                    <a href="{{ route('favorites.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        <span class="text-base">♥</span> المفضلة
                    </a>
                    <a href="{{ route('cart.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        <span class="text-base">🛒</span> السلة
                    </a>
                    <a href="{{ route('profile.show') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        <span class="text-base">👤</span> حسابي
                    </a>

                    @if (in_array(\App\Support\RoleHome::url(auth()->user()), ['/admin', '/vendor', '/delivery', '/company'], true))
                        <a href="{{ \App\Support\RoleHome::url(auth()->user()) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-primary hover:bg-primary/5">
                            <span class="text-base">⚙️</span> لوحة التحكم
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        <span class="text-base">🛒</span> السلة
                    </a>
                    <a href="{{ route('login') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        <span class="text-base">🔑</span> تسجيل الدخول
                    </a>
                    <a href="{{ route('register') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        <span class="text-base">✨</span> حساب جديد
                    </a>
                @endauth
            </nav>

            @auth
                <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-100 p-3">
                    @csrf
                    <button type="submit" class="w-full rounded-xl border border-gray-200 py-2.5 text-xs font-semibold text-gray-600 hover:border-red-200 hover:text-red-600">
                        تسجيل الخروج
                    </button>
                </form>
            @endauth
        </aside>
    </div>

    {{-- تكبير الصورة الشخصية --}}
    <div id="avatar-lightbox" style="display:none" class="fixed inset-0 z-[60] items-center justify-center bg-black/70 p-6 backdrop-blur-sm">
        <button type="button" data-lightbox-close
                class="absolute end-5 top-5 grid size-10 place-items-center rounded-full bg-white/10 text-white transition hover:bg-white/20">
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
        <img data-lightbox-image alt="" class="max-h-[80vh] max-w-[90vw] rounded-full border-4 border-white/20 object-cover shadow-2xl">
    </div>

    {{-- نافذة معاينة المنتج --}}
    <dialog id="product-modal" class="fixed inset-0 m-auto h-fit max-h-[92vh] w-[min(94vw,880px)] overflow-y-auto rounded-3xl border-0 bg-transparent p-0 backdrop:bg-black/60 backdrop:backdrop-blur-sm">
        <div class="relative overflow-hidden bg-white">
            <button type="button" data-modal-close
                    class="absolute end-3 top-3 z-10 grid size-9 place-items-center rounded-full bg-white/90 text-gray-600 shadow-sm transition hover:bg-white hover:text-ink">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>

            <div class="grid sm:grid-cols-2">
                <div class="relative aspect-square bg-gradient-to-br from-primary-50 to-secondary-50">
                    <img data-modal-image alt="" class="hidden size-full object-cover">
                    <span data-modal-placeholder class="grid size-full place-items-center text-6xl font-extrabold text-primary/30"></span>
                </div>

                <div class="flex flex-col gap-3 p-5 sm:p-6">
                    <a data-modal-store-link href="#" class="hidden w-fit rounded-lg bg-primary/10 px-2.5 py-1 text-[11px] font-bold text-primary"></a>

                    <h2 data-modal-name class="text-xl font-extrabold text-ink"></h2>
                    <p data-modal-store class="text-xs text-gray-500"></p>

                    <div class="flex items-baseline gap-2">
                        <span data-modal-price class="text-lg font-extrabold text-primary"></span>
                        <span data-modal-compare class="hidden text-sm text-gray-400 line-through"></span>
                        <span data-modal-discount class="hidden rounded-lg bg-red-500 px-2 py-0.5 text-[10px] font-bold text-white"></span>
                    </div>

                    <p data-modal-description class="text-sm leading-6 text-gray-600"></p>

                    <form data-modal-form method="POST" class="mt-auto hidden">
                        @csrf
                        <button type="submit" class="w-full rounded-xl bg-primary py-3 text-sm font-bold text-white transition hover:bg-primary-600">
                            أضف إلى السلة
                        </button>
                    </form>

                    <div data-modal-guest class="mt-auto hidden">
                        <a href="{{ route('login') }}" class="block rounded-xl bg-primary py-3 text-center text-sm font-bold text-white transition hover:bg-primary-600">
                            سجّل الدخول للشراء
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </dialog>

    {{-- شريط سفلي للموبايل --}}
    <nav class="mobile-nav" aria-label="التنقل الرئيسي">
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="m3 11 9-8 9 8"/><path d="M5 10v11h14V10M9 21v-6h6v6"/></svg>
            <span>الرئيسية</span>
        </a>
        <a href="{{ route('stores.index') }}" class="{{ request()->routeIs('stores.*') ? 'active' : '' }}">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M4 10v11h16V10M3 4h18l-2 6H5L3 4Z"/><path d="M8 21v-6h8v6"/></svg>
            <span>المتاجر</span>
        </a>
        <a href="{{ auth()->check() ? route('cart.index') : route('login') }}" class="{{ request()->routeIs('cart.*') ? 'active' : '' }}">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M5 8h14l-1 13H6L5 8Z"/><path d="M9 9V6a3 3 0 0 1 6 0v3"/></svg>
            <span>السلة</span>
        </a>
        <a href="{{ auth()->check() ? route('favorites.index') : route('login') }}" class="{{ request()->routeIs('favorites.*') ? 'active' : '' }}">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M20.8 4.7a5.5 5.5 0 0 0-7.8 0L12 5.8l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.5a5.5 5.5 0 0 0 0-7.8Z"/></svg>
            <span>المفضلة</span>
        </a>
        <a href="{{ auth()->check() ? route('profile.show') : route('login') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4.5 21a7.5 7.5 0 0 1 15 0"/></svg>
            <span>حسابي</span>
        </a>
    </nav>

    {{-- تنبيه صغير (إضافة للسلة بدون إعادة تحميل) --}}
    <div id="mini-toast" style="position:fixed;z-index:90;bottom:26px;left:50%;transform:translate(-50%,20px);opacity:0;pointer-events:none;transition:.25s;background:#043b2d;color:#fff;padding:11px 18px;border-radius:13px;font-size:12px;font-weight:700;box-shadow:0 12px 30px rgba(4,59,45,.25);max-width:90vw;text-align:center"></div>

    <script>
        (function () {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            const toastEl = document.getElementById('mini-toast');
            let toastTimer = null;

            function showToast(message, isError) {
                if (!toastEl || !message) return;
                toastEl.textContent = message;
                toastEl.style.background = isError ? '#b3261e' : '#043b2d';
                toastEl.style.opacity = '1';
                toastEl.style.transform = 'translate(-50%, 0)';
                clearTimeout(toastTimer);
                toastTimer = setTimeout(() => {
                    toastEl.style.opacity = '0';
                    toastEl.style.transform = 'translate(-50%, 20px)';
                }, 2600);
            }

            function updateCartBadge(count) {
                const cartLink = document.querySelector('.nav-action.cart-action');
                if (!cartLink) return;
                let badge = cartLink.querySelector('b');

                if (count > 0) {
                    if (!badge) {
                        badge = document.createElement('b');
                        cartLink.appendChild(badge);
                    }
                    badge.textContent = count;
                } else if (badge) {
                    badge.remove();
                }
            }

            // إضافة للسلة عبر AJAX: بلا إعادة تحميل وبلا فقدان مكان التمرير
            document.addEventListener('submit', function (event) {
                const form = event.target.closest('form[action*="cart/add"]');
                if (!form || form.dataset.noAjax) return;

                event.preventDefault();

                const button = form.querySelector('button[type="submit"]');
                if (button) button.disabled = true;

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(form),
                })
                    .then(async (response) => ({ status: response.status, data: await response.json().catch(() => ({})) }))
                    .then(({ status, data }) => {
                        if (status >= 200 && status < 300 && data.ok) {
                            updateCartBadge(data.count || 0);
                            showToast(data.message || 'أُضيف إلى السلة');

                            if (button) {
                                button.classList.add('added');
                                setTimeout(() => button.classList.remove('added'), 1400);
                            }
                        } else {
                            showToast(data.message || 'تعذّرت الإضافة', true);
                        }
                    })
                    .catch(() => {
                        // في حال فشل الشبكة: أرسل النموذج بالطريقة التقليدية
                        form.dataset.noAjax = '1';
                        form.submit();
                    })
                    .finally(() => {
                        if (button) button.disabled = false;
                    });
            }, true);

            // إضافة/إزالة منتج من المفضلة بدون إعادة تحميل
            document.addEventListener('submit', function (event) {
                const form = event.target.closest('form[action*="/favorites/"]');
                if (!form || form.dataset.noAjax || form.hasAttribute('data-favorite-form')) return;

                event.preventDefault();

                const button = form.querySelector('button');
                if (button) button.disabled = true;

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(form),
                })
                    .then(async (response) => ({ status: response.status, data: await response.json().catch(() => ({})) }))
                    .then(({ status, data }) => {
                        if (status >= 200 && status < 300 && data.ok && button) {
                            button.classList.toggle('is-fav', data.favorited);
                            const svg = button.querySelector('svg');
                            if (svg) svg.setAttribute('fill', data.favorited ? 'currentColor' : 'none');

                            button.classList.remove('heart-pop');
                            void button.offsetWidth;
                            button.classList.add('heart-pop');
                            setTimeout(() => button.classList.remove('heart-pop'), 450);

                            showToast(data.message || (data.favorited ? 'أُضيف إلى المفضلة' : 'أُزيل من المفضلة'));
                        } else {
                            showToast(data.message || 'تعذّر تحديث المفضلة', true);
                        }
                    })
                    .catch(() => {
                        form.dataset.noAjax = '1';
                        form.submit();
                    })
                    .finally(() => {
                        if (button) button.disabled = false;
                    });
            }, true);
        })();
    </script>

    @stack('scripts')
</body>
</html>
