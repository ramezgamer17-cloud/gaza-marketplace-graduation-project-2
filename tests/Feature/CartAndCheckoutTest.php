<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartAndCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function makeVendor(string $storeName, float $price): array
    {
        $vendor = User::factory()->create();
        $store = VendorProfile::create([
            'user_id' => $vendor->id,
            'store_name' => $storeName,
            'store_type' => 'supermarket',
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

    public function test_checkout_creates_one_invoice_per_vendor(): void
    {
        $customer = User::factory()->create();
        [, $storeA, $productA] = $this->makeVendor('متجر أ', 15);
        [, $storeB, $productB] = $this->makeVendor('متجر ب', 6.5);

        $this->actingAs($customer)->post(route('cart.add', $productA), ['quantity' => 2]);
        $this->actingAs($customer)->post(route('cart.add', $productB), ['quantity' => 3]);

        $this->assertSame(2, Cart::where('user_id', $customer->id)->first()->items()->count());

        $this->actingAs($customer)
            ->post(route('checkout.store'), [
                'customer_name' => 'زبون',
                'customer_phone' => '0599000000',
                'shipping_address' => 'غزة - الرمال',
                'payment_method' => 'jawwal_pay',
            ])
            ->assertRedirect(route('orders.index'));

        $this->assertSame(2, Order::count());
        $this->assertDatabaseHas('orders', ['vendor_id' => $storeA->user_id, 'total' => 30]);
        $this->assertDatabaseHas('orders', ['vendor_id' => $storeB->user_id, 'total' => 19.5]);
        $this->assertDatabaseCount('cart_items', 0);

        // خصم المخزون
        $this->assertSame(8, $productA->fresh()->stock);
        $this->assertSame(7, $productB->fresh()->stock);
    }

    public function test_invoice_is_visible_only_to_its_owner(): void
    {
        $customer = User::factory()->create();
        $other = User::factory()->create();
        [, $store, $product] = $this->makeVendor('متجر', 10);

        $this->actingAs($customer)->post(route('cart.add', $product));
        $this->actingAs($customer)->post(route('checkout.store'), [
            'customer_name' => 'زبون',
            'customer_phone' => '0599000000',
            'shipping_address' => 'غزة',
            'payment_method' => 'cash_on_delivery',
        ]);

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
        $this->actingAs($customer)->post(route('checkout.store'), [
            'customer_name' => 'زبون',
            'customer_phone' => '0599000000',
            'shipping_address' => 'غزة',
            'payment_method' => 'jawwal_pay',
        ]);

        $numbers = Order::pluck('order_number')->all();

        $this->assertCount(2, $numbers);
        $this->assertCount(2, array_unique($numbers), 'أرقام الفواتير يجب أن تكون مختلفة');

        foreach ($numbers as $number) {
            $this->assertMatchesRegularExpression('/^INV-\d{6}$/', $number);
        }
    }

    public function test_empty_cart_redirects_checkout(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get('/checkout')
            ->assertRedirect(route('cart.index'));
    }
}
