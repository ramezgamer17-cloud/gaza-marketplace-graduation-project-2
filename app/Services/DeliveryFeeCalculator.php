<?php

namespace App\Services;

use App\Models\DeliveryRate;
use App\Models\User;
use Illuminate\Support\Collection;

class DeliveryFeeCalculator
{
    /**
     * سعر التوصيل بين منطقة الزبون ومنطقة المتجر.
     * يعيد null إذا لم تكن هناك منطقة مسجّلة أو سعر معرّف.
     */
    public function fee(?int $customerZoneId, ?int $vendorZoneId): ?float
    {
        if (! $customerZoneId || ! $vendorZoneId) {
            return null;
        }

        $rate = DeliveryRate::query()
            ->where('customer_zone_id', $customerZoneId)
            ->where('vendor_zone_id', $vendorZoneId)
            ->first();

        return $rate ? (float) $rate->fee : null;
    }

    /**
     * سعر التوصيل لكل متجر في السلة.
     *
     * @param  Collection<int, object{vendor: User, items: Collection, subtotal: float}>  $groups
     * @return array<int, float|null>  مفتاحها معرّف البائع
     */
    public function feesForGroups(Collection $groups, ?int $customerZoneId): array
    {
        $fees = [];

        foreach ($groups as $group) {
            $vendorZoneId = $group->vendor->vendorProfile?->zone_id;

            $fees[$group->vendor->id] = $this->fee($customerZoneId, $vendorZoneId);
        }

        return $fees;
    }

    /**
     * إجمالي التوصيل (يتجاهل القيم غير المتاحة).
     *
     * @param  array<int, float|null>  $fees
     */
    public function total(array $fees): float
    {
        return (float) collect($fees)->filter(fn ($fee) => $fee !== null)->sum();
    }
}
