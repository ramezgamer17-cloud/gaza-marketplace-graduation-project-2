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
    <header class="sticky top-0 z-40 border-b border-gray-100 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center gap-3 px-4 py-3">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2">
                <span class="grid size-9 place-items-center rounded-xl bg-primary text-sm font-extrabold text-white">س</span>
                <span class="hidden text-lg font-extrabold text-ink sm:block">سوق غزة</span>
            </a>

            <form action="{{ route('home') }}" method="GET" class="relative flex-1">
                <input type="search" name="q" value="{{ request('q') }}"
                       placeholder="ابحث عن منتج أو متجر…"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pe-10 ps-4 text-sm outline-none transition focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/15">
                <svg class="pointer-events-none absolute inset-y-0 end-3 my-auto size-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z"/>
                </svg>
            </form>

            <a href="{{ route('cart.index') }}" class="relative grid size-10 shrink-0 place-items-center rounded-xl border border-gray-200 text-gray-600 transition hover:border-primary hover:text-primary">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                </svg>
                @auth
                    @php($cartCount = auth()->user()->cart?->items()->sum('quantity') ?? 0)
                    @if ($cartCount > 0)
                        <span class="absolute -end-1 -top-1 grid min-w-5 place-items-center rounded-full bg-primary px-1 text-[10px] font-bold text-white">{{ $cartCount }}</span>
                    @endif
                @endauth
            </a>

            @auth
                <div class="flex shrink-0 items-center gap-2">
                    <a href="{{ \App\Support\RoleHome::url(auth()->user()) }}"
                       class="hidden rounded-xl bg-primary/10 px-3 py-2 text-sm font-semibold text-primary transition hover:bg-primary/15 sm:block">
                        حسابي
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-600 transition hover:border-red-200 hover:text-red-600">
                            خروج
                        </button>
                    </form>
                </div>
            @else
                <a href="{{ route('login') }}" class="shrink-0 rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-600">
                    دخول
                </a>
            @endauth
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

    {{-- شريط سفلي للموبايل --}}
    <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-gray-100 bg-white/95 backdrop-blur lg:hidden">
        <div class="mx-auto grid max-w-lg grid-cols-4">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 py-2.5 text-primary">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955a1.125 1.125 0 0 1 1.591 0L21.75 12M4.5 9.75v10.5a.75.75 0 0 0 .75.75h4.5v-6h4.5v6h4.5a.75.75 0 0 0 .75-.75V9.75"/></svg>
                <span class="text-[11px] font-medium">الرئيسية</span>
            </a>
            <a href="{{ route('home') }}#stores" class="flex flex-col items-center gap-1 py-2.5 text-gray-500">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/></svg>
                <span class="text-[11px] font-medium">المتاجر</span>
            </a>
            <a href="#" class="flex flex-col items-center gap-1 py-2.5 text-gray-500">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272"/></svg>
                <span class="text-[11px] font-medium">السلة</span>
            </a>
            <a href="{{ auth()->check() ? \App\Support\RoleHome::url(auth()->user()) : route('login') }}" class="flex flex-col items-center gap-1 py-2.5 text-gray-500">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                <span class="text-[11px] font-medium">حسابي</span>
            </a>
        </div>
    </nav>
</body>
</html>
