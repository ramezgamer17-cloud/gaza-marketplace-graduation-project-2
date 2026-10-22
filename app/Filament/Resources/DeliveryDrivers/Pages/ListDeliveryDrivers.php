<?php

namespace App\Filament\Resources\DeliveryDrivers\Pages;

use App\Filament\Resources\DeliveryDrivers\DeliveryDriverResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryDrivers extends ListRecords
{
    protected static string $resource = DeliveryDriverResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
