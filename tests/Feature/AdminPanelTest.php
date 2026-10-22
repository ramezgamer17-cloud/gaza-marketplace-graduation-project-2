<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function makeAdmin(): User
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        return $admin;
    }

    public function test_guest_sees_login_page(): void
    {
        $this->get('/admin/login')->assertSuccessful();
    }

    public function test_admin_can_open_every_resource_page(): void
    {
        $admin = $this->makeAdmin();

        $pages = [
            '/admin',
            '/admin/users',
            '/admin/users/create',
            '/admin/vendor-profiles',
            '/admin/vendor-profiles/create',
            '/admin/categories',
            '/admin/categories/create',
            '/admin/products',
            '/admin/products/create',
            '/admin/orders',
        ];

        foreach ($pages as $url) {
            $this->actingAs($admin)
                ->get($url)
                ->assertSuccessful();
        }
    }

    public function test_panel_is_arabic_and_rtl(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertSuccessful()
            ->assertSee('dir="rtl"', false)
            ->assertSee('المستخدمون')
            ->assertSee('المتاجر')
            ->assertSee('الكتالوج')
            ->assertSee('الطلبات');
    }

    public function test_vendor_cannot_open_admin_panel(): void
    {
        Role::firstOrCreate(['name' => 'vendor', 'guard_name' => 'web']);

        $vendor = User::factory()->create();
        $vendor->assignRole('vendor');

        $this->actingAs($vendor)->get('/admin')->assertForbidden();
    }

    public function test_order_edit_page_shows_invoice_items(): void
    {
        $admin = $this->makeAdmin();

        $vendor = User::factory()->create();
        $customer = User::factory()->create();

        $product = \App\Models\Product::create([
            'user_id' => $vendor->id,
            'name' => 'شاورما دجاج',
            'price' => 15,
            'stock' => 10,
        ]);

        $order = \App\Models\Order::create([
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'subtotal' => 30,
            'delivery_fee' => 7,
            'total' => 37,
            'customer_name' => $customer->name,
            'customer_phone' => '0599000000',
            'shipping_address' => 'غزة',
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => 15,
            'quantity' => 2,
            'total' => 30,
        ]);

        $this->actingAs($admin)
            ->get("/admin/orders/{$order->id}/edit")
            ->assertSuccessful()
            ->assertSee($order->order_number);

        \Livewire\Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\Orders\RelationManagers\ItemsRelationManager::class, [
                'ownerRecord' => $order,
                'pageClass' => \App\Filament\Resources\Orders\Pages\EditOrder::class,
            ])
            ->assertSuccessful()
            ->assertSee('شاورما دجاج')
            ->assertSee('الكمية');
    }

    public function test_admin_can_open_delivery_screens(): void
    {
        $admin = $this->makeAdmin();

        foreach ([
            '/admin/delivery-companies',
            '/admin/delivery-companies/create',
            '/admin/delivery-profiles',
            '/admin/delivery-profiles/create',
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertSuccessful();
        }
    }

    public function test_admin_can_approve_delivery_profile(): void
    {
        $admin = $this->makeAdmin();

        $driverUser = \App\Models\User::factory()->create();
        $zone = \App\Models\DeliveryZone::create(['name' => 'الشمال', 'slug' => 'n-approve']);

        $profile = \App\Models\DeliveryProfile::create([
            'user_id' => $driverUser->id,
            'type' => 'driver',
            'zone_id' => $zone->id,
            'is_active' => false,
        ]);

        \Livewire\Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\DeliveryProfiles\Pages\ListDeliveryProfiles::class)
            ->callAction(\Filament\Actions\Testing\TestAction::make('approve')->table($profile));

        $this->assertTrue($profile->fresh()->is_active);
    }

    public function test_dashboard_widgets_render_without_errors(): void
    {
        $admin = $this->makeAdmin();

        \Livewire\Livewire::actingAs($admin)
            ->test(\App\Filament\Widgets\MarketplaceStats::class)
            ->assertSuccessful();

        \Livewire\Livewire::actingAs($admin)
            ->test(\App\Filament\Widgets\OrdersChart::class)
            ->assertSuccessful();
    }
}
