<?php

namespace Database\Seeders;

use App\Models\DeliveryCompany;
use App\Models\DeliveryProfile;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DeliveryDemoSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'delivery', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'vendor', 'guard_name' => 'web']);

        $zone = DeliveryZone::query()->whereNull('parent_id')->where('name', 'الشمال')->first()
            ?? DeliveryZone::query()->whereNull('parent_id')->firstOrFail();

        $manager = User::firstOrCreate(
            ['email' => 'manager@demo.test'],
            [
                'name' => 'مدير شركة التوصيل',
                'phone' => '0599444001',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $manager->syncRoles(['delivery']);

        $company = DeliveryCompany::firstOrCreate(
            ['name' => 'توصيل غزة السريع'],
            [
                'phone' => '0599444000',
                'email' => 'info@fastgaza.test',
                'address' => 'غزة - شارع الوحدة',
                'zone_id' => $zone->id,
                'manager_id' => $manager->id,
                'is_active' => true,
            ]
        );

        DeliveryProfile::updateOrCreate(
            ['user_id' => $manager->id],
            [
                'company_id' => $company->id,
                'type' => DeliveryProfile::TYPE_MANAGER,
                'zone_id' => $zone->id,
                'address' => 'غزة',
                'is_active' => true,
            ]
        );

        $driver = User::firstOrCreate(
            ['email' => 'delivery@demo.test'],
            [
                'name' => 'مندوب التوصيل',
                'phone' => '0599333001',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $driver->syncRoles(['delivery']);

        DeliveryProfile::updateOrCreate(
            ['user_id' => $driver->id],
            [
                'company_id' => $company->id,
                'type' => DeliveryProfile::TYPE_DRIVER,
                'zone_id' => $zone->id,
                'address' => 'جباليا',
                'vehicle_type' => 'دراجة',
                'is_active' => true,
            ]
        );

        // إسناد أول طلب للشركة والمندوب
        $order = Order::query()->whereNull('delivery_company_id')->oldest()->first();

        if ($order) {
            $order->update([
                'delivery_company_id' => $company->id,
                'delivery_user_id' => $driver->id,
                'status' => Order::STATUS_SHIPPED,
            ]);
        }

        $this->command?->info('Delivery demo ready: company, manager@demo.test, driver delivery@demo.test');
    }
}
