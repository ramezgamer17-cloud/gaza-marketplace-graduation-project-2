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

                <p class="mt-6 text-center text-xs text-gray-400">
                    الدخول متاح للزبائن والبائعين والإدارة ومندوبي التوصيل — توجيهك تلقائي حسب دورك.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
