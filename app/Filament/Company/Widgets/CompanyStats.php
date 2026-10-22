<?php

namespace App\Filament\Company\Widgets;

use App\Models\DeliveryProfile;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CompanyStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $companyId = auth()->user()?->deliveryProfile?->company_id;
        $orders = Order::query()->where('delivery_company_id', $companyId ?? 0);

        $today = (clone $orders)->whereDate('created_at', today())->count();
        $unassigned = (clone $orders)->whereNull('delivery_user_id')->count();
        $inProgress = (clone $orders)->whereIn('status', [Order::STATUS_SHIPPED, Order::STATUS_PREPARING])->count();
        $delivered = (clone $orders)->where('status', Order::STATUS_DELIVERED)->count();
        $drivers = DeliveryProfile::query()
            ->where('company_id', $companyId ?? 0)
            ->where('type', DeliveryProfile::TYPE_DRIVER)
            ->count();

        return [
            Stat::make('طلبات اليوم', (string) $today)
                ->description('بانتظار الإسناد: '.$unassigned)
                ->descriptionIcon('heroicon-m-inbox')
                ->color($unassigned > 0 ? 'warning' : 'info'),

            Stat::make('قيد التنفيذ', (string) $inProgress)
                ->description('في الطريق أو التحضير')
                ->descriptionIcon('heroicon-m-truck')
                ->color('primary'),

            Stat::make('تم التوصيل', (string) $delivered)
                ->description('مكتملة')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('المناديب', (string) $drivers)
                ->description('ضمن الشركة')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
        ];
    }
}
