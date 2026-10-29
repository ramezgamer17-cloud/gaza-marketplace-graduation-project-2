<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function order(User $customer): Order
    {
        $vendor = User::factory()->create();

        return Order::create([
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'subtotal' => 30,
            'delivery_fee' => 5,
            'total' => 35,
            'customer_name' => 'زبون',
            'customer_phone' => '0599000000',
            'shipping_address' => 'غزة',
            'payment_method' => 'jawwal_pay',
            'payment_status' => 'unpaid',
        ]);
    }

    public function test_payment_flow_marks_order_as_paid(): void
    {
        $customer = User::factory()->create();
        $order = $this->order($customer);

        $this->actingAs($customer)
            ->get(route('payment.show', $order))
            ->assertSuccessful()
            ->assertSee($order->order_number)
            ->assertSee('جوال بي');

        $this->actingAs($customer)
            ->post(route('payment.send', $order), ['payment_phone' => '0597784541'])
            ->assertRedirect(route('payment.verify', $order));

        $order->refresh();
        $this->assertSame('0597784541', $order->payment_phone);
        $this->assertNotNull($order->payment_code);

        // رمز خاطئ
        $this->actingAs($customer)
            ->post(route('payment.verify.submit', $order), ['code' => '0000'])
            ->assertSessionHasErrors('code');

        $this->assertSame('unpaid', $order->fresh()->payment_status);

        // الرمز الصحيح
        $this->actingAs($customer)
            ->post(route('payment.verify.submit', $order), ['code' => $order->payment_code])
            ->assertRedirect(route('orders.index'));

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame(Order::STATUS_CONFIRMED, $order->status);
    }

    public function test_other_user_cannot_pay_someone_elses_order(): void
    {
        $customer = User::factory()->create();
        $other = User::factory()->create();
        $order = $this->order($customer);

        $this->actingAs($other)->get(route('payment.show', $order))->assertForbidden();
    }

    public function test_invalid_wallet_number_is_rejected(): void
    {
        $customer = User::factory()->create();
        $order = $this->order($customer);

        $this->actingAs($customer)
            ->post(route('payment.send', $order), ['payment_phone' => '123'])
            ->assertSessionHasErrors('payment_phone');
    }
}
