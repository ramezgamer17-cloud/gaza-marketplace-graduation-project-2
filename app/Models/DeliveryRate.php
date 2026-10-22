<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryRate extends Model
{
    protected $fillable = [
        'customer_zone_id',
        'vendor_zone_id',
        'fee',
    ];

    protected function casts(): array
    {
        return ['fee' => 'decimal:2'];
    }

    public function customerZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'customer_zone_id');
    }

    public function vendorZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'vendor_zone_id');
    }
}
