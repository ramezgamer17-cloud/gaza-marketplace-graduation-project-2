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
        'delivery_company_id',
        'status',
        'payment_method',
        'payment_status',
        'payment_reference',
        'payment_phone',
        'payment_code',
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
        // رقم مؤقت قصير يمنع التعارض، ثم يتحول إلى رقم تسلسلي دائم بعد الحفظ
        static::creating(function (Order $order) {
            $order->order_number ??= 'TMP-'.Str::lower(Str::random(12));
        });

        static::created(function (Order $order) {
            if (str_starts_with((string) $order->order_number, 'TMP-')) {
                $order->forceFill([
                    'order_number' => static::formatInvoiceNumber($order->id),
                ])->saveQuietly();
            }
        });
    }

    /**
     * رقم الفاتورة النهائي: INV-000001 (فريد لكل طلب وقابل للبحث).
     */
    public static function formatInvoiceNumber(int $id): string
    {
        return 'INV-'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
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

    public function deliveryCompany(): BelongsTo
    {
        return $this->belongsTo(DeliveryCompany::class, 'delivery_company_id');
    }

    public function deliveryZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'delivery_zone_id');
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
