<?php

namespace App\Filament\Delivery\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'عناصر الطلب';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product_name')->label('المنتج')->wrap(),
                TextColumn::make('quantity')->label('الكمية')->badge(),
                TextColumn::make('unit_price')->label('السعر')
                    ->formatStateUsing(fn ($state) => \App\Support\Money::format($state)),
                TextColumn::make('total')->label('المجموع')
                    ->formatStateUsing(fn ($state) => \App\Support\Money::format($state))
                    ->summarize(Sum::make()->label('الإجمالي')),
            ])
            ->paginated(false)
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
