<?php

namespace Tests\Feature;

use App\Filament\Vendor\Resources\Sections\Pages\CreateSection;
use App\Models\Product;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\VendorSection;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VendorPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('vendor'));
    }

    protected function makeVendor(string $email = 'vendor@test.local', string $store = 'متجر الاختبار'): User
    {
        Role::firstOrCreate(['name' => 'vendor', 'guard_name' => 'web']);

        $vendor = User::factory()->create(['email' => $email]);
        $vendor->assignRole('vendor');

        VendorProfile::create([
            'user_id' => $vendor->id,
            'store_name' => $store,
            'store_type' => 'supermarket',
            'delivery_fee' => 5,
        ]);

        return $vendor;
    }

    public function test_vendor_can_open_panel_pages(): void
    {
        $vendor = $this->makeVendor();

        foreach ([
            '/vendor',
            '/vendor/products',
            '/vendor/products/create',
            '/vendor/sections',
            '/vendor/sections/create',
            '/vendor/orders',
        ] as $url) {
            $this->actingAs($vendor)->get($url)->assertSuccessful();
        }
    }

    public function test_vendor_sees_only_own_products(): void
    {
        $vendorA = $this->makeVendor('a@test.local', 'متجر أ');
        $vendorB = $this->makeVendor('b@test.local', 'متجر ب');

        Product::create(['user_id' => $vendorA->id, 'name' => 'منتج متجري', 'price' => 10, 'stock' => 5]);
        Product::create(['user_id' => $vendorB->id, 'name' => 'منتج بائع آخر', 'price' => 20, 'stock' => 5]);

        $this->actingAs($vendorA)
            ->get('/vendor/products')
            ->assertSuccessful()
            ->assertSee('منتج متجري')
            ->assertDontSee('منتج بائع آخر');
    }

    public function test_vendor_cannot_edit_another_vendors_product(): void
    {
        $vendorA = $this->makeVendor('a@test.local', 'متجر أ');
        $vendorB = $this->makeVendor('b@test.local', 'متجر ب');

        $foreign = Product::create(['user_id' => $vendorB->id, 'name' => 'منتج غيري', 'price' => 20, 'stock' => 5]);

        $this->actingAs($vendorA)
            ->get("/vendor/products/{$foreign->id}/edit")
            ->assertNotFound();
    }

    public function test_vendor_can_add_own_section(): void
    {
        $vendor = $this->makeVendor();

        Livewire::actingAs($vendor)
            ->test(CreateSection::class)
            ->fillForm([
                'name' => 'مشروبات باردة',
                'sort_order' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('vendor_sections', [
            'user_id' => $vendor->id,
            'name' => 'مشروبات باردة',
        ]);

        $this->assertSame(1, VendorSection::where('user_id', $vendor->id)->count());
    }

    public function test_customer_and_admin_cannot_open_vendor_panel(): void
    {
        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);

        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $this->actingAs($customer)->get('/vendor')->assertForbidden();
    }

    public function test_vendor_stats_widget_renders(): void
    {
        $vendor = $this->makeVendor();

        Product::create(['user_id' => $vendor->id, 'name' => 'منتج', 'price' => 10, 'stock' => 0]);

        Livewire::actingAs($vendor)
            ->test(\App\Filament\Vendor\Widgets\VendorStats::class)
            ->assertSuccessful()
            ->assertSee('منتجاتي');
    }

    public function test_sections_page_renders_with_product_counts(): void
    {
        $vendor = $this->makeVendor();

        $section = VendorSection::create([
            'user_id' => $vendor->id,
            'name' => 'مشروبات',
        ]);

        Product::create([
            'user_id' => $vendor->id,
            'section_id' => $section->id,
            'name' => 'عصير',
            'price' => 5,
            'stock' => 3,
        ]);

        $this->actingAs($vendor)
            ->get('/vendor/sections')
            ->assertSuccessful()
            ->assertSee('مشروبات');

        $this->assertSame(1, $section->fresh()->products()->count());
        $this->assertSame(1, $vendor->products()->first()->section->id);
    }

    public function test_store_link_goes_straight_to_edit_form(): void
    {
        $vendor = $this->makeVendor();

        $this->actingAs($vendor)
            ->get('/vendor/store')
            ->assertRedirect();
    }

    public function test_vendor_store_page_shows_logo_and_cover_fields(): void
    {
        $vendor = $this->makeVendor();
        $store = $vendor->vendorProfile;

        $this->actingAs($vendor)
            ->get('/vendor/store')
            ->assertRedirect("/vendor/store/{$store->id}/edit");

        $this->actingAs($vendor)
            ->get("/vendor/store/{$store->id}/edit")
            ->assertSuccessful()
            ->assertSee('اسم المتجر')
            ->assertSee('صورة الغلاف')
            ->assertSee('حفظ التغييرات')
            ->assertDontSee('data.delivery_fee', false);
    }
}
