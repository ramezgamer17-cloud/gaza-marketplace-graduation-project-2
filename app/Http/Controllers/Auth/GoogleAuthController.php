<?php

namespace App\Http\Controllers\Auth;

use App\Models\DeliveryProfile;
use App\Models\User;
use App\Models\VendorProfile;
use App\Support\RoleHome;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;
use Throwable;

class GoogleAuthController extends \Illuminate\Routing\Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        if (blank(config('services.google.client_id')) || blank(config('services.google.client_secret'))) {
            return back()->with('error', 'تسجيل الدخول بجوجل غير مفعّل. أضف مفاتيح Google في ملف .env.');
        }

        $type = $request->query('type', 'customer');

        if (! in_array($type, ['customer', 'vendor', 'delivery'], true)) {
            $type = 'customer';
        }

        $request->session()->put('google_account_type', Auth::check() ? null : $type);

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('login')
                ->with('error', 'تعذّر إكمال تسجيل الدخول بجوجل. حاول مرة أخرى.');
        }

        $type = $request->session()->pull('google_account_type', 'customer');
        $type = in_array($type, ['customer', 'vendor', 'delivery'], true) ? $type : 'customer';

        $user = User::query()
            ->where('provider', 'google')
            ->where('provider_id', $googleUser->getId())
            ->first()
            ?? User::query()->where('email', $googleUser->getEmail())->first();

        if ($user) {
            return $this->loginExisting($request, $user, $googleUser->getId());
        }

        $user = DB::transaction(function () use ($googleUser, $type) {
            $user = User::create([
                'name' => $googleUser->getName() ?: 'مستخدم جوجل',
                'email' => $googleUser->getEmail(),
                'password' => Hash::make(Str::random(40)),
                'provider' => 'google',
                'provider_id' => $googleUser->getId(),
                'avatar' => $this->storeAvatar($googleUser->getAvatar()),
                'email_verified_at' => now(),
                'is_active' => true,
            ]);

            Role::firstOrCreate(['name' => $type, 'guard_name' => 'web']);
            $user->assignRole($type);

            if ($type === 'vendor') {
                VendorProfile::create([
                    'user_id' => $user->id,
                    'store_name' => $user->name,
                    'store_type' => 'other',
                    'is_active' => false,
                ]);
            }

            if ($type === 'delivery') {
                DeliveryProfile::create([
                    'user_id' => $user->id,
                    'type' => DeliveryProfile::TYPE_DRIVER,
                    'is_active' => false,
                ]);
            }

            return $user;
        });

        Auth::login($user, true);
        $request->session()->regenerate();

        $message = match ($type) {
            'vendor' => 'تم إنشاء حسابك بجوجل. أكمل بيانات متجرك من صفحة حسابك.',
            'delivery' => 'تم إنشاء حساب التوصيل بجوجل. أكمل بياناتك (المنطقة والشركة) من صفحة حسابك.',
            default => 'أهلًا بك في سوق غزة! أكمل بياناتك إن أردت.',
        };

        // المستخدم الجديد يكمل بياناته من صفحة الحساب
        return redirect()->route('profile.show')->with('status', $message);
    }

    protected function loginExisting(Request $request, User $user, string $providerId): RedirectResponse
    {
        if (! $user->is_active) {
            return redirect()->route('login')->with('error', 'هذا الحساب معطّل. تواصل مع إدارة المنصة.');
        }

        if (blank($user->provider)) {
            $user->forceFill([
                'provider' => 'google',
                'provider_id' => $providerId,
            ])->save();
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->to(RoleHome::url($user))->with('status', 'تم تسجيل الدخول بجوجل.');
    }

    /**
     * تنزيل صورة جوجل وحفظها محليًا (بدل ربط رابط خارجي).
     */
    protected function storeAvatar(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        try {
            $response = Http::timeout(8)->get($url);

            if (! $response->successful()) {
                return null;
            }

            $path = 'avatars/'.Str::random(32).'.jpg';
            \Illuminate\Support\Facades\Storage::disk('public')->put($path, $response->body());

            return $path;
        } catch (Throwable) {
            return null;
        }
    }
}
