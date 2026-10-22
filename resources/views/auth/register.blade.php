@extends('layouts.app')

@section('title', 'إنشاء حساب')

@section('content')

    <div class="mx-auto max-w-xl">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-extrabold text-primary">سوق غزة</h1>
            <p class="mt-1 text-sm text-gray-500">أنشئ حسابك وابدأ التسوّق أو البيع أو التوصيل</p>
        </div>

        <div class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm sm:p-7">
            @if ($errors->any())
                <div class="mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- جوجل --}}
            <div class="mb-5">
                @if ($googleEnabled)
                    <a id="google-btn" href="{{ route('auth.google', ['type' => 'customer']) }}"
                       class="flex w-full items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white py-3 text-sm font-bold text-gray-700 transition hover:bg-gray-50">
                        <svg class="size-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1Z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23Z"/><path fill="#FBBC05" d="M5.84 14.1a6.6 6.6 0 0 1 0-4.2V7.06H2.18a11 11 0 0 0 0 9.88l3.66-2.84Z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38Z"/></svg>
                        المتابعة باستخدام جوجل
                    </a>
                    <p class="mt-2 text-center text-[10px] text-gray-400">
                        اختر نوع الحساب أدناه أولًا، وسيُستخدم عند المتابعة بجوجل.
                    </p>
                @else
                    <button type="button" disabled
                            class="flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-xl border border-dashed border-gray-200 bg-gray-50 py-3 text-sm font-bold text-gray-400">
                        جوجل (قيد التجهيز)
                    </button>
                @endif

                <div class="my-4 flex items-center gap-3">
                    <span class="h-px flex-1 bg-gray-100"></span>
                    <span class="text-[11px] text-gray-400">أو سجّل بالبريد</span>
                    <span class="h-px flex-1 bg-gray-100"></span>
                </div>
            </div>

            <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
                @csrf

                {{-- نوع الحساب --}}
                <div class="grid grid-cols-3 gap-2">
                    @php
                        $types = [
                            ['value' => 'customer', 'title' => 'زبون', 'desc' => 'أشتري', 'icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z'],
                            ['value' => 'vendor', 'title' => 'بائع', 'desc' => 'أفتح متجرًا', 'icon' => 'M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z'],
                            ['value' => 'delivery', 'title' => 'توصيل', 'desc' => 'أوصّل الطلبات', 'icon' => 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12'],
                        ];
                    @endphp

                    @foreach ($types as $type)
                        <label class="cursor-pointer">
                            <input type="radio" name="account_type" value="{{ $type['value'] }}" class="peer sr-only"
                                   @checked(old('account_type', 'customer') === $type['value'])>
                            <span class="flex flex-col items-center gap-1 rounded-xl border-2 border-gray-100 px-2 py-3 text-center transition peer-checked:border-primary peer-checked:bg-primary/5">
                                <svg class="size-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $type['icon'] }}"/>
                                </svg>
                                <span class="text-sm font-bold text-ink">{{ $type['title'] }}</span>
                                <span class="text-[10px] text-gray-500">{{ $type['desc'] }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="mb-1 block text-xs font-semibold text-gray-600">الاسم الكامل</label>
                        <input id="name" name="name" value="{{ old('name') }}" required
                               class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                    </div>

                    <div>
                        <label for="email" class="mb-1 block text-xs font-semibold text-gray-600">البريد الإلكتروني</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required dir="ltr"
                               class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-left text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                    </div>

                    <div>
                        <label for="phone" class="mb-1 block text-xs font-semibold text-gray-600">رقم الجوال</label>
                        <input id="phone" name="phone" value="{{ old('phone') }}" dir="ltr"
                               class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-left text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                    </div>

                    <div>
                        <label for="password" class="mb-1 block text-xs font-semibold text-gray-600">كلمة المرور</label>
                        <input id="password" name="password" type="password" required
                               class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-1 block text-xs font-semibold text-gray-600">تأكيد كلمة المرور</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                               class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                    </div>
                </div>

                {{-- الموقع: منطقة + حي (للبائع والمندوب) --}}
                <div id="location-fields" class="hidden space-y-3 rounded-2xl border border-gray-100 bg-gray-50 p-3">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label for="zone_id" class="mb-1 block text-xs font-semibold text-gray-600">
                                المنطقة <span class="text-red-500">*</span>
                            </label>
                            <select id="zone_id" name="zone_id"
                                    class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                                <option value="">اختر المنطقة…</option>
                                @foreach ($regions as $region)
                                    <option value="{{ $region->id }}" @selected((string) old('zone_id') === (string) $region->id)>{{ $region->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="area-wrap" class="hidden">
                            <label for="area_id" class="mb-1 block text-xs font-semibold text-gray-600">
                                الحي / المدينة <span class="text-red-500">*</span>
                            </label>
                            <select id="area_id" name="area_id"
                                    class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                                <option value="">اختر المنطقة أولًا…</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- العنوان: للبائع إجباري (باقي العنوان)، وللزبون اختياري --}}
                <div id="address-wrap">
                    <label for="address" class="mb-1 block text-xs font-semibold text-gray-600">
                        <span id="address-label-required" class="hidden">باقي العنوان <span class="text-red-500">*</span></span>
                        <span id="address-label-optional">العنوان <span class="text-[10px] font-normal text-gray-400">(اختياري)</span></span>
                    </label>
                    <input id="address" name="address" value="{{ old('address') }}"
                           placeholder="مثال: شارع الجلاء - بجانب المسجد"
                           class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                </div>

                {{-- حقول البائع --}}
                <div id="vendor-fields" class="hidden space-y-3 rounded-2xl border border-primary/15 bg-primary/5 p-3">
                    <p class="text-xs font-bold text-primary-700">بيانات متجرك</p>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label for="store_name" class="mb-1 block text-xs font-semibold text-gray-600">اسم المتجر <span class="text-red-500">*</span></label>
                            <input id="store_name" name="store_name" value="{{ old('store_name') }}"
                                   class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                        </div>

                        <div>
                            <label for="store_type" class="mb-1 block text-xs font-semibold text-gray-600">نوع المتجر <span class="text-red-500">*</span></label>
                            <select id="store_type" name="store_type"
                                    class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                                <option value="">اختر النوع…</option>
                                @foreach ($storeTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('store_type') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <p class="rounded-lg bg-white px-3 py-2 text-[11px] text-gray-500">
                        العنوان (المنطقة والحي) ضروري ليظهر متجرك في السوق. يُراجع الأدمن المتجر قبل ظهوره.
                    </p>
                </div>

                {{-- حقول التوصيل --}}
                <div id="delivery-fields" class="hidden space-y-3 rounded-2xl border border-secondary/20 bg-secondary-50/60 p-3">
                    <p class="text-xs font-bold text-secondary-700">بيانات التوصيل</p>

                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="delivery_type" value="driver" class="peer sr-only"
                                   @checked(old('delivery_type', 'driver') === 'driver')>
                            <span class="block rounded-xl border-2 border-gray-100 bg-white px-3 py-2.5 text-center text-xs font-bold text-ink transition peer-checked:border-secondary peer-checked:bg-secondary/10">
                                مندوب توصيل
                            </span>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" name="delivery_type" value="manager" class="peer sr-only"
                                   @checked(old('delivery_type') === 'manager')>
                            <span class="block rounded-xl border-2 border-gray-100 bg-white px-3 py-2.5 text-center text-xs font-bold text-ink transition peer-checked:border-secondary peer-checked:bg-secondary/10">
                                مدير شركة توصيل
                            </span>
                        </label>
                    </div>

                    <div id="company-select-wrap">
                        <label for="company_id" class="mb-1 block text-xs font-semibold text-gray-600">شركة التوصيل</label>
                        <select id="company_id" name="company_id"
                                class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                            <option value="">بدون شركة (مستقل)</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" @selected((string) old('company_id') === (string) $company->id)>{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="company-name-wrap" class="hidden">
                        <label for="company_name" class="mb-1 block text-xs font-semibold text-gray-600">اسم شركتك <span class="text-red-500">*</span></label>
                        <input id="company_name" name="company_name" value="{{ old('company_name') }}"
                               placeholder="مثال: توصيل غزة السريع"
                               class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                    </div>

                    <p class="rounded-lg bg-white px-3 py-2 text-[11px] text-gray-500">
                        يكفي تحديد منطقة العمل. يُفعّل الحساب بعد موافقة الإدارة.
                    </p>
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-primary py-3 text-sm font-bold text-white transition hover:bg-primary-600">
                    إنشاء الحساب
                </button>
            </form>

            <p class="mt-5 text-center text-xs text-gray-500">
                لديك حساب؟ <a href="{{ route('login') }}" class="font-bold text-primary hover:underline">سجّل الدخول</a>
            </p>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const areas = @json($regions->mapWithKeys(fn ($region) => [$region->id => $region->children->map(fn ($child) => ['id' => $child->id, 'name' => $child->name])]));

            const vendorFields = document.getElementById('vendor-fields');
            const deliveryFields = document.getElementById('delivery-fields');
            const locationFields = document.getElementById('location-fields');
            const addressWrap = document.getElementById('address-wrap');
            const areaWrap = document.getElementById('area-wrap');
            const areaSelect = document.getElementById('area_id');
            const zoneSelect = document.getElementById('zone_id');
            const addressLabelRequired = document.getElementById('address-label-required');
            const addressLabelOptional = document.getElementById('address-label-optional');
            const companySelect = document.getElementById('company-select-wrap');
            const companyName = document.getElementById('company-name-wrap');
            const googleBtn = document.getElementById('google-btn');

            const type = () => document.querySelector('input[name="account_type"]:checked')?.value;
            const deliveryType = () => document.querySelector('input[name="delivery_type"]:checked')?.value;

            const fillAreas = () => {
                const zoneId = zoneSelect.value;
                const list = areas[zoneId] || [];
                areaSelect.innerHTML = '<option value="">اختر الحي…</option>';
                list.forEach((area) => {
                    const opt = document.createElement('option');
                    opt.value = area.id;
                    opt.textContent = area.name;
                    areaSelect.appendChild(opt);
                });
            };

            const sync = () => {
                const t = type();
                const isVendor = t === 'vendor';
                const isDelivery = t === 'delivery';
                const needsLocation = isVendor || isDelivery;

                vendorFields.classList.toggle('hidden', !isVendor);
                deliveryFields.classList.toggle('hidden', !isDelivery);
                locationFields.classList.toggle('hidden', !needsLocation);

                // العنوان يظهر للزبون والبائع، ويُخفى للمندوب
                addressWrap.classList.toggle('hidden', isDelivery);

                // الحي للبائع فقط
                areaWrap.classList.toggle('hidden', !isVendor);
                areaSelect.disabled = !isVendor;

                // العنوان إجباري للبائع فقط
                addressLabelRequired.classList.toggle('hidden', !isVendor);
                addressLabelOptional.classList.toggle('hidden', isVendor);

                const isManager = isDelivery && deliveryType() === 'manager';
                companySelect.classList.toggle('hidden', isManager);
                companyName.classList.toggle('hidden', !isManager);

                if (googleBtn) {
                    googleBtn.href = '{{ route('auth.google') }}?type=' + encodeURIComponent(t || 'customer');
                }
            };

            zoneSelect.addEventListener('change', () => { fillAreas(); });
            document.querySelectorAll('input[name="account_type"], input[name="delivery_type"]')
                .forEach((el) => el.addEventListener('change', sync));

            sync();
        });
    </script>
@endpush
