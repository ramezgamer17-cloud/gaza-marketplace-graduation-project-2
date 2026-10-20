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

    public function test_inactive_store_returns_404(): void
    {
        [$store] = $this->makeStore(active: false);

        $this->get(route('stores.show', $store))->assertNotFound();
    }

    public function test_guest_can_open_home_and_login(): void
    {
        $this->get('/')->assertSuccessful();
        $this->get('/login')->assertSuccessful()->assertSee('تسجيل الدخول');
    }
}
