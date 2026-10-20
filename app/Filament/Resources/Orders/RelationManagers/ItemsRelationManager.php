<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'عناصر الفاتورة';

    protected static ?string $modelLabel = 'عنصر';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                TextColumn::make('product_name')
                    ->label('المنتج')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('unit_price')
                    ->label('سعر الوحدة')
                    ->formatStateUsing(fn ($state) => \App\Support\Money::format($state)),

                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->badge(),

                TextColumn::make('total')
                    ->label('المجموع')
                    ->formatStateUsing(fn ($state) => \App\Support\Money::format($state))
                    ->summarize(Sum::make()->label('إجمالي العناصر')->formatStateUsing(fn ($state) => \App\Support\Money::format($state))),
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
