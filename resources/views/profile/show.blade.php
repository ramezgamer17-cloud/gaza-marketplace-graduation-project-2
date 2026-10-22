@extends('layouts.app')

@section('title', 'حسابي')

@section('content')

    @php
        $panelUrl = \App\Support\RoleHome::url($user);
        $hasPanel = in_array($panelUrl, ['/admin', '/vendor', '/delivery', '/company'], true);
        $panelLabel = match ($panelUrl) {
            '/admin' => 'لوحة الأدمن',
            '/vendor' => 'لوحة البائع',
            '/delivery' => 'لوحة التوصيل',
            '/company' => 'لوحة الشركة',
            default => null,
        };
    @endphp

    {{-- الغلاف والصورة --}}
    <section class="mb-5 overflow-hidden rounded-3xl border border-gray-100 bg-white">
        <div class="h-32 bg-gradient-to-l from-primary-600 via-primary to-primary-400 sm:h-44"
             @if ($user->cover) style="background-image:url('{{ Storage::disk('public')->url($user->cover) }}');background-size:cover;background-position:center" @endif>
        </div>

        <div class="px-5 pb-5">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div class="flex items-end gap-4">
                    <span class="-mt-12 block size-24 shrink-0 overflow-hidden rounded-full border-4 border-white bg-primary text-2xl font-extrabold text-white shadow-sm {{ $user->avatar ? 'cursor-zoom-in' : '' }}"
                          @if ($user->avatar) data-avatar="{{ Storage::disk('public')->url($user->avatar) }}" @endif>
                        @if ($user->avatar)
                            <img src="{{ Storage::disk('public')->url($user->avatar) }}" alt="{{ $user->name }}" class="size-full object-cover">
                        @else
                            <span class="grid size-full place-items-center">{{ mb_substr($user->name, 0, 1) }}</span>
                        @endif
                    </span>

                    <div class="min-w-0 pb-1">
                        <h1 class="truncate text-lg font-extrabold text-ink">{{ $user->name }}</h1>
                        <p class="mt-0.5 text-xs text-gray-500" dir="ltr">{{ $user->email }}</p>
                        <p class="mt-1 flex flex-wrap gap-1 text-[10px] font-bold">
                            @foreach ($user->getRoleNames() as $role)
                                <span class="rounded-lg bg-primary/10 px-2 py-0.5 text-primary">
                                    {{ match ($role) { 'admin' => 'أدمن', 'vendor' => 'بائع', 'delivery' => 'توصيل', default => 'زبون' } }}
                                </span>
                            @endforeach
                        </p>
                    </div>
                </div>

                @if ($hasPanel && $panelLabel)
                    <a href="{{ $panelUrl }}"
                       class="rounded-xl bg-primary px-4 py-2 text-xs font-bold text-white transition hover:bg-primary-600">
                        {{ $panelLabel }}
                    </a>
                @endif
            </div>

            {{-- إحصاءات سريعة --}}
            <div class="mt-4 grid grid-cols-3 gap-2">
                <a href="{{ route('orders.index') }}" class="rounded-2xl bg-gray-50 p-3 text-center transition hover:bg-gray-100">
                    <span class="block text-lg font-extrabold text-ink">{{ $ordersCount }}</span>
                    <span class="text-[11px] text-gray-500">طلباتي</span>
                </a>
                <div class="rounded-2xl bg-gray-50 p-3 text-center">
                    <span class="block text-lg font-extrabold text-ink">{{ $favoritesCount }}</span>
                    <span class="text-[11px] text-gray-500">المفضلة</span>
                </div>
                <a href="{{ route('cart.index') }}" class="rounded-2xl bg-gray-50 p-3 text-center transition hover:bg-gray-100">
                    <span class="block text-lg font-extrabold text-ink">{{ $user->cart?->items()->sum('quantity') ?? 0 }}</span>
                    <span class="text-[11px] text-gray-500">في السلة</span>
                </a>
            </div>
        </div>
    </section>

    {{-- بيانات المتجر للبائع --}}
    @if ($user->vendorProfile)
        <section class="mb-4 rounded-2xl border border-gray-100 bg-white p-4">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="grid size-11 place-items-center overflow-hidden rounded-full bg-primary/10 text-sm font-extrabold text-primary"
                          @if ($user->vendorProfile->logo) style="background-image:url('{{ Storage::disk('public')->url($user->vendorProfile->logo) }}');background-size:cover;background-position:center" @endif>
                        @unless ($user->vendorProfile->logo){{ mb_substr($user->vendorProfile->store_name, 0, 1) }}@endunless
                    </span>
                    <div>
                        <p class="text-sm font-bold text-ink">{{ $user->vendorProfile->store_name }}</p>
                        <p class="text-[11px] {{ $user->vendorProfile->is_active ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $user->vendorProfile->is_active ? 'المتجر مفعّل ويظهر في السوق' : 'المتجر بانتظار موافقة الإدارة' }}
                        </p>
                    </div>
                </div>
                @if ($user->vendorProfile->is_active)
                    <a href="{{ route('stores.show', $user->vendorProfile) }}" class="text-[11px] font-bold text-primary hover:underline">عرض المتجر</a>
                @endif
            </div>
        </section>
    @endif

    {{-- بيانات التوصيل --}}
    @if ($user->deliveryProfile)
        <section class="mb-4 rounded-2xl border border-gray-100 bg-white p-4">
            <p class="text-sm font-bold text-ink">{{ $user->deliveryProfile->type_label }}</p>
            <p class="mt-1 text-[11px] text-gray-500">
                المنطقة: {{ $user->deliveryProfile->zone?->name ?? '—' }}
                · الشركة: {{ $user->deliveryProfile->company?->name ?? 'بدون شركة' }}
                · الحالة: {{ $user->deliveryProfile->is_active ? 'مفعّل' : 'قيد المراجعة' }}
            </p>
        </section>
    @endif

    {{-- إكمال بيانات الحساب (بائع / توصيل) — لا يظهر للأدمن --}}
    @unless ($user->hasRole('admin'))
    <section class="mb-4 rounded-3xl border border-gray-100 bg-white p-5">
        <h2 class="text-sm font-extrabold">إكمال بيانات الحساب</h2>
        <p class="mt-1 text-[11px] text-gray-500">
            يمكنك تطوير حسابك إلى بائع أو مندوب توصيل/مدير شركة، أو استكمال بياناتك الناقصة من هنا.
        </p>

        <div class="mt-3 space-y-2">
            {{-- بائع --}}
            <details class="rounded-2xl border border-gray-100 p-3" @if ($user->vendorProfile) open @endif>
                <summary class="cursor-pointer text-xs font-bold text-ink">
                    بيانات البائع / المتجر
                    @if ($user->vendorProfile)
                        <span class="ms-1 rounded-lg px-2 py-0.5 text-[10px] {{ $user->vendorProfile->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">
                            {{ $user->vendorProfile->is_active ? 'مفعّل' : 'قيد المراجعة' }}
                        </span>
                    @endif
                </summary>

                <form method="POST" action="{{ route('profile.complete') }}" class="mt-3 space-y-3">
                    @csrf
                    <input type="hidden" name="account_type" value="vendor">

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-600">اسم المتجر <span class="text-red-500">*</span></label>
                            <input name="store_name" value="{{ old('store_name', $user->vendorProfile?->store_name) }}" required
                                   class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-600">نوع المتجر <span class="text-red-500">*</span></label>
                            <select name="store_type" required
                                    class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                                <option value="">اختر النوع…</option>
                                @foreach ($storeTypes as $value => $label)
                                    <option value="{{ $value }}" @selected($user->vendorProfile?->store_type === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-600">المنطقة <span class="text-red-500">*</span></label>
                            <select name="zone_id" data-zone required
                                    class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                                <option value="">اختر…</option>
                                @foreach ($regions as $region)
                                    <option value="{{ $region->id }}" @selected($user->vendorProfile?->zone_id === $region->id)>{{ $region->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-600">الحي <span class="text-red-500">*</span></label>
                            <select name="area_id" data-area data-selected="{{ $user->vendorProfile?->area_id }}" required
                                    class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                                <option value="">اختر المنطقة أولًا…</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-semibold text-gray-600">باقي العنوان <span class="text-red-500">*</span></label>
                            <input name="address" value="{{ old('address', $user->vendorProfile?->address) }}" required
                                   placeholder="شارع الجلاء - بجانب المسجد"
                                   class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                        </div>
                    </div>

                    <button type="submit" class="rounded-xl bg-primary px-4 py-2 text-xs font-bold text-white">
                        {{ $user->vendorProfile ? 'تحديث بيانات المتجر' : 'افتح متجرًا' }}
                    </button>
                </form>
            </details>

            {{-- توصيل --}}
            <details class="rounded-2xl border border-gray-100 p-3" @if ($user->deliveryProfile) open @endif>
                <summary class="cursor-pointer text-xs font-bold text-ink">
                    بيانات التوصيل
                    @if ($user->deliveryProfile)
                        <span class="ms-1 rounded-lg px-2 py-0.5 text-[10px] {{ $user->deliveryProfile->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">
                            {{ $user->deliveryProfile->is_active ? 'مفعّل' : 'قيد المراجعة' }}
                        </span>
                    @endif
                </summary>

                <form method="POST" action="{{ route('profile.complete') }}" class="mt-3 space-y-3">
                    @csrf
                    <input type="hidden" name="account_type" value="delivery">

                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="delivery_type" value="driver" class="peer sr-only"
                                   @checked($user->deliveryProfile?->type !== 'manager')>
                            <span class="block rounded-xl border-2 border-gray-100 px-3 py-2 text-center text-xs font-bold transition peer-checked:border-secondary peer-checked:bg-secondary/10">مندوب توصيل</span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="delivery_type" value="manager" class="peer sr-only"
                                   @checked($user->deliveryProfile?->type === 'manager')>
                            <span class="block rounded-xl border-2 border-gray-100 px-3 py-2 text-center text-xs font-bold transition peer-checked:border-secondary peer-checked:bg-secondary/10">مدير شركة</span>
                        </label>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-600">منطقة العمل <span class="text-red-500">*</span></label>
                            <select name="zone_id" required
                                    class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                                <option value="">اختر…</option>
                                @foreach ($regions as $region)
                                    <option value="{{ $region->id }}" @selected($user->deliveryProfile?->zone_id === $region->id)>{{ $region->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-600">الشركة</label>
                            <select name="company_id"
                                    class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                                <option value="">بدون شركة (مستقل)</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}" @selected($user->deliveryProfile?->company_id === $company->id)>{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-semibold text-gray-600">أو أنشئ شركة جديدة (لمدير الشركة)</label>
                            <input name="company_name" value="{{ old('company_name') }}"
                                   placeholder="مثال: توصيل غزة السريع"
                                   class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                        </div>
                    </div>

                    <button type="submit" class="rounded-xl bg-secondary px-4 py-2 text-xs font-bold text-white">
                        {{ $user->deliveryProfile ? 'تحديث بيانات التوصيل' : 'انضم كوصول' }}
                    </button>
                </form>
            </details>
        </div>
    </section>
    @endunless

    {{-- تعديل البيانات --}}
    <section class="rounded-3xl border border-gray-100 bg-white p-5">
        <h2 class="mb-4 text-sm font-extrabold">تعديل البيانات</h2>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label for="name" class="mb-1 block text-xs font-semibold text-gray-600">الاسم</label>
                    <input id="name" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                </div>

                <div>
                    <label for="phone" class="mb-1 block text-xs font-semibold text-gray-600">رقم الجوال</label>
                    <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" dir="ltr"
                           class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-left text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                </div>

                <div class="sm:col-span-2">
                    <label for="address" class="mb-1 block text-xs font-semibold text-gray-600">العنوان</label>
                    <input id="address" name="address" value="{{ old('address', $user->address) }}"
                           class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15">
                </div>

                <div>
                    <label for="avatar" class="mb-1 block text-xs font-semibold text-gray-600">الصورة الشخصية</label>
                    <input id="avatar" name="avatar" type="file" accept="image/*"
                           class="w-full rounded-xl border border-dashed border-gray-300 px-3 py-2 text-xs text-gray-500 file:me-3 file:rounded-lg file:border-0 file:bg-primary/10 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-primary">
                    <p class="mt-1 text-[10px] text-gray-400">تظهر دائرية في كل الصفحات.</p>
                </div>

                <div>
                    <label for="cover" class="mb-1 block text-xs font-semibold text-gray-600">صورة الغلاف</label>
                    <input id="cover" name="cover" type="file" accept="image/*"
                           class="w-full rounded-xl border border-dashed border-gray-300 px-3 py-2 text-xs text-gray-500 file:me-3 file:rounded-lg file:border-0 file:bg-primary/10 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-primary">
                </div>
            </div>

            <button type="submit" class="rounded-xl bg-primary px-5 py-2.5 text-xs font-bold text-white transition hover:bg-primary-600">
                حفظ التغييرات
            </button>
        </form>
    </section>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const areas = @json($regions->mapWithKeys(fn ($region) => [$region->id => $region->children->map(fn ($child) => ['id' => $child->id, 'name' => $child->name])]));

            document.querySelectorAll('[data-zone]').forEach((zone) => {
                const form = zone.closest('form');
                const area = form?.querySelector('[data-area]');
                if (!area) return;

                const fill = () => {
                    const selected = area.dataset.selected;
                    const list = areas[zone.value] || [];
                    area.innerHTML = '<option value="">اختر الحي…</option>';
                    list.forEach((item) => {
                        const opt = document.createElement('option');
                        opt.value = item.id;
                        opt.textContent = item.name;
                        if (selected && String(item.id) === String(selected)) opt.selected = true;
                        area.appendChild(opt);
                    });
                };

                zone.addEventListener('change', () => {
                    area.dataset.selected = '';
                    fill();
                });

                fill();
            });
        });
    </script>
@endpush
