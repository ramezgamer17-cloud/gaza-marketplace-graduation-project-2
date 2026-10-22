<?php

namespace App\Filament\Resources\DeliveryDrivers\Pages;

use App\Filament\Resources\DeliveryDrivers\DeliveryDriverResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryDriver extends EditRecord
{
    protected static string $resource = DeliveryDriverResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
