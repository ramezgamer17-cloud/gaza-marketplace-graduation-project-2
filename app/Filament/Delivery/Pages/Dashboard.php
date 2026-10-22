<?php

namespace App\Filament\Delivery\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    public function getHeading(): string|Htmlable
    {
        return 'مرحبًا، '.(auth()->user()?->name ?? '');
    }

    public function getSubheading(): string|Htmlable|null
    {
        $profile = auth()->user()?->deliveryProfile;

        if (! $profile) {
            return null;
        }

        return collect([$profile->zone?->name, $profile->company?->name])->filter()->implode(' · ');
    }
}
