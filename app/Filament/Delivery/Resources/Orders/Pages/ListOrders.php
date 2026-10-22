<?php

namespace App\Filament\Delivery\Resources\Orders\Pages;

use App\Filament\Delivery\Resources\Orders\OrderResource;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;
}
