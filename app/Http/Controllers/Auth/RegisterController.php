<?php

namespace App\Http\Controllers\Auth;

use App\Models\DeliveryCompany;
use App\Models\DeliveryProfile;
use App\Models\DeliveryZone;
use App\Models\User;
use App\Models\VendorProfile;
use App\Support\RoleHome;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisterController extends \Illuminate\Routing\Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'storeTypes' => VendorProfile::storeTypes(),
            'regions' => DeliveryZone::query()->regions()->with('children')->get(),
            'companies' => DeliveryCompany::query()->where('is_active', true)->orderBy('name')->get(),
            'googleEnabled' => filled(config('services.google.client_id')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'account_type' => ['required', 'in:customer,vendor,delivery'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::min(8)],

            // العنوان: إجباري للبائع، اختياري للزبون، وغير مطلوب للمندوب (منطقة العمل تكفي)
            'address' => ['required_if:account_type,vendor', 'nullable', 'string', 'max:255'],

            // بيانات المتجر (بائع)
            'store_name' => ['required_if:account_type,vendor', 'nullable', 'string', 'max:255'],
            'store_type' => ['required_if:account_type,vendor', 'nullable', 'string', 'max:50'],

            // بيانات التوصيل
            'delivery_type' => ['required_if:account_type,delivery', 'nullable', 'in:driver,manager'],
            'zone_id' => ['required_if:account_type,vendor,delivery', 'nullable', 'exists:delivery_zones,id'],
            'area_id' => ['required_if:account_type,vendor', 'nullable', 'exists:delivery_zones,id'],
            'company_id' => ['nullable', 'exists:delivery_companies,id'],
            'company_name' => ['required_if:delivery_type,manager', 'nullable', 'string', 'max:255'],
        ], [], [
            'name' => 'الاسم',
            'email' => 'البريد الإلكتروني',
            'phone' => 'رقم الجوال',
            'password' => 'كلمة المرور',
            'store_name' => 'اسم المتجر',
            'store_type' => 'نوع المتجر',
            'address' => 'العنوان',
            'account_type' => 'نوع الحساب',
            'delivery_type' => 'نوع حساب التوصيل',
            'zone_id' => 'المنطقة',
            'area_id' => 'الحي',
            'company_id' => 'شركة التوصيل',
            'company_name' => 'اسم الشركة',
        ]);

        $type = $validated['account_type'];

        $user = DB::transaction(function () use ($validated, $type) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'password' => Hash::make($validated['password']),
                'is_active' => true,
            ]);

            Role::firstOrCreate(['name' => $type, 'guard_name' => 'web']);
            $user->assignRole($type);

            if ($type === 'vendor') {
                VendorProfile::create([
                    'user_id' => $user->id,
                    'store_name' => $validated['store_name'],
                    'store_type' => $validated['store_type'],
                    'phone' => $validated['phone'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'zone_id' => $validated['zone_id'] ?? null,
                    'area_id' => $validated['area_id'] ?? null,
                    'is_active' => false,
                ]);
            }

            if ($type === 'delivery') {
                $companyId = $validated['company_id'] ?? null;

                if ($validated['delivery_type'] === 'manager' && filled($validated['company_name'] ?? null)) {
                    $company = DeliveryCompany::create([
                        'name' => $validated['company_name'],
                        'phone' => $validated['phone'] ?? null,
                        'address' => $validated['address'] ?? null,
                        'zone_id' => $validated['zone_id'],
                        'manager_id' => $user->id,
                        'is_active' => false,
                    ]);

                    $companyId = $company->id;
                }

                DeliveryProfile::create([
                    'user_id' => $user->id,
                    'company_id' => $companyId,
                    'type' => $validated['delivery_type'],
                    'zone_id' => $validated['zone_id'],
                    'address' => $validated['address'] ?? null,
                    'is_active' => false,
                ]);
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        $message = match ($type) {
            'vendor' => 'تم إنشاء حساب البائع. متجرك بانتظار موافقة الإدارة قبل ظهوره في السوق.',
            'delivery' => 'تم إنشاء حساب التوصيل. سيتم تفعيله بعد مراجعة الإدارة.',
            default => 'أهلًا بك في سوق غزة! تم إنشاء حسابك بنجاح.',
        };

        return redirect()->to(RoleHome::url($user))->with('status', $message);
    }
}
