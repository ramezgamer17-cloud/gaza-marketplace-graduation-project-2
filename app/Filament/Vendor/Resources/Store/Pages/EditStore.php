<?php

namespace App\Filament\Vendor\Resources\Store\Pages;

use App\Filament\Vendor\Resources\Store\StoreResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditStore extends EditRecord
{
    protected static string $resource = StoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('حفظ التغييرات')
                ->icon('heroicon-m-check')
                ->color('primary')
                ->action('save'),
        ];
    }

    /**
     * إخفاء أزرار الحفظ السفلية — الحفظ من الأعلى.
     */
    protected function getFormActions(): array
    {
        return [];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم حفظ بيانات متجرك';
    }
}
