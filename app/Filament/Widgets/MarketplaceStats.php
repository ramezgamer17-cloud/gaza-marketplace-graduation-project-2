<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\VendorProfile;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MarketplaceStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $totalSales = (float) Order::whereIn('status', [
            Order::STATUS_DELIVERED,
            Order::STATUS_SHIPPED,
        ])->sum('total');

        $pendingOrders = Order::where('status', Order::STATUS_PENDING)->count();
        $todayOrders = Order::whereDate('created_at', today())->count();

        return [
            Stat::make('المستخدمون', (string) User::count())
                ->description('البائعون: '.User::query()
                    ->whereHas('roles', fn ($query) => $query->where('name', 'vendor'))
                    ->count())
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('المتاجر', (string) VendorProfile::active()->count())
                ->description('من أصل '.VendorProfile::count().' متجر')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('info'),

            Stat::make('المنتجات', (string) Product::active()->count())
                ->description('نفدت الكمية: '.Product::active()->where('stock', 0)->count())
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('warning'),

            Stat::make('طلبات اليوم', (string) $todayOrders)
                ->description('قيد الانتظار: '.$pendingOrders)
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color($pendingOrders > 0 ? 'danger' : 'gray'),

            Stat::make('إجمالي الطلبات', (string) Order::count())
                ->description('ملغاة: '.Order::where('status', Order::STATUS_CANCELLED)->count())
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),

            Stat::make('المبيعات المحقّقة', \App\Support\Money::format($totalSales))
                ->description('الطلبات المُسلّمة والمشحونة')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('متوسط قيمة الطلب', \App\Support\Money::format(Order::avg('total')))
                ->description('لكل طلب/متجر')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),

            Stat::make('مستخدمون غير مفعّلين', (string) User::where('is_active', false)->count())
                ->description('بحاجة لمراجعة')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
