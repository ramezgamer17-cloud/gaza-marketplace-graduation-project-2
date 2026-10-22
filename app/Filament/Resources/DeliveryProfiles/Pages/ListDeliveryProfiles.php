<?php

namespace App\Filament\Resources\DeliveryProfiles\Pages;

use App\Filament\Resources\DeliveryProfiles\DeliveryProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryProfiles extends ListRecords
{
    protected static string $resource = DeliveryProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
