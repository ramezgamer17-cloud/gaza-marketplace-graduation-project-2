<?php

namespace App\Filament\Company\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    public function getHeading(): string|Htmlable
    {
        return auth()->user()?->deliveryProfile?->company?->name ?? 'لوحة الشركة';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'مرحبًا، '.(auth()->user()?->name ?? '');
    }
}
