<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function makeOrder(User $customer): Order
    {
        $vendor = User::factory()->create();

        return Order::create([
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'status' => Order::STATUS_CONFIRMED,
            'subtotal' => 30,
            'delivery_fee' => 5,
            'total' => 35,
            'customer_name' => 'زبون',
            'customer_phone' => '0599000000',
            'shipping_address' => 'غزة - الرمال',
            'payment_method' => 'jawwal_pay',
            'payment_status' => 'paid',
        ]);
    }

    public function test_customer_can_track_own_order(): void
    {
        $customer = User::factory()->create();
        $order = $this->makeOrder($customer);

        $this->actingAs($customer)
            ->get(route('orders.tracking', $order))
            ->assertSuccessful()
            ->assertSee('تتبّع مباشر')
            ->assertSee($order->order_number)
            ->assertSee('تم تأكيد الطلب');
    }

    public function test_customer_cannot_track_someone_elses_order(): void
    {
        $customer = User::factory()->create();
        $other = User::factory()->create();
        $order = $this->makeOrder($customer);

        $this->actingAs($other)
            ->get(route('orders.tracking', $order))
            ->assertForbidden();
    }

    public function test_orders_page_links_to_tracking(): void
    {
        $customer = User::factory()->create();
        $order = $this->makeOrder($customer);

        $this->actingAs($customer)
            ->get(route('orders.index'))
            ->assertSuccessful()
            ->assertSee(route('orders.tracking', $order), false);
    }
}
