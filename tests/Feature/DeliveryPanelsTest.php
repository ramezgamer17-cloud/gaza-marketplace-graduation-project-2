<?php

namespace Tests\Feature;

use App\Models\DeliveryCompany;
use App\Models\DeliveryProfile;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DeliveryPanelsTest extends TestCase
{
    use RefreshDatabase;

    protected function zone(): DeliveryZone
    {
        return DeliveryZone::create(['name' => 'الشمال', 'slug' => 'z-'.uniqid()]);
    }

    protected function driver(bool $active = true, ?DeliveryCompany $company = null): array
    {
        Role::firstOrCreate(['name' => 'delivery', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole('delivery');

        $profile = DeliveryProfile::create([
            'user_id' => $user->id,
            'company_id' => $company?->id,
            'type' => DeliveryProfile::TYPE_DRIVER,
            'zone_id' => $this->zone()->id,
            'is_active' => $active,
        ]);

        return [$user, $profile];
    }

    protected function manager(bool $active = true): array
    {
        Role::firstOrCreate(['name' => 'delivery', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole('delivery');

        $company = DeliveryCompany::create([
            'name' => 'شركة '.uniqid(),
            'is_active' => true,
            'manager_id' => $user->id,
        ]);

        $profile = DeliveryProfile::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'type' => DeliveryProfile::TYPE_MANAGER,
            'zone_id' => $this->zone()->id,
            'is_active' => $active,
        ]);

        return [$user, $company, $profile];
    }

    protected function order(array $attributes = []): Order
    {
        $customer = User::factory()->create();
        $vendor = User::factory()->create();

        return Order::create(array_merge([
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'subtotal' => 20,
            'delivery_fee' => 0,
            'total' => 20,
            'customer_name' => 'زبون',
            'customer_phone' => '0599000000',
            'shipping_address' => 'غزة',
        ], $attributes));
    }

    public function test_active_driver_can_open_driver_panel(): void
    {
        [$driver] = $this->driver();

        $this->actingAs($driver)->get('/delivery')->assertSuccessful();
        $this->actingAs($driver)->get('/delivery/orders')->assertSuccessful();
    }

    public function test_pending_driver_cannot_open_driver_panel(): void
    {
        [$driver] = $this->driver(active: false);

        $this->actingAs($driver)->get('/delivery')->assertForbidden();
    }

    public function test_driver_sees_only_his_assigned_orders(): void
    {
        [$driver] = $this->driver();
        [$otherDriver] = $this->driver();

        $mine = $this->order(['delivery_user_id' => $driver->id, 'customer_name' => 'زبون تابع لي']);
        $others = $this->order(['delivery_user_id' => $otherDriver->id, 'customer_name' => 'زبون مندوب آخر']);

        $this->actingAs($driver)
            ->get('/delivery/orders')
            ->assertSuccessful()
            ->assertSee($mine->order_number)
            ->assertDontSee($others->order_number);
    }

    public function test_driver_cannot_open_company_panel(): void
    {
        [$driver] = $this->driver();

        $this->actingAs($driver)->get('/company')->assertForbidden();
    }

    public function test_manager_sees_company_orders_only(): void
    {
        [$manager, $company] = $this->manager();

        $otherCompany = DeliveryCompany::create(['name' => 'شركة أخرى', 'is_active' => true]);

        $ours = $this->order(['delivery_company_id' => $company->id, 'customer_name' => 'زبون شركتنا']);
        $theirs = $this->order(['delivery_company_id' => $otherCompany->id, 'customer_name' => 'زبون شركة أخرى']);

        $this->actingAs($manager)
            ->get('/company')
            ->assertSuccessful();

        $this->actingAs($manager)
            ->get('/company/orders')
            ->assertSuccessful()
            ->assertSee($ours->order_number)
            ->assertDontSee($theirs->order_number);
    }

    public function test_manager_can_see_company_drivers(): void
    {
        [$manager, $company] = $this->manager();
        [$driver] = $this->driver(company: $company);

        $this->actingAs($manager)
            ->get('/company/drivers')
            ->assertSuccessful()
            ->assertSee($driver->name);
    }

    public function test_manager_cannot_open_driver_panel(): void
    {
        [$manager] = $this->manager();

        $this->actingAs($manager)->get('/delivery')->assertForbidden();
    }

    public function test_manager_with_inactive_profile_cannot_open_company_panel(): void
    {
        [$manager] = $this->manager(active: false);

        $this->actingAs($manager)->get('/company')->assertForbidden();
    }

    public function test_delivery_user_sees_his_panel_link_on_account_page(): void
    {
        [$driver] = $this->driver();

        $this->actingAs($driver)
            ->get('/account')
            ->assertSuccessful()
            ->assertSee('لوحة التوصيل');

        [$manager] = $this->manager();

        $this->actingAs($manager)
            ->get('/account')
            ->assertSuccessful()
            ->assertSee('لوحة الشركة');
    }

    public function test_storefront_menu_shows_delivery_links_for_delivery_users(): void
    {
        [$driver] = $this->driver();

        $this->actingAs($driver)
            ->get('/')
            ->assertSuccessful()
            ->assertSee('توصيلاتي');

        [$manager] = $this->manager();

        $this->actingAs($manager)
            ->get('/')
            ->assertSuccessful()
            ->assertSee('التوصيلات');
    }

    public function test_role_home_routes_delivery_users_correctly(): void
    {
        [$driver] = $this->driver();
        [$manager] = $this->manager();
        [$pending] = $this->driver(active: false);

        $this->assertSame('/delivery', \App\Support\RoleHome::url($driver->fresh()));
        $this->assertSame('/company', \App\Support\RoleHome::url($manager->fresh()));
        $this->assertSame('/pending', \App\Support\RoleHome::url($pending->fresh()));
    }
}
