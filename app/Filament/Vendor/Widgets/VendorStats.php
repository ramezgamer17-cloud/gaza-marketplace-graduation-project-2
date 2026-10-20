<?php

namespace App\Filament\Vendor\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VendorStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $vendorId = auth()->id();

        $products = Product::where('user_id', $vendorId);
        $orders = Order::where('vendor_id', $vendorId);

        $sales = (float) (clone $orders)
            ->whereIn('status', [Order::STATUS_DELIVERED, Order::STATUS_SHIPPED])
            ->sum('total');

        $pending = (clone $orders)->where('status', Order::STATUS_PENDING)->count();

        return [
            Stat::make('منتجاتي', (string) (clone $products)->count())
                ->description('مخفية: '.(clone $products)->where('is_active', false)->count())
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success'),

            Stat::make('نفدت الكمية', (string) (clone $products)->where('stock', 0)->count())
                ->description('تحتاج إعادة تعبئة')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make('طلبات جديدة', (string) $pending)
                ->description('بحاجة لتأكيد')
                ->descriptionIcon('heroicon-m-bell-alert')
                ->color($pending > 0 ? 'warning' : 'gray'),

            Stat::make('مبيعاتي', \App\Support\Money::format($sales))
                ->description('من الطلبات المُسلّمة والمشحونة')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),
        ];
    }
}
