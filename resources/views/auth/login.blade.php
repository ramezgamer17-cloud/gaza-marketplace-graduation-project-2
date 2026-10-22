<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تسجيل الدخول — سوق غزة</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-surface text-ink antialiased">
    <div class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <h1 class="text-2xl font-bold text-primary">سوق غزة</h1>
                <p class="mt-2 text-sm text-gray-500">سجّل دخولك للمتابعة</p>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 sm:p-8">
                <h2 class="mb-6 text-xl font-semibold">تسجيل الدخول</h2>

                @if ($errors->any())
                    <div class="mb-5 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium">البريد الإلكتروني</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                               dir="ltr"
                               class="w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-left text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20">
                    </div>

                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-medium">كلمة المرور</label>
                        <input id="password" name="password" type="password" required
                               class="w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20">
                    </div>

                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="remember" value="1"
                               class="size-4 rounded border-gray-300 text-primary focus:ring-primary">
                        تذكّرني
                    </label>

                    <button type="submit"
                            class="w-full rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary/40">
                        دخول
                    </button>
                </form>

                @if (filled(config('services.google.client_id')))
                    <div class="my-4 flex items-center gap-3">
                        <span class="h-px flex-1 bg-gray-100"></span>
                        <span class="text-[11px] text-gray-400">أو</span>
                        <span class="h-px flex-1 bg-gray-100"></span>
                    </div>

                    <a href="{{ route('auth.google') }}"
                       class="flex w-full items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white py-2.5 text-sm font-bold text-gray-700 transition hover:bg-gray-50">
                        <svg class="size-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1Z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23Z"/><path fill="#FBBC05" d="M5.84 14.1a6.6 6.6 0 0 1 0-4.2V7.06H2.18a11 11 0 0 0 0 9.88l3.66-2.84Z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38Z"/></svg>
                        الدخول باستخدام جوجل
                    </a>
                @endif

                <p class="mt-6 text-center text-xs text-gray-400">
                    الدخول متاح للزبائن والبائعين والإدارة ومندوبي التوصيل — توجيهك تلقائي حسب دورك.
                </p>

                <p class="mt-3 text-center text-xs text-gray-500">
                    ليس لديك حساب؟
                    <a href="{{ route('register') }}" class="font-bold text-primary hover:underline">أنشئ حسابًا جديدًا</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
