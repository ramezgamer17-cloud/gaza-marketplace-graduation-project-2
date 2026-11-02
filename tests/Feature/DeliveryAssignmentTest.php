<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\DeliveryCompany;
use App\Models\DeliveryProfile;
use App\Models\DeliveryRate;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\VendorProfile;
use App\Notifications\OrderAssignedToCompany;
use App\Notifications\OrderAssignedToDriver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DeliveryAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function zone(string $name, string $slug): DeliveryZone
    {
        return DeliveryZone::create(['name' => $name, 'slug' => $slug, 'sort_order' => 1]);
    }

    protected function orderWithZone(DeliveryZone $zone): Order
    {
        $customer = User::factory()->create();
        $vendor = User::factory()->create();

        return Order::create([
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'delivery_zone_id' => $zone->id,
            'subtotal' => 30,
            'delivery_fee' => 5,
            'total' => 35,
            'customer_name' => 'زبون',
            'customer_phone' => '0599000000',
            'shipping_address' => 'غزة',
            'payment_method' => 'jawwal_pay',
            'payment_status' => 'paid',
        ]);
    }

    public function test_order_is_assigned_to_company_in_same_zone(): void
    {
        $north = $this->zone('الشمال', 'north');
        $central = $this->zone('الوسطى', 'central');

        DeliveryCompany::create(['name' => 'شركة الوسطى', 'zone_id' => $central->id, 'is_active' => true]);
        $northCompany = DeliveryCompany::create(['name' => 'شركة الشمال', 'zone_id' => $north->id, 'is_active' => true]);

        $order = $this->orderWithZone($north);

        $assigned = app(\App\Services\DeliveryAssignment::class)->assignCompany($order);

        $this->assertNotNull($assigned);
        $this->assertSame($northCompany->id, $order->fresh()->delivery_company_id);
    }

    public function test_manager_is_notified_when_company_assigned(): void
    {
        Notification::fake();

        $north = $this->zone('الشمال', 'north');
        $manager = User::factory()->create();

        DeliveryCompany::create([
            'name' => 'شركة الشمال',
            'zone_id' => $north->id,
            'manager_id' => $manager->id,
            'is_active' => true,
        ]);

        $order = $this->orderWithZone($north);
        $assignment = app(\App\Services\DeliveryAssignment::class);
        $assignment->assignCompany($order);
        $assignment->notifyCompanyManager($order->fresh(['deliveryCompany.manager']));

        Notification::assertSentTo($manager, OrderAssignedToCompany::class);
    }

    public function test_driver_is_notified_when_assigned(): void
    {
        Notification::fake();

        $north = $this->zone('الشمال', 'north');
        $driver = User::factory()->create();

        $order = $this->orderWithZone($north);

        $order->update(['delivery_user_id' => $driver->id]);

        Notification::assertSentTo($driver, OrderAssignedToDriver::class);
    }

    public function test_checkout_assigns_company_by_delivery_zone(): void
    {
        Notification::fake();

        $north = $this->zone('الشمال', 'north');
        DeliveryRate::create(['customer_zone_id' => $north->id, 'vendor_zone_id' => $north->id, 'fee' => 5]);

        $manager = User::factory()->create();
        $company = DeliveryCompany::create([
            'name' => 'شركة الشمال',
            'zone_id' => $north->id,
            'manager_id' => $manager->id,
            'is_active' => true,
        ]);

        $customer = User::factory()->create();
        $vendor = User::factory()->create();

        VendorProfile::create([
            'user_id' => $vendor->id,
            'store_name' => 'متجر',
            'store_type' => 'supermarket',
            'zone_id' => $north->id,
            'is_active' => true,
        ]);

        $product = Product::create([
            'user_id' => $vendor->id,
            'name' => 'منتج',
            'price' => 10,
            'stock' => 20,
            'is_active' => true,
        ]);

        $this->actingAs($customer)->post(route('cart.add', $product));

        $this->actingAs($customer)->post(route('checkout.store'), [
            'customer_name' => 'زبون',
            'customer_phone' => '0599000000',
            'delivery_zone_id' => $north->id,
            'shipping_address' => 'غزة - الشمال',
            'payment_method' => 'jawwal_pay',
        ]);

        $order = Order::firstOrFail();
        $this->assertSame($company->id, $order->delivery_company_id);
        Notification::assertSentTo($manager, OrderAssignedToCompany::class);
    }
}
