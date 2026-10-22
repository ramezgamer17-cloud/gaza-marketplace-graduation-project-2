<?php

namespace App\Providers\Filament;

use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Navigation\NavigationItem;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class VendorPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::PAGE_START,
            fn (): View => view('filament.vendor.pending-notice'),
        );
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('vendor')
            ->path('vendor')
            ->login()
            ->profile()
            ->brandName('سوق غزة — لوحة البائع')
            ->font(
                'Cairo',
                url: 'https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap',
            )
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->navigationGroups([
                'متجري',
                'الطلبات',
            ])
            ->navigationItems([
                NavigationItem::make('السوق')
                    ->url(fn (): string => route('home'))
                    ->icon('heroicon-o-shopping-bag')
                    ->sort(-20),
                NavigationItem::make('المتاجر')
                    ->url(fn (): string => route('stores.index'))
                    ->icon('heroicon-o-building-storefront')
                    ->sort(-19),
            ])
            ->userMenuItems([
                'market' => Action::make('market')
                    ->label('العودة إلى السوق')
                    ->url(fn (): string => route('home'))
                    ->icon('heroicon-o-arrow-left-on-rectangle'),
                'stores' => Action::make('stores')
                    ->label('تصفّح المتاجر')
                    ->url(fn (): string => route('stores.index'))
                    ->icon('heroicon-o-building-storefront'),
                'cart' => Action::make('cart')
                    ->label('سلة الشراء')
                    ->url(fn (): string => route('cart.index'))
                    ->icon('heroicon-o-shopping-cart'),
                'orders' => Action::make('orders')
                    ->label('طلباتي كزبون')
                    ->url(fn (): string => route('orders.index'))
                    ->icon('heroicon-o-clipboard-document-list'),
            ])
            ->discoverResources(in: app_path('Filament/Vendor/Resources'), for: 'App\Filament\Vendor\Resources')
            ->discoverPages(in: app_path('Filament/Vendor/Pages'), for: 'App\Filament\Vendor\Pages')
            ->pages([
                \App\Filament\Vendor\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Vendor/Widgets'), for: 'App\Filament\Vendor\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
