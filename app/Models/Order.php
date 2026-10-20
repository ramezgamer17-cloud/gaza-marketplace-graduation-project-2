<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_PREPARING = 'preparing';

    public const STATUS_SHIPPED = 'shipped';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'order_number',
        'user_id',
        'vendor_id',
        'delivery_user_id',
        'status',
        'payment_method',
        'payment_status',
        'payment_reference',
        'subtotal',
        'delivery_fee',
        'total',
        'customer_name',
        'customer_phone',
        'shipping_address',
        'notes',
        'delivered_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->order_number ??= 'ORD-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function deliveryUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeForVendor(Builder $query, int $vendorId): Builder
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeForDelivery(Builder $query, int $deliveryUserId): Builder
    {
        return $query->where('delivery_user_id', $deliveryUserId);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'قيد الانتظار',
            self::STATUS_CONFIRMED => 'مؤكد',
            self::STATUS_PREPARING => 'قيد التحضير',
            self::STATUS_SHIPPED => 'في الطريق',
            self::STATUS_DELIVERED => 'تم التوصيل',
            self::STATUS_CANCELLED => 'ملغي',
        ];
    }
}
