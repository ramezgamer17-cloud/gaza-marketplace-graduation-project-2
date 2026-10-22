<?php

namespace App\Filament\Resources\DeliveryDrivers;

use App\Filament\Concerns\BaseDeliveryProfileResource;
use App\Filament\Resources\DeliveryDrivers\Pages\CreateDeliveryDriver;
use App\Filament\Resources\DeliveryDrivers\Pages\EditDeliveryDriver;
use App\Filament\Resources\DeliveryDrivers\Pages\ListDeliveryDrivers;
use App\Models\DeliveryProfile;

class DeliveryDriverResource extends BaseDeliveryProfileResource
{
    protected static ?string $modelLabel = 'مندوب';

    protected static ?string $pluralModelLabel = 'طلبات المناديب';

    protected static ?int $navigationSort = 2;

    protected static function profileType(): string
    {
        return DeliveryProfile::TYPE_DRIVER;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeliveryDrivers::route('/'),
            'create' => CreateDeliveryDriver::route('/create'),
            'edit' => EditDeliveryDriver::route('/{record}/edit'),
        ];
    }
}
