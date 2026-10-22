<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryProfile extends Model
{
    public const TYPE_DRIVER = 'driver';

    public const TYPE_MANAGER = 'manager';

    protected $fillable = [
        'user_id',
        'company_id',
        'type',
        'zone_id',
        'address',
        'vehicle_type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(DeliveryCompany::class, 'company_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'zone_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeDrivers(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_DRIVER);
    }

    public function scopeManagers(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_MANAGER);
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === self::TYPE_MANAGER ? 'مدير شركة' : 'مندوب توصيل';
    }
}
