<?php

namespace App\Filament\Delivery\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DriverStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $orders = Order::query()->where('delivery_user_id', auth()->id());

        $today = (clone $orders)->whereDate('created_at', today())->count();
        $onTheWay = (clone $orders)->where('status', Order::STATUS_SHIPPED)->count();
        $delivered = (clone $orders)->where('status', Order::STATUS_DELIVERED)->count();
        $pending = (clone $orders)->whereIn('status', [Order::STATUS_CONFIRMED, Order::STATUS_PREPARING])->count();

        return [
            Stat::make('طلبات اليوم', (string) $today)
                ->description('جديدة: '.$pending)
                ->descriptionIcon('heroicon-m-inbox')
                ->color('info'),

            Stat::make('في الطريق', (string) $onTheWay)
                ->description('بانتظار التسليم')
                ->descriptionIcon('heroicon-m-truck')
                ->color('primary'),

            Stat::make('تم التوصيل', (string) $delivered)
                ->description('إجمالي المكتمل')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('إجمالي المسند', (string) (clone $orders)->count())
                ->description('كل الطلبات المسندة إليك')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('gray'),
        ];
    }
}
