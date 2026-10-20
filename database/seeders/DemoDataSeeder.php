<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'vendor', 'customer', 'delivery'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // التصنيفات
        $food = Category::firstOrCreate(['name' => 'مواد غذائية'], ['sort_order' => 1]);
        $canned = Category::firstOrCreate(['name' => 'معلبات'], ['parent_id' => $food->id, 'sort_order' => 1]);
        $drinks = Category::firstOrCreate(['name' => 'مشروبات'], ['parent_id' => $food->id, 'sort_order' => 2]);
        $restaurants = Category::firstOrCreate(['name' => 'مطاعم'], ['sort_order' => 2]);
        $vegetables = Category::firstOrCreate(['name' => 'خضار وفواكه'], ['sort_order' => 3]);
        $homeTools = Category::firstOrCreate(['name' => 'أدوات منزلية'], ['sort_order' => 4]);

        // بائعون
        $vendorsData = [
            [
                'user' => ['name' => 'أحمد الشاورما', 'email' => 'sharma@demo.test', 'phone' => '0599111001'],
                'store' => ['store_name' => 'شاورما الرمال', 'store_type' => 'restaurant', 'theme_color' => '#F59E0B', 'delivery_fee' => 7, 'delivery_time' => 25],
                'sections' => ['ساندويشات', 'مقبلات'],
                'products' => [
                    ['name' => 'شاورما دجاج', 'price' => 15, 'compare_at_price' => 18, 'stock' => 40, 'category' => $restaurants, 'section' => 'ساندويشات'],
                    ['name' => 'شاورما لحم', 'price' => 20, 'stock' => 25, 'category' => $restaurants, 'section' => 'ساندويشات'],
                    ['name' => 'بطاطا مقلية', 'price' => 7, 'stock' => 60, 'category' => $restaurants, 'section' => 'مقبلات'],
                ],
            ],
            [
                'user' => ['name' => 'سامي النصر', 'email' => 'nasser@demo.test', 'phone' => '0599111002'],
                'store' => ['store_name' => 'سوبرماركت النصر', 'store_type' => 'supermarket', 'theme_color' => '#10B981', 'delivery_fee' => 10, 'delivery_time' => 40],
                'sections' => ['معلبات ومشروبات', 'مواد أساسية'],
                'products' => [
                    ['name' => 'تونة معلبة', 'price' => 6.5, 'stock' => 120, 'category' => $canned, 'section' => 'معلبات ومشروبات'],
                    ['name' => 'عصير برتقال', 'price' => 4, 'compare_at_price' => 5, 'stock' => 80, 'category' => $drinks, 'section' => 'معلبات ومشروبات'],
                    ['name' => 'أرز مصري 1كغ', 'price' => 9, 'stock' => 50, 'category' => $food, 'section' => 'مواد أساسية'],
                    ['name' => 'زيت زيتون 1ل', 'price' => 28, 'stock' => 15, 'category' => $food, 'section' => 'مواد أساسية'],
                ],
            ],
            [
                'user' => ['name' => 'أبو أحمد الخضار', 'email' => 'khodar@demo.test', 'phone' => '0599111003'],
                'store' => ['store_name' => 'خضار أبو أحمد', 'store_type' => 'vegetables', 'theme_color' => '#65A30D', 'delivery_fee' => 5, 'delivery_time' => 30],
                'sections' => ['خضار طازجة'],
                'products' => [
                    ['name' => 'طماطم 1كغ', 'price' => 4, 'stock' => 100, 'category' => $vegetables, 'section' => 'خضار طازجة'],
                    ['name' => 'خيار 1كغ', 'price' => 3.5, 'stock' => 90, 'category' => $vegetables, 'section' => 'خضار طازجة'],
                    ['name' => 'بطاطس 1كغ', 'price' => 3, 'stock' => 150, 'category' => $vegetables, 'section' => 'خضار طازجة'],
                ],
            ],
            [
                'user' => ['name' => 'محمد الكهربائي', 'email' => 'electric@demo.test', 'phone' => '0599111004'],
                'store' => ['store_name' => 'كهرباء السلام', 'store_type' => 'electronics', 'theme_color' => '#0EA5E9', 'delivery_fee' => 12, 'delivery_time' => 60],
                'sections' => ['إنارة', 'تمديدات'],
                'products' => [
                    ['name' => 'لمبة LED 12 واط', 'price' => 12, 'stock' => 70, 'category' => $homeTools, 'section' => 'إنارة'],
                    ['name' => 'وصلة كهرباء 3 متر', 'price' => 18, 'stock' => 35, 'category' => $homeTools, 'section' => 'تمديدات'],
                ],
            ],
        ];

        foreach ($vendorsData as $data) {
            $vendor = User::firstOrCreate(
                ['email' => $data['user']['email']],
                [
                    'name' => $data['user']['name'],
                    'phone' => $data['user']['phone'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            $vendor->syncRoles(['vendor']);

            $profile = VendorProfile::firstOrCreate(
                ['user_id' => $vendor->id],
                $data['store'] + ['description' => 'متجر تجريبي في سوق غزة.', 'is_active' => true]
            );

            $sections = [];
            foreach ($data['sections'] as $index => $sectionName) {
                $sections[$sectionName] = \App\Models\VendorSection::firstOrCreate(
                    ['user_id' => $vendor->id, 'name' => $sectionName],
                    ['sort_order' => $index + 1, 'is_active' => true]
                );
            }

            foreach ($data['products'] as $p) {
                Product::updateOrCreate(
                    ['name' => $p['name'], 'user_id' => $vendor->id],
                    [
                        'category_id' => $p['category']->id,
                        'section_id' => isset($p['section']) ? ($sections[$p['section']]->id ?? null) : null,
                        'price' => $p['price'],
                        'compare_at_price' => $p['compare_at_price'] ?? null,
                        'stock' => $p['stock'],
                        'is_active' => true,
                        'is_featured' => $p['price'] > 10,
                    ]
                );
            }
        }

        // زبون
        $customer = User::firstOrCreate(
            ['email' => 'customer@demo.test'],
            [
                'name' => 'زبون تجريبي',
                'phone' => '0599222001',
                'address' => 'غزة - الرمال',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $customer->syncRoles(['customer']);

        // مندوب توصيل
        $delivery = User::firstOrCreate(
            ['email' => 'delivery@demo.test'],
            [
                'name' => 'مندوب التوصيل',
                'phone' => '0599333001',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $delivery->syncRoles(['delivery']);

        // سلة للزبون
        Cart::firstOrCreate(['user_id' => $customer->id]);

        // طلبان تجريبيان
        if (Order::count() === 0) {
            $sharma = VendorProfile::where('store_name', 'شاورما الرمال')->firstOrFail();
            $market = VendorProfile::where('store_name', 'سوبرماركت النصر')->firstOrFail();
            $sharmaProduct = Product::where('user_id', $sharma->user_id)->firstOrFail();
            $tuna = Product::where('name', 'تونة معلبة')->firstOrFail();

            $order1 = Order::create([
                'user_id' => $customer->id,
                'vendor_id' => $sharma->user_id,
                'status' => Order::STATUS_PENDING,
                'payment_method' => 'jawwal_pay',
                'payment_status' => 'unpaid',
                'subtotal' => 30,
                'delivery_fee' => 7,
                'total' => 37,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'shipping_address' => $customer->address,
            ]);
            $order1->items()->create([
                'product_id' => $sharmaProduct->id,
                'product_name' => $sharmaProduct->name,
                'unit_price' => 15,
                'quantity' => 2,
                'total' => 30,
            ]);

            $order2 = Order::create([
                'user_id' => $customer->id,
                'vendor_id' => $market->user_id,
                'delivery_user_id' => $delivery->id,
                'status' => Order::STATUS_DELIVERED,
                'payment_method' => 'jawwal_pay',
                'payment_status' => 'paid',
                'subtotal' => 19.5,
                'delivery_fee' => 10,
                'total' => 29.5,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'shipping_address' => $customer->address,
                'delivered_at' => now(),
            ]);
            $order2->items()->create([
                'product_id' => $tuna->id,
                'product_name' => $tuna->name,
                'unit_price' => 6.5,
                'quantity' => 3,
                'total' => 19.5,
            ]);
        }

        $this->command?->info('Demo data ready: 4 vendors, 1 customer, 1 delivery, 2 orders.');
    }
}
