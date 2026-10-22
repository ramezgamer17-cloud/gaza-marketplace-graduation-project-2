<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoritesTest extends TestCase
{
    use RefreshDatabase;

    protected function product(): Product
    {
        $vendor = User::factory()->create();

        return Product::create([
            'user_id' => $vendor->id,
            'name' => 'تونة',
            'price' => 6.5,
            'stock' => 10,
            'is_active' => true,
        ]);
    }

    public function test_guest_cannot_add_to_favorites(): void
    {
        $product = $this->product();

        $this->post(route('favorites.toggle', $product))->assertRedirect('/login');
    }

    public function test_user_can_toggle_favorite(): void
    {
        $user = User::factory()->create();
        $product = $this->product();

        $this->actingAs($user)->post(route('favorites.toggle', $product));
        $this->assertDatabaseHas('favorites', ['user_id' => $user->id, 'product_id' => $product->id]);

        $this->actingAs($user)->post(route('favorites.toggle', $product));
        $this->assertDatabaseCount('favorites', 0);
    }

    public function test_favorites_page_lists_saved_products(): void
    {
        $user = User::factory()->create();
        $product = $this->product();

        $this->actingAs($user)->post(route('favorites.toggle', $product));

        $this->actingAs($user)
            ->get('/favorites')
            ->assertSuccessful()
            ->assertSee('المفضلة')
            ->assertSee('تونة');
    }

    public function test_side_menu_contains_account_links(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertSuccessful()
            ->assertSee('side-menu', false)
            ->assertSee('طلباتي')
            ->assertSee('المفضلة')
            ->assertSee('السلة')
            ->assertSee('حسابي');
    }
}
