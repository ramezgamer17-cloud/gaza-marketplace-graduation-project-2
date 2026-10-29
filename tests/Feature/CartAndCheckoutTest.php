<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\DeliveryRate;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartAndCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected DeliveryZone $north;

    protected DeliveryZone $central;

    protected DeliveryZone $south;

    protected function setUp(): void
    {
        parent::setUp();

        $this->north = DeliveryZone::create(['name' => 'الشمال', 'slug' => 'north', 'sort_order' => 1]);
        $this->central = DeliveryZone::create(['name' => 'الوسطى', 'slug' => 'central', 'sort_order' => 2]);
        $this->south = DeliveryZone::create(['name' => 'الجنوب', 'slug' => 'south', 'sort_order' => 3]);

        $zones = [$this->north, $this->central, $this->south];

        foreach ($zones as $customer) {
            foreach ($zones as $vendor) {
                $distance = abs($customer->sort_order - $vendor->sort_order);
                $fee = match ($distance) { 0 => 5, 1 => 10, default => 15 };

                DeliveryRate::create([
                    'customer_zone_id' => $customer->id,
                    'vendor_zone_id' => $vendor->id,
                    'fee' => $fee,
                ]);
            }
        }
    }

    protected function makeVendor(string $storeName, float $price, ?DeliveryZone $zone = null): array
    {
        $vendor = User::factory()->create();
        $store = VendorProfile::create([
            'user_id' => $vendor->id,
            'store_name' => $storeName,
            'store_type' => 'supermarket',
            'zone_id' => ($zone ?? $this->north)->id,
            'is_active' => true,
        ]);

        $product = Product::create([
            'user_id' => $vendor->id,
            'name' => 'منتج '.$storeName,
            'price' => $price,
            'stock' => 10,
            'is_active' => true,
        ]);

        return [$vendor, $store, $product];
    }

    protected function checkoutPayload(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'زبون',
            'customer_phone' => '0599000000',
            'delivery_zone_id' => $this->north->id,
            'shipping_address' => 'غزة - الرمال',
            'payment_method' => 'jawwal_pay',
        ], $overrides);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/cart')->assertRedirect('/login');
    }

    public function test_customer_can_add_product_to_cart(): void
    {
        $customer = User::factory()->create();
        [, , $product] = $this->makeVendor('متجر أ', 10);

        $this->actingAs($customer)
            ->post(route('cart.add', $product))
            ->assertRedirect();

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
    }

    public function test_add_to_cart_supports_ajax_without_page_reload(): void
    {
        $customer = User::factory()->create();
        [, , $product] = $this->makeVendor('متجر', 10);

        $this->actingAs($customer)
            ->postJson(route('cart.add', $product))
            ->assertOk()
            ->assertJson(['ok' => true, 'count' => 1]);
    }

    public function test_cart_update_returns_json_without_reload(): void
    {
        $customer = User::factory()->create();
        [, , $product] = $this->makeVendor('متجر', 10);

        $this->actingAs($customer)->post(route('cart.add', $product), ['quantity' => 1]);

        $item = \App\Models\CartItem::firstOrFail();

        $this->actingAs($customer)
            ->patchJson(route('cart.update', $item), ['quantity' => 3])
            ->assertOk()
            ->assertJsonPath('item.quantity', 3)
            ->assertJsonPath('item.line_total', 30)
            ->assertJsonPath('count', 3);
    }

    public function test_cart_remove_returns_json_without_reload(): void
    {
        $customer = User::factory()->create();
        [, , $product] = $this->makeVendor('متجر', 10);

        $this->actingAs($customer)->post(route('cart.add', $product));
        $item = \App\Models\CartItem::firstOrFail();

        $this->actingAs($customer)
            ->postJson(route('cart.remove', $item), ['_method' => 'DELETE'])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('count', 0);

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_cart_groups_products_by_vendor(): void
    {
        $customer = User::factory()->create();
        [, , $productA] = $this->makeVendor('شاورما الرمال', 15);
        [, , $productB] = $this->makeVendor('سوبرماركت النصر', 6.5);

        $this->actingAs($customer)->post(route('cart.add', $productA));
        $this->actingAs($customer)->post(route('cart.add', $productB));

        $this->actingAs($customer)
            ->get('/cart')
            ->assertSuccessful()
            ->assertSee('شاورما الرمال')
            ->assertSee('سوبرماركت النصر')
            ->assertSee('منتج شاورما الرمال')
            ->assertSee('منتج سوبرماركت النصر');
    }

    public function test_adding_more_than_stock_is_rejected(): void
    {
        $customer = User::factory()->create();
        [, , $product] = $this->makeVendor('متجر', 10);

        $this->actingAs($customer)
            ->post(route('cart.add', $product), ['quantity' => 50])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_checkout_creates_one_invoice_per_vendor_with_delivery_fee(): void
    {
        $customer = User::factory()->create();
        [, $storeA, $productA] = $this->makeVendor('متجر أ', 15);
        [, $storeB, $productB] = $this->makeVendor('متجر ب', 6.5);

        $this->actingAs($customer)->post(route('cart.add', $productA), ['quantity' => 2]);
        $this->actingAs($customer)->post(route('cart.add', $productB), ['quantity' => 3]);

        $this->assertSame(2, Cart::where('user_id', $customer->id)->first()->items()->count());

        $this->actingAs($customer)
            ->post(route('checkout.store'), $this->checkoutPayload())
            ->assertRedirectContains('/pay');

        $this->assertSame(2, Order::count());

        // كل متجر في الشمال، والزبون في الشمال → 5 لكل متجر
        $this->assertDatabaseHas('orders', ['vendor_id' => $storeA->user_id, 'delivery_fee' => 5, 'total' => 35]);
        $this->assertDatabaseHas('orders', ['vendor_id' => $storeB->user_id, 'delivery_fee' => 5, 'total' => 24.5]);
        $this->assertDatabaseCount('cart_items', 0);

        // خصم المخزون
        $this->assertSame(8, $productA->fresh()->stock);
        $this->assertSame(7, $productB->fresh()->stock);
    }

    public function test_delivery_fee_depends_on_distance_between_zones(): void
    {
        $customer = User::factory()->create();
        [, , $nearProduct] = $this->makeVendor('متجر قريب', 10, $this->north);
        [, , $middleProduct] = $this->makeVendor('متجر وسط', 10, $this->central);
        [, , $farProduct] = $this->makeVendor('متجر بعيد', 10, $this->south);

        $this->actingAs($customer)->post(route('cart.add', $nearProduct));
        $this->actingAs($customer)->post(route('cart.add', $middleProduct));
        $this->actingAs($customer)->post(route('cart.add', $farProduct));

        $this->actingAs($customer)
            ->post(route('checkout.store'), $this->checkoutPayload(['delivery_zone_id' => $this->north->id]));

        $fees = Order::query()->orderBy('delivery_fee')->pluck('delivery_fee', 'vendor_id');

        $this->assertEquals(5, (float) $fees[$nearProduct->user_id]);
        $this->assertEquals(10, (float) $fees[$middleProduct->user_id]);
        $this->assertEquals(15, (float) $fees[$farProduct->user_id]);
        $this->assertEquals(30, (float) Order::sum('delivery_fee'));
    }

    public function test_vendor_without_zone_blocks_checkout(): void
    {
        $customer = User::factory()->create();
        $vendor = User::factory()->create();

        VendorProfile::create([
            'user_id' => $vendor->id,
            'store_name' => 'متجر بلا منطقة',
            'store_type' => 'supermarket',
            'is_active' => true,
        ]);

        $product = Product::create([
            'user_id' => $vendor->id,
            'name' => 'منتج',
            'price' => 10,
            'stock' => 5,
            'is_active' => true,
        ]);

        $this->actingAs($customer)->post(route('cart.add', $product));

        $this->actingAs($customer)
            ->post(route('checkout.store'), $this->checkoutPayload())
            ->assertSessionHas('error');

        $this->assertSame(0, Order::count());
    }

    public function test_invoice_is_visible_only_to_its_owner(): void
    {
        $customer = User::factory()->create();
        $other = User::factory()->create();
        [, $store, $product] = $this->makeVendor('متجر', 10);

        $this->actingAs($customer)->post(route('cart.add', $product));
        $this->actingAs($customer)->post(route('checkout.store'), $this->checkoutPayload(['payment_method' => 'cash_on_delivery']));

        $order = Order::firstOrFail();

        $this->actingAs($customer)
            ->get(route('orders.show', $order))
            ->assertSuccessful()
            ->assertSee($order->order_number)
            ->assertSee($order->vendor->vendorProfile->store_name)
            ->assertSee('فاتورة إلكترونية');

        $this->actingAs($other)
            ->get(route('orders.show', $order))
            ->assertForbidden();
    }

    public function test_invoice_numbers_are_unique_and_sequential(): void
    {
        $customer = User::factory()->create();
        [, , $productA] = $this->makeVendor('متجر أ', 10);
        [, , $productB] = $this->makeVendor('متجر ب', 20);

        $this->actingAs($customer)->post(route('cart.add', $productA));
        $this->actingAs($customer)->post(route('cart.add', $productB));
        $this->actingAs($customer)->post(route('checkout.store'), $this->checkoutPayload());

        $numbers = Order::pluck('order_number')->all();

        $this->assertCount(2, $numbers);
        $this->assertCount(2, array_unique($numbers), 'أرقام الفواتير يجب أن تكون مختلفة');

        foreach ($numbers as $number) {
            $this->assertMatchesRegularExpression('/^INV-\d{6}$/', $number);
        }
    }

    public function test_admin_can_buy_and_receives_notifications(): void
    {
        \Illuminate\Support\Facades\Notification::fake();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        [$vendor, , $product] = $this->makeVendor('متجر الأدمن', 25);

        $this->actingAs($admin)->post(route('cart.add', $product));
        $this->actingAs($admin)->post(route('checkout.store'), $this->checkoutPayload(['customer_name' => 'الأدمن']));

        $order = Order::firstOrFail();
        $this->assertSame($admin->id, $order->user_id);

        \Illuminate\Support\Facades\Notification::assertSentTo(
            $admin,
            \App\Notifications\NewOrderNotification::class
        );

        \Illuminate\Support\Facades\Notification::assertSentTo(
            $vendor,
            \App\Notifications\NewOrderNotification::class
        );
    }

    public function test_phone_must_be_a_valid_palestinian_mobile(): void
    {
        $customer = User::factory()->create();
        [, , $product] = $this->makeVendor('متجر', 10);

        $this->actingAs($customer)->post(route('cart.add', $product));

        $this->actingAs($customer)
            ->post(route('checkout.store'), $this->checkoutPayload(['customer_phone' => '12345']))
            ->assertSessionHasErrors('customer_phone');

        $this->assertSame(0, Order::count());
    }

    public function test_checkout_page_has_back_to_cart_link(): void
    {
        $customer = User::factory()->create();
        [, , $product] = $this->makeVendor('متجر', 10);

        $this->actingAs($customer)->post(route('cart.add', $product));

        $this->actingAs($customer)
            ->get('/checkout')
            ->assertSuccessful()
            ->assertSee('رجوع إلى السلة')
            ->assertSee(route('cart.index'), false);
    }

    public function test_item_can_be_removed_via_post_remove_route(): void
    {
        $customer = User::factory()->create();
        [, , $product] = $this->makeVendor('متجر', 10);

        $this->actingAs($customer)->post(route('cart.add', $product));

        $item = \App\Models\CartItem::firstOrFail();

        $this->actingAs($customer)
            ->post(route('cart.remove.post', $item))
            ->assertRedirect();

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_empty_cart_redirects_checkout(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get('/checkout')
            ->assertRedirect(route('cart.index'));
    }
}
