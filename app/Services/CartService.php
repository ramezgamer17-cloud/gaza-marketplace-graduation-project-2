<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use RuntimeException;

class CartService
{
    public function forUser(User $user): Cart
    {
        return Cart::firstOrCreate(['user_id' => $user->id]);
    }

    /**
     * إضافة منتج إلى سلة الزبون (أو زيادة الكمية إن كان موجودًا).
     */
    public function add(User $user, Product $product, int $quantity = 1): void
    {
        if (! $product->is_active || $product->stock < 1) {
            throw new RuntimeException('هذا المنتج غير متوفر حاليًا.');
        }

        $cart = $this->forUser($user);

        $item = $cart->items()->firstOrNew(['product_id' => $product->id]);
        $newQuantity = ($item->quantity ?? 0) + max(1, $quantity);

        if ($newQuantity > $product->stock) {
            throw new RuntimeException("الكمية المتاحة من «{$product->name}» هي {$product->stock} فقط.");
        }

        $item->quantity = $newQuantity;
        $item->save();
    }

    public function updateQuantity(User $user, int $itemId, int $quantity): void
    {
        $cart = $this->forUser($user);
        $item = $cart->items()->with('product')->findOrFail($itemId);

        if ($quantity < 1) {
            $item->delete();

            return;
        }

        if ($item->product && $quantity > $item->product->stock) {
            throw new RuntimeException("وصلت إلى الحد الأقصى المتوفر من «{$item->product->name}» ({$item->product->stock} قطعة).");
        }

        $item->quantity = $quantity;
        $item->save();
    }

    public function remove(User $user, int $itemId): void
    {
        $this->forUser($user)->items()->findOrFail($itemId)->delete();
    }

    public function clear(User $user): void
    {
        $this->forUser($user)->items()->delete();
    }
}
