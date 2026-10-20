<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * إجمالي المنتجات في السلة (بدون رسوم التوصيل).
     */
    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->sum(fn (CartItem $item) => $item->line_total);
    }

    /**
     * السلة مجمّعة حسب البائع — هذا ما تعرضه صفحة السلة:
     * اسم المتجر ثم منتجاته، ثم المتجر التالي، وهكذا.
     *
     * @return Collection<int, object{vendor: User, items: Collection, subtotal: float, delivery_fee: float, total: float}>
     */
    public function vendorGroups(): Collection
    {
        $items = $this->relationLoaded('items')
            ? $this->items
            : $this->items()->with(['product.vendor.vendorProfile'])->get();

        return $items
            ->filter(fn (CartItem $item) => $item->product !== null)
            ->groupBy(fn (CartItem $item) => $item->product->user_id)
            ->map(function (Collection $group) {
                /** @var CartItem $first */
                $first = $group->first();
                $vendor = $first->product->vendor;
                $subtotal = (float) $group->sum(fn (CartItem $item) => $item->line_total);

                // رسوم التوصيل تُحددها إدارة التوصيل على مستوى المنصة، لا المتجر
                $deliveryFee = (float) config('marketplace.delivery_fee', 0);

                return (object) [
                    'vendor' => $vendor,
                    'items' => $group->values(),
                    'subtotal' => $subtotal,
                    'delivery_fee' => $deliveryFee,
                    'total' => $subtotal + $deliveryFee,
                ];
            })
            ->values();
    }

    /**
     * الإجمالي النهائي للسلة: منتجات + توصيل كل بائع.
     */
    public function getGrandTotalAttribute(): float
    {
        return (float) $this->vendorGroups()->sum(fn ($group) => $group->total);
    }

    public function getItemsCountAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }

    public function isEmpty(): bool
    {
        return $this->items()->doesntExist();
    }
}
