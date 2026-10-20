<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\VendorSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    protected function makeStore(bool $active = true): array
    {
        $vendor = User::factory()->create(['name' => 'بائع']);
        $store = VendorProfile::create([
            'user_id' => $vendor->id,
            'store_name' => 'شاورما الرمال',
            'store_type' => 'restaurant',
            'description' => 'أشهى شاورما في غزة',
            'is_active' => $active,
            'delivery_time' => 25,
        ]);

        $category = Category::create(['name' => 'مطاعم']);
        $section = VendorSection::create(['user_id' => $vendor->id, 'name' => 'ساندويشات']);

        $product = Product::create([
            'user_id' => $vendor->id,
            'category_id' => $category->id,
            'section_id' => $section->id,
            'name' => 'شاورما دجاج',
            'price' => 15,
            'compare_at_price' => 18,
            'stock' => 10,
            'is_active' => true,
            'is_featured' => true,
        ]);

        return [$store, $product, $section];
    }

    public function test_home_page_shows_stores_and_products(): void
    {
        $this->makeStore();

        $this->get('/')
            ->assertSuccessful()
            ->assertSee('سوق غزة')
            ->assertSee('شاورما الرمال')
            ->assertSee('شاورما دجاج')
            ->assertSee('مطاعم');
    }

    public function test_home_page_shows_a_showcase_section_per_store(): void
    {
        $this->makeStore();

        $vendor = User::factory()->create();
        VendorProfile::create([
            'user_id' => $vendor->id,
            'store_name' => 'كهرباء السلام',
            'store_type' => 'electronics',
            'is_active' => true,
        ]);
        Product::create([
            'user_id' => $vendor->id,
            'name' => 'لمبة LED',
            'price' => 12,
            'stock' => 5,
            'is_active' => true,
            'is_featured' => true,
        ]);

        $this->get('/')
            ->assertSuccessful()
            ->assertSee('مختارات المتاجر')
            ->assertSee('شاورما الرمال')
            ->assertSee('كهرباء السلام')
            ->assertSee('لمبة LED')
            ->assertSee('زيارة المتجر')
            ->assertSee('data-product', false);
    }

    public function test_home_page_does_not_show_inactive_stores(): void
    {
        [$store] = $this->makeStore(active: false);

        $this->get('/')->assertSuccessful()->assertDontSee('شاورما الرمال');
    }

    public function test_store_page_shows_sections_and_products(): void
    {
        [$store] = $this->makeStore();

        $this->get(route('stores.show', $store))
            ->assertSuccessful()
            ->assertSee('شاورما الرمال')
            ->assertSee('ساندويشات')
            ->assertSee('شاورما دجاج')
            ->assertSee('15.00');
    }

    public function test_stores_index_lists_and_filters_stores(): void
    {
        $this->makeStore();

        $vendor = User::factory()->create();
        VendorProfile::create([
            'user_id' => $vendor->id,
            'store_name' => 'كهرباء السلام',
            'store_type' => 'electronics',
            'is_active' => true,
        ]);

        $this->get('/stores')
            ->assertSuccessful()
            ->assertSee('شاورما الرمال')
            ->assertSee('كهرباء السلام');

        $this->get('/stores?q=كهرباء')
            ->assertSuccessful()
            ->assertSee('كهرباء السلام')
            ->assertDontSee('شاورما الرمال');
    }

    public function test_inactive_store_is_hidden_from_index(): void
    {
        $this->makeStore(active: false);

        $this->get('/stores')->assertSuccessful()->assertDontSee('شاورما الرمال');
    }

    public function test_inactive_store_returns_404(): void
    {
        [$store] = $this->makeStore(active: false);

        $this->get(route('stores.show', $store))->assertNotFound();
    }

    public function test_vendor_sees_a_toast_when_browsing_the_marketplace(): void
    {
        $this->makeStore();

        $vendor = User::factory()->create();
        $vendor->assignRole(\Spatie\Permission\Models\Role::firstOrCreate(['name' => 'vendor', 'guard_name' => 'web']));
        \App\Models\VendorProfile::create([
            'user_id' => $vendor->id,
            'store_name' => 'متجر البائع',
            'store_type' => 'supermarket',
        ]);

        $this->actingAs($vendor)
            ->get('/')
            ->assertSuccessful()
            ->assertSee('أنت تتصفّح السوق كزبون')
            ->assertSee('العودة إلى لوحة البائع')
            ->assertSee('role-toast', false);
    }

    public function test_customer_does_not_see_the_role_toast(): void
    {
        $this->makeStore();

        $customer = User::factory()->create();
        $customer->assignRole(\Spatie\Permission\Models\Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']));

        $this->actingAs($customer)
            ->get('/')
            ->assertSuccessful()
            ->assertDontSee('role-toast', false);
    }

    public function test_guest_can_open_home_and_login(): void
    {
        $this->get('/')->assertSuccessful();
        $this->get('/login')->assertSuccessful()->assertSee('تسجيل الدخول');
    }
}
