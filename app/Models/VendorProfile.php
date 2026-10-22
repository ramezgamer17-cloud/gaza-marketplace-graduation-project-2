<?php

namespace App\Models;

use App\Support\Slug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorProfile extends Model
{
    use HasFactory;

    /**
     * أنواع المتاجر المتاحة على المنصة.
     */
    public static function storeTypes(): array
    {
        return [
            'restaurant' => 'مطعم',
            'supermarket' => 'سوبرماركت',
            'vegetables' => 'خضار وفواكه',
            'electronics' => 'أدوات كهربائية',
            'plumbing' => 'سباكة',
            'clothing' => 'ملابس',
            'pharmacy' => 'صيدلية',
            'bakery' => 'مخبز',
            'other' => 'أخرى',
        ];
    }

    protected $fillable = [
        'user_id',
        'store_name',
        'slug',
        'description',
        'logo',
        'cover',
        'theme_color',
        'store_type',
        'phone',
        'address',
        'zone_id',
        'area_id',
        'latitude',
        'longitude',
        'delivery_fee',
        'delivery_time',
        'is_active',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'delivery_fee' => 'decimal:2',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (VendorProfile $profile) {
            $profile->slug ??= Slug::make($profile->store_name);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'zone_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'area_id');
    }

    /**
     * العنوان المعروض للزبون: المنطقة + الحي + التفاصيل.
     */
    public function getFullAddressAttribute(): string
    {
        return collect([$this->zone?->name, $this->area?->name, $this->address])
            ->filter()
            ->implode(' - ');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
