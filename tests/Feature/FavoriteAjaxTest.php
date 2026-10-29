<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteAjaxTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_favorite_toggle_returns_json(): void
    {
        $customer = User::factory()->create();
        $vendor = User::factory()->create();

        VendorProfile::create([
            'user_id' => $vendor->id,
            'store_name' => 'متجر',
            'store_type' => 'supermarket',
            'is_active' => true,
        ]);

        $product = Product::create([
            'user_id' => $vendor->id,
            'name' => 'خيار أحمر',
            'price' => 4,
            'stock' => 20,
            'is_active' => true,
        ]);

        $this->actingAs($customer)
            ->postJson(route('favorites.toggle', $product))
            ->assertOk()
            ->assertJson(['ok' => true, 'favorited' => true, 'count' => 1]);

        $this->actingAs($customer)
            ->postJson(route('favorites.toggle', $product))
            ->assertOk()
            ->assertJson(['ok' => true, 'favorited' => false, 'count' => 0]);

        $this->assertDatabaseCount('favorites', 0);
    }
}
