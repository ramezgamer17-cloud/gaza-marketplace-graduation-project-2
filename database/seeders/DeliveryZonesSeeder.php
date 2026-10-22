<?php

namespace Database\Seeders;

use App\Models\DeliveryZone;
use App\Support\Slug;
use Illuminate\Database\Seeder;

class DeliveryZonesSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            ['name' => 'الشمال', 'sort_order' => 1, 'areas' => ['جباليا', 'بيت لاهيا', 'بيت حانون']],
            ['name' => 'الوسطى', 'sort_order' => 2, 'areas' => ['دير البلح', 'النصيرات', 'البريج', 'المغازي']],
            ['name' => 'الجنوب', 'sort_order' => 3, 'areas' => ['خان يونس', 'رفح', 'بني سهيلا', 'عبسان']],
        ];

        foreach ($regions as $region) {
            $parent = DeliveryZone::firstOrCreate(
                ['name' => $region['name'], 'parent_id' => null],
                [
                    'slug' => Slug::make($region['name'], unique: false),
                    'sort_order' => $region['sort_order'],
                    'is_active' => true,
                ]
            );

            foreach ($region['areas'] as $index => $area) {
                DeliveryZone::firstOrCreate(
                    ['name' => $area, 'parent_id' => $parent->id],
                    [
                        'slug' => Slug::make($area, unique: false).'-'.$parent->id,
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command?->info('Delivery zones ready: 3 regions with areas.');
    }
}
