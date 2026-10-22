<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CheckoutService
{
    /**
     * إتمام الشراء: يتحول كل بائع في السلة إلى طلب/فاتورة مستقلة.
     *
     * @param  array{customer_name: string, customer_phone: string, shipping_address: string, notes?: string|null, payment_method?: string}  $data
     * @return Collection<int, Order>
     */
    public function checkout(Cart $cart, array $data): Collection
    {
        $groups = $cart->vendorGroups();

        if ($groups->isEmpty()) {
            throw new RuntimeException('السلة فارغة.');
        }

        $customerId = $cart->user_id;
        $cartId = $cart->id;

        $customerZoneId = (int) ($data['delivery_zone_id'] ?? 0);

        if (! $customerZoneId) {
            throw new RuntimeException('اختر منطقة التوصيل أولًا.');
        }

        $fees = app(DeliveryFeeCalculator::class)->feesForGroups($groups, $customerZoneId);

        $orders = DB::transaction(function () use ($groups, $data, $customerId, $cartId, $customerZoneId, $fees) {
            $orders = collect();

            foreach ($groups as $group) {
                $this->assertStock($group->items);

                $fee = $fees[$group->vendor->id] ?? null;

                if ($fee === null) {
                    $storeName = $group->vendor->vendorProfile?->store_name ?? $group->vendor->name;

                    throw new RuntimeException("لا يمكن التوصيل من «{$storeName}» — لم يحدد موقعه بعد أو منطقته غير مغطاة.");
                }

                $order = Order::create([
                    'user_id' => $customerId,
                    'vendor_id' => $group->vendor->id,
                    'delivery_zone_id' => $customerZoneId,
                    'status' => Order::STATUS_PENDING,
                    'payment_method' => $data['payment_method'] ?? 'jawwal_pay',
                    'payment_status' => 'unpaid',
                    'subtotal' => $group->subtotal,
                    'delivery_fee' => $fee,
                    'total' => $group->subtotal + $fee,
                    'customer_name' => $data['customer_name'],
                    'customer_phone' => $data['customer_phone'],
                    'shipping_address' => $data['shipping_address'],
                    'notes' => $data['notes'] ?? null,
                ]);

                foreach ($group->items as $item) {
                    $order->items()->create([
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'unit_price' => $item->product->price,
                        'quantity' => $item->quantity,
                        'total' => $item->line_total,
                    ]);

                    Product::whereKey($item->product_id)->decrement('stock', $item->quantity);
                }

                $orders->push($order);
            }

            CartItem::where('cart_id', $cartId)->delete();

            return $orders;
        });

        $this->notifyAboutOrders($orders);

        return $orders;
    }

    /**
     * إشعار الإدارة والبائع بكل طلب جديد.
     *
     * @param  Collection<int, Order>  $orders
     */
    protected function notifyAboutOrders(Collection $orders): void
    {
        $admins = \App\Models\User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', 'admin'))
            ->get();

        foreach ($orders as $order) {
            \Illuminate\Support\Facades\Notification::send(
                $admins,
                new \App\Notifications\NewOrderNotification($order)
            );

            if ($order->vendor) {
                $order->vendor->notify(new \App\Notifications\NewOrderNotification($order));
            }
        }
    }

    /**
     * @param  Collection<int, CartItem>  $items
     */
    protected function assertStock(Collection $items): void
    {
        foreach ($items as $item) {
            $product = $item->product;

            if (! $product || ! $product->is_active) {
                throw new RuntimeException("المنتج «{$product?->name}» لم يعد متاحًا.");
            }

            if ($product->stock < $item->quantity) {
                throw new RuntimeException("الكمية المطلوبة من «{$product->name}» غير متوفرة (المتاح: {$product->stock}).");
            }
        }
    }
}
