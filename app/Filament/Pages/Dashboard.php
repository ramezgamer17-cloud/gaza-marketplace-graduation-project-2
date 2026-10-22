<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    public function getHeading(): string|Htmlable
    {
        return 'أهلًا، '.(auth()->user()?->name ?? '');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'إدارة سوق غزة — المتاجر، الطلبات، التوصيل والمستخدمون.';
    }
}
