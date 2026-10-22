<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndAdminSeeder extends Seeder
{
    /**
     * إنشاء الأدوار الأساسية وحساب الأدمن.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roles = ['admin', 'vendor', 'customer', 'delivery', 'delivery_manager'];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@multistore.test'],
            [
                'name' => 'مدير النظام',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $admin->syncRoles(['admin']);

        $this->command?->info("Admin: {$admin->email} / password  (roles: admin, vendor, customer, delivery)");
    }
}
