<?php

namespace App\Filament\Vendor\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    public function getHeading(): string|Htmlable
    {
        $user = auth()->user();

        return 'أهلاً، '.($user?->name ?? '');
    }

    public function getSubheading(): string|Htmlable|null
    {
        $store = auth()->user()?->vendorProfile;

        if (! $store) {
            return 'لا يوجد متجر مرتبط بحسابك بعد.';
        }

        return $store->store_name.' — '.(static::storeTypeLabel($store->store_type));
    }

    protected static function storeTypeLabel(?string $type): string
    {
        return match ($type) {
            'restaurant' => 'مطعم',
            'supermarket' => 'سوبرماركت',
            'vegetables' => 'خضار وفواكه',
            'electronics' => 'أدوات كهربائية',
            'plumbing' => 'سباكة',
            'clothing' => 'ملابس',
            'pharmacy' => 'صيدلية',
            'bakery' => 'مخبز',
            default => 'متجر',
        };
    }
}
