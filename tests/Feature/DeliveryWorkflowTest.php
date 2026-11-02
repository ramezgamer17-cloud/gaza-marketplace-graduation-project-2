<?php

namespace Tests\Feature;

use App\Models\DeliveryCompany;
use App\Models\DeliveryProfile;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\User;
use App\Models\VendorProfile;
use App\Notifications\OrderAssignedToDriver;
use App\Notifications\OrderStatusChanged;
use App\Services\DeliveryAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DeliveryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected DeliveryZone $north;

    protected function setUp(): void
    {
        parent::setUp();

        $this->north = DeliveryZone::create(['name' => 'الشمال', 'slug' => 'north', 'sort_order' => 1]);
    }

    protected function makeOrder(array $overrides = []): Order
    {
        $customer = User::factory()->create();
        $vendor = User::factory()->create();

        return Order::create(array_merge([
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'delivery_zone_id' => $this->north->id,
            'status' => Order::STATUS_READY,
            'subtotal' => 30,
            'delivery_fee' => 5,
            'total' => 35,
            'customer_name' => 'زبون',
            'customer_phone' => '0599000000',
            'shipping_address' => 'غزة',
            'payment_method' => 'jawwal_pay',
            'payment_status' => 'paid',
        ], $overrides));
    }

    protected function driver(?DeliveryCompany $company = null): User
    {
        $user = User::factory()->create();

        DeliveryProfile::create([
            'user_id' => $user->id,
            'company_id' => $company?->id,
            'type' => DeliveryProfile::TYPE_DRIVER,
            'zone_id' => $this->north->id,
            'is_active' => true,
        ]);

        return $user;
    }

    public function test_free_company_driver_is_assigned_first(): void
    {
        Notification::fake();

        $company = DeliveryCompany::create(['name' => 'شركة الشمال', 'zone_id' => $this->north->id, 'is_active' => true]);
        $companyDriver = $this->driver($company);
        $independent = $this->driver();

        $order = $this->makeOrder(['delivery_company_id' => $company->id]);

        $assigned = app(DeliveryAssignment::class)->assignDriver($order);

        $this->assertNotNull($assigned);
        $this->assertSame($companyDriver->id, $assigned->id);
        $this->assertSame($companyDriver->id, $order->fresh()->delivery_user_id);

        Notification::assertSentTo($companyDriver, OrderAssignedToDriver::class);
    }

    public function test_busy_company_driver_falls_back_to_independent_driver(): void
    {
        Notification::fake();

        $company = DeliveryCompany::create(['name' => 'شركة الشمال', 'zone_id' => $this->north->id, 'is_active' => true]);
        $companyDriver = $this->driver($company);
        $independent = $this->driver();

        // المندوب التابع للشركة مشغول بطلب آخر
        $this->makeOrder([
            'delivery_company_id' => $company->id,
            'delivery_user_id' => $companyDriver->id,
            'status' => Order::STATUS_SHIPPED,
        ]);

        $order = $this->makeOrder(['delivery_company_id' => $company->id]);

        $assigned = app(DeliveryAssignment::class)->assignDriver($order);

        $this->assertSame($independent->id, $assigned?->id);
    }

    public function test_no_free_driver_returns_null(): void
    {
        $company = DeliveryCompany::create(['name' => 'شركة الشمال', 'zone_id' => $this->north->id, 'is_active' => true]);
        $driver = $this->driver($company);

        $this->makeOrder([
            'delivery_company_id' => $company->id,
            'delivery_user_id' => $driver->id,
            'status' => Order::STATUS_SHIPPED,
        ]);

        $order = $this->makeOrder(['delivery_company_id' => $company->id]);

        $this->assertNull(app(DeliveryAssignment::class)->assignDriver($order));
        $this->assertNull($order->fresh()->delivery_user_id);
    }

    public function test_status_change_notifies_customer(): void
    {
        Notification::fake();

        $order = $this->makeOrder(['status' => Order::STATUS_PENDING]);
        $customer = $order->customer;

        $order->update(['status' => Order::STATUS_CONFIRMED]);

        Notification::assertSentTo($customer, OrderStatusChanged::class);
    }

    public function test_vendor_can_mark_order_as_ready_from_panel(): void
    {
        Notification::fake();

        $vendor = User::factory()->create();
        VendorProfile::create([
            'user_id' => $vendor->id,
            'store_name' => 'متجر البائع',
            'store_type' => 'supermarket',
            'zone_id' => $this->north->id,
            'is_active' => true,
        ]);

        $company = DeliveryCompany::create(['name' => 'شركة الشمال', 'zone_id' => $this->north->id, 'is_active' => true]);
        $driver = $this->driver($company);

        $order = $this->makeOrder([
            'vendor_id' => $vendor->id,
            'delivery_company_id' => $company->id,
            'status' => Order::STATUS_PENDING,
        ]);

        \Livewire\Livewire::actingAs($vendor)
            ->test(\App\Filament\Vendor\Resources\Orders\Pages\ListOrders::class)
            ->callAction(\Filament\Actions\Testing\TestAction::make('markReady')->table($order));

        $order->refresh();

        $this->assertSame(Order::STATUS_READY, $order->status);
        $this->assertSame($driver->id, $order->delivery_user_id);
        Notification::assertSentTo($driver, OrderAssignedToDriver::class);
    }
}
