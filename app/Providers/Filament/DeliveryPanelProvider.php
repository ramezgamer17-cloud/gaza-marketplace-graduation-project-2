<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class DeliveryPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('delivery')
            ->path('delivery')
            ->login()
            ->databaseNotifications()
            ->brandName('سوق غزة — لوحة المندوب')
            ->font('Cairo', url: 'https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap')
            ->colors(['primary' => Color::Sky])
            ->navigationItems([
                NavigationItem::make('السوق')
                    ->url(fn (): string => route('home'))
                    ->icon('heroicon-o-shopping-bag')
                    ->sort(-20),
            ])
            ->discoverResources(in: app_path('Filament/Delivery/Resources'), for: 'App\Filament\Delivery\Resources')
            ->discoverPages(in: app_path('Filament/Delivery/Pages'), for: 'App\Filament\Delivery\Pages')
            ->pages([
                \App\Filament\Delivery\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Delivery/Widgets'), for: 'App\Filament\Delivery\Widgets')
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
