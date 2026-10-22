<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_account_page(): void
    {
        $this->get('/account')->assertRedirect('/login');
    }

    public function test_user_can_see_account_page(): void
    {
        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);

        $user = User::factory()->create(['name' => 'زبون تجريبي']);
        $user->assignRole('customer');

        $this->actingAs($user)
            ->get('/account')
            ->assertSuccessful()
            ->assertSee('زبون تجريبي')
            ->assertSee('تعديل البيانات')
            ->assertSee('طلباتي');
    }

    public function test_user_can_update_profile_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/account', [
                'name' => 'اسم جديد',
                'phone' => '0599123456',
                'address' => 'غزة - الرمال',
            ])
            ->assertRedirect();

        $user->refresh();
        $this->assertSame('اسم جديد', $user->name);
        $this->assertSame('0599123456', $user->phone);
        $this->assertSame('غزة - الرمال', $user->address);
    }

    public function test_user_can_upload_avatar_and_it_is_stored(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/account', [
                'name' => $user->name,
                'avatar' => UploadedFile::fake()->image('me.jpg', 300, 300),
            ])
            ->assertRedirect();

        $user->refresh();
        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
    }

    public function test_customer_can_upgrade_to_vendor_from_account(): void
    {
        $zone = \App\Models\DeliveryZone::create(['name' => 'الشمال', 'slug' => 'n1']);
        $area = \App\Models\DeliveryZone::create(['name' => 'جباليا', 'slug' => 'a1', 'parent_id' => $zone->id]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/account/complete', [
                'account_type' => 'vendor',
                'store_name' => 'متجري الجديد',
                'store_type' => 'bakery',
                'zone_id' => $zone->id,
                'area_id' => $area->id,
                'address' => 'شارع الجلاء',
            ])
            ->assertRedirect(route('profile.show'));

        $user->refresh();
        $this->assertTrue($user->hasRole('vendor'));
        $this->assertSame('متجري الجديد', $user->vendorProfile->store_name);
        $this->assertSame($area->id, $user->vendorProfile->area_id);
        $this->assertFalse($user->vendorProfile->is_active);
    }

    public function test_customer_can_join_delivery_from_account(): void
    {
        $zone = \App\Models\DeliveryZone::create(['name' => 'الوسطى', 'slug' => 'c1']);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/account/complete', [
                'account_type' => 'delivery',
                'delivery_type' => 'driver',
                'zone_id' => $zone->id,
            ])
            ->assertRedirect(route('profile.show'));

        $user->refresh();
        $this->assertTrue($user->hasRole('delivery'));
        $this->assertSame('driver', $user->deliveryProfile->type);
        $this->assertFalse($user->deliveryProfile->is_active);
    }
}
