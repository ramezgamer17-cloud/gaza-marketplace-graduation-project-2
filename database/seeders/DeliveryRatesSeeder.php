<?php

namespace Database\Seeders;

use App\Models\DeliveryRate;
use App\Models\DeliveryZone;
use Illuminate\Database\Seeder;

class DeliveryRatesSeeder extends Seeder
{
    /**
     * سعر التوصيل حسب المسافة: نفس المنطقة 5، مجاورة 10، بعيدة 15.
     */
    public function run(): void
    {
        $zones = DeliveryZone::query()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        if ($zones->count() < 3) {
            $this->command?->warn('تحتاج 3 مناطق أولًا.');

            return;
        }

        // ترتيب المناطق: الشمال (0)، الوسطى (1)، الجنوب (2)
        $positions = [];

        foreach ($zones->values() as $index => $zone) {
            $positions[$zone->id] = $index;
        }

        foreach ($zones as $customerZone) {
            foreach ($zones as $vendorZone) {
                $distance = abs($positions[$customerZone->id] - $positions[$vendorZone->id]);

                $fee = match ($distance) {
                    0 => 5,
                    1 => 10,
                    default => 15,
                };

                DeliveryRate::updateOrCreate(
                    [
                        'customer_zone_id' => $customerZone->id,
                        'vendor_zone_id' => $vendorZone->id,
                    ],
                    ['fee' => $fee]
                );
            }
        }

        $this->command?->info('Delivery rates ready: 5 / 10 / 15 by distance.');
    }
}
