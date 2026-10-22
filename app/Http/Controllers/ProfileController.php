<?php

namespace App\Http\Controllers;

use App\Models\DeliveryCompany;
use App\Models\DeliveryProfile;
use App\Models\DeliveryZone;
use App\Models\VendorProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class ProfileController extends Controller
{
    public function show(): View
    {
        $user = auth()->user()->load(['vendorProfile', 'deliveryProfile.company', 'deliveryProfile.zone']);

        return view('profile.show', [
            'user' => $user,
            'ordersCount' => $user->orders()->count(),
            'favoritesCount' => $user->favorites()->count(),
            'regions' => DeliveryZone::query()->regions()->with('children')->get(),
            'companies' => DeliveryCompany::query()->where('is_active', true)->orderBy('name')->get(),
            'storeTypes' => VendorProfile::storeTypes(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'cover' => ['nullable', 'image', 'max:4096'],
        ], [], [
            'name' => 'الاسم',
            'phone' => 'رقم الجوال',
            'address' => 'العنوان',
            'avatar' => 'الصورة الشخصية',
            'cover' => 'صورة الغلاف',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if ($request->hasFile('cover')) {
            if ($user->cover) {
                Storage::disk('public')->delete($user->cover);
            }

            $validated['cover'] = $request->file('cover')->store('covers', 'public');
        }

        $user->update($validated);

        return back()->with('status', 'تم تحديث بياناتك بنجاح.');
    }

    /**
     * إكمال بيانات الحساب: التحول إلى بائع أو مندوب/مدير شركة توصيل،
     * أو استكمال بيانات الملف الموجود.
     */
    public function complete(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'account_type' => ['required', 'in:vendor,delivery'],

            'store_name' => ['required_if:account_type,vendor', 'nullable', 'string', 'max:255'],
            'store_type' => ['required_if:account_type,vendor', 'nullable', 'string', 'max:50'],
            'address' => ['required_if:account_type,vendor', 'nullable', 'string', 'max:255'],

            'delivery_type' => ['required_if:account_type,delivery', 'nullable', 'in:driver,manager'],
            'company_id' => ['nullable', 'exists:delivery_companies,id'],
            'company_name' => ['nullable', 'string', 'max:255'],

            'zone_id' => ['required', 'exists:delivery_zones,id'],
            'area_id' => ['nullable', 'exists:delivery_zones,id'],
        ], [], [
            'account_type' => 'نوع الحساب',
            'store_name' => 'اسم المتجر',
            'store_type' => 'نوع المتجر',
            'address' => 'العنوان',
            'zone_id' => 'المنطقة',
            'area_id' => 'الحي',
            'delivery_type' => 'نوع حساب التوصيل',
            'company_name' => 'اسم الشركة',
        ]);

        // مدير الشركة يجب أن يحدد شركة قائمة أو اسم شركة جديدة
        if (
            $validated['account_type'] === 'delivery'
            && ($validated['delivery_type'] ?? null) === 'manager'
            && blank($validated['company_name'] ?? null)
            && blank($validated['company_id'] ?? null)
        ) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'company_name' => 'اسم الشركة مطلوب عند اختيار «مدير شركة».',
            ]);
        }

        DB::transaction(function () use ($user, $validated) {
            if ($validated['account_type'] === 'vendor') {
                Role::firstOrCreate(['name' => 'vendor', 'guard_name' => 'web']);

                if (! $user->hasRole('vendor')) {
                    $user->assignRole('vendor');
                }

                VendorProfile::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'store_name' => $validated['store_name'],
                        'store_type' => $validated['store_type'],
                        'zone_id' => $validated['zone_id'],
                        'area_id' => $validated['area_id'] ?? null,
                        'address' => $validated['address'] ?? null,
                        'phone' => $user->phone,
                        'is_active' => $user->vendorProfile?->is_active ?? false,
                    ]
                );

                return;
            }

            $roleName = ($validated['delivery_type'] ?? null) === 'manager' ? 'delivery_manager' : 'delivery';
            $otherRole = $roleName === 'delivery_manager' ? 'delivery' : 'delivery_manager';

            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            if (! $user->hasRole($roleName)) {
                $user->assignRole($roleName);
            }

            if ($user->hasRole($otherRole)) {
                $user->removeRole($otherRole);
            }

            $companyId = $validated['company_id'] ?? null;

            if ($validated['delivery_type'] === 'manager' && filled($validated['company_name'] ?? null)) {
                $company = DeliveryCompany::firstOrCreate(
                    ['name' => $validated['company_name']],
                    [
                        'manager_id' => $user->id,
                        'zone_id' => $validated['zone_id'],
                        'is_active' => false,
                    ]
                );

                $companyId = $company->id;
            }

            DeliveryProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'company_id' => $companyId,
                    'type' => $validated['delivery_type'],
                    'zone_id' => $validated['zone_id'],
                    'address' => $user->address,
                    'is_active' => $user->deliveryProfile?->is_active ?? false,
                ]
            );
        });

        return redirect()->route('profile.show')->with('status', 'تم حفظ بيانات الحساب. ستُراجعها الإدارة للتفعيل.');
    }
}
