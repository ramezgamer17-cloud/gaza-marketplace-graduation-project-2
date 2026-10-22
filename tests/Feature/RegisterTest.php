<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_renders(): void
    {
        $this->get('/register')
            ->assertSuccessful()
            ->assertSee('إنشاء الحساب')
            ->assertSee('بائع')
            ->assertSee('زبون');
    }

    public function test_customer_can_register(): void
    {
        $response = $this->post('/register', [
            'account_type' => 'customer',
            'name' => 'زبون جديد',
            'email' => 'new.customer@test.local',
            'phone' => '0599000111',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/');

        $user = User::where('email', 'new.customer@test.local')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->hasRole('customer'));
        $this->assertNull($user->vendorProfile);
    }

    protected function zoneWithArea(): array
    {
        $zone = \App\Models\DeliveryZone::create(['name' => 'الشمال', 'slug' => 'north-'.uniqid()]);
        $area = \App\Models\DeliveryZone::create(['name' => 'جباليا', 'slug' => 'jabalia-'.uniqid(), 'parent_id' => $zone->id]);

        return [$zone, $area];
    }

    public function test_vendor_can_register_and_store_is_pending(): void
    {
        [$zone, $area] = $this->zoneWithArea();

        $response = $this->post('/register', [
            'account_type' => 'vendor',
            'name' => 'بائع جديد',
            'email' => 'new.vendor@test.local',
            'phone' => '0599000222',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'store_name' => 'متجر التجربة',
            'store_type' => 'supermarket',
            'zone_id' => $zone->id,
            'area_id' => $area->id,
            'address' => 'شارع الجلاء',
        ]);

        $response->assertRedirect('/vendor');

        $user = User::where('email', 'new.vendor@test.local')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->hasRole('vendor'));

        $store = $user->vendorProfile;
        $this->assertNotNull($store);
        $this->assertSame('متجر التجربة', $store->store_name);
        $this->assertSame($zone->id, $store->zone_id);
        $this->assertSame($area->id, $store->area_id);
        $this->assertFalse($store->is_active, 'متجر البائع الجديد يجب أن ينتظر موافقة الإدارة');

        // المتجر غير المفعّل لا يظهر في السوق
        $this->get('/')->assertDontSee('متجر التجربة');
        $this->get('/stores')->assertDontSee('متجر التجربة');
    }

    public function test_vendor_fields_are_required_for_vendor_account(): void
    {
        $this->post('/register', [
            'account_type' => 'vendor',
            'name' => 'بائع',
            'email' => 'vendor.missing@test.local',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors(['store_name', 'store_type', 'zone_id', 'area_id', 'address']);

        $this->assertDatabaseMissing('users', ['email' => 'vendor.missing@test.local']);
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'used@test.local']);

        $this->post('/register', [
            'account_type' => 'customer',
            'name' => 'زبون',
            'email' => 'used@test.local',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('email');
    }

    public function test_password_must_be_confirmed(): void
    {
        $this->post('/register', [
            'account_type' => 'customer',
            'name' => 'زبون',
            'email' => 'mismatch@test.local',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ])->assertSessionHasErrors('password');
    }

    public function test_vendor_requires_address(): void
    {
        [$zone, $area] = $this->zoneWithArea();

        $this->post('/register', [
            'account_type' => 'vendor',
            'name' => 'بائع بلا عنوان',
            'email' => 'vendor.no.address@test.local',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'store_name' => 'متجر',
            'store_type' => 'bakery',
            'zone_id' => $zone->id,
            'area_id' => $area->id,
        ])->assertSessionHasErrors('address');
    }

    public function test_customer_address_is_optional(): void
    {
        $this->post('/register', [
            'account_type' => 'customer',
            'name' => 'زبون بلا عنوان',
            'email' => 'customer.no.address@test.local',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'customer.no.address@test.local']);
    }

    public function test_delivery_driver_can_register_pending(): void
    {
        $zone = \App\Models\DeliveryZone::create(['name' => 'الشمال', 'slug' => 'north']);

        $response = $this->post('/register', [
            'account_type' => 'delivery',
            'delivery_type' => 'driver',
            'name' => 'مندوب جديد',
            'email' => 'driver@test.local',
            'phone' => '0599000333',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'zone_id' => $zone->id,
            'address' => 'جباليا - الشارع العام',
        ]);

        $response->assertRedirect('/pending');

        $user = User::where('email', 'driver@test.local')->firstOrFail();
        $this->assertTrue($user->hasRole('delivery'));

        $profile = $user->deliveryProfile;
        $this->assertNotNull($profile);
        $this->assertSame('driver', $profile->type);
        $this->assertFalse($profile->is_active);
        $this->assertSame($zone->id, $profile->zone_id);
        $this->assertSame('جباليا - الشارع العام', $profile->address);
    }

    public function test_delivery_manager_creates_pending_company(): void
    {
        $zone = \App\Models\DeliveryZone::create(['name' => 'الجنوب', 'slug' => 'south']);

        $this->post('/register', [
            'account_type' => 'delivery',
            'delivery_type' => 'manager',
            'name' => 'مدير شركة',
            'email' => 'manager@test.local',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'zone_id' => $zone->id,
            'address' => 'خان يونس',
            'company_name' => 'توصيل الجنوب',
        ])->assertRedirect('/pending');

        $user = User::where('email', 'manager@test.local')->firstOrFail();
        $company = \App\Models\DeliveryCompany::where('name', 'توصيل الجنوب')->firstOrFail();

        $this->assertSame($user->id, $company->manager_id);
        $this->assertFalse($company->is_active);
        $this->assertSame('manager', $user->deliveryProfile->type);
        $this->assertSame($company->id, $user->deliveryProfile->company_id);
    }

    public function test_delivery_requires_zone_only(): void
    {
        $this->post('/register', [
            'account_type' => 'delivery',
            'delivery_type' => 'driver',
            'name' => 'مندوب',
            'email' => 'driver.missing@test.local',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors(['zone_id'])
            ->assertSessionDoesntHaveErrors(['address']);
    }

    public function test_google_button_is_present_in_register_page(): void
    {
        $this->get('/register')
            ->assertSuccessful()
            ->assertSee('جوجل');
    }

    public function test_pending_vendor_sees_notice_in_panel(): void
    {
        Role::firstOrCreate(['name' => 'vendor', 'guard_name' => 'web']);

        $vendor = User::factory()->create();
        $vendor->assignRole('vendor');

        VendorProfile::create([
            'user_id' => $vendor->id,
            'store_name' => 'متجر قيد المراجعة',
            'store_type' => 'supermarket',
            'is_active' => false,
        ]);

        $this->actingAs($vendor)
            ->get('/vendor')
            ->assertSuccessful()
            ->assertSee('بانتظار موافقة الإدارة');
    }
}
