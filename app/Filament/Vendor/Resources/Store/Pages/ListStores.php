<?php

namespace App\Filament\Vendor\Resources\Store\Pages;

use App\Filament\Vendor\Resources\Store\StoreResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListStores extends ListRecords
{
    protected static string $resource = StoreResource::class;

    /**
     * لا قائمة ولا إضافة: البائع ينتقل مباشرة إلى بيانات متجره.
     */
    public function mount(): void
    {
        $store = StoreResource::getEloquentQuery()->first();

        if (! $store) {
            Notification::make()
                ->title('لا يوجد متجر مرتبط بحسابك')
                ->body('تواصل مع إدارة المنصة لإنشاء متجرك.')
                ->warning()
                ->send();

            $this->redirect(\Filament\Facades\Filament::getUrl());

            return;
        }

        $this->redirect(StoreResource::getUrl('edit', ['record' => $store]));
    }
}
