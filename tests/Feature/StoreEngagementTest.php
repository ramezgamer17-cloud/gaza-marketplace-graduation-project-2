<?php

namespace Tests\Feature;

use App\Models\StoreFavorite;
use App\Models\StoreRating;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreEngagementTest extends TestCase
{
    use RefreshDatabase;

    protected function store(): VendorProfile
    {
        $vendor = User::factory()->create();

        return VendorProfile::create([
            'user_id' => $vendor->id,
            'store_name' => 'متجر تجريبي',
            'store_type' => 'supermarket',
            'is_active' => true,
        ]);
    }

    public function test_customer_can_toggle_store_favorite(): void
    {
        $store = $this->store();
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->post(route('stores.favorite', $store))
            ->assertRedirect();

        $this->assertDatabaseHas('store_favorites', [
            'user_id' => $customer->id,
            'vendor_profile_id' => $store->id,
        ]);

        $this->actingAs($customer)->post(route('stores.favorite', $store));

        $this->assertSame(0, StoreFavorite::count());
    }

    public function test_store_rating_average_is_calculated(): void
    {
        $store = $this->store();
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->actingAs($first)->post(route('stores.rate', $store), ['rating' => 4]);
        $this->actingAs($second)->post(route('stores.rate', $store), ['rating' => 2]);

        $this->assertSame(2, StoreRating::count());
        $this->assertEquals(3.0, $store->fresh()->ratingAverage());
        $this->assertSame(2, $store->fresh()->ratingCount());
    }

    public function test_rating_is_updated_when_customer_rates_again(): void
    {
        $store = $this->store();
        $customer = User::factory()->create();

        $this->actingAs($customer)->post(route('stores.rate', $store), ['rating' => 5]);
        $this->actingAs($customer)->post(route('stores.rate', $store), ['rating' => 1]);

        $this->assertSame(1, StoreRating::count());
        $this->assertEquals(1.0, $store->fresh()->ratingAverage());
    }

    public function test_guest_cannot_rate_or_favorite_store(): void
    {
        $store = $this->store();

        $this->post(route('stores.rate', $store), ['rating' => 5])->assertRedirect('/login');
        $this->post(route('stores.favorite', $store))->assertRedirect('/login');
    }
}
