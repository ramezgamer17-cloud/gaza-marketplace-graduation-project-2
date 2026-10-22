<?php

namespace App\Filament\Resources\DeliveryCompanies\RelationManagers;

use App\Models\DeliveryProfile;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DriversRelationManager extends RelationManager
{
    protected static string $relationship = 'drivers';

    protected static ?string $title = 'المنتسبون للشركة';

    protected static ?string $modelLabel = 'مندوب';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('user.name')
                    ->label('المندوب')
                    ->searchable()
                    ->description(fn (DeliveryProfile $record) => $record->user?->email),

                TextColumn::make('user.phone')
                    ->label('الجوال'),

                TextColumn::make('zone.name')
                    ->label('المنطقة')
                    ->badge()
                    ->placeholder('—'),

                TextColumn::make('vehicle_type')
                    ->label('المركبة')
                    ->placeholder('—'),

                TextColumn::make('is_active')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (bool $state) => $state ? 'مفعّل' : 'قيد المراجعة')
                    ->color(fn (bool $state) => $state ? 'success' : 'warning'),
            ])
            ->headerActions([])
            ->recordActions([
                Action::make('approve')
                    ->label('اعتماد')
                    ->icon('heroicon-m-check-badge')
                    ->color('success')
                    ->visible(fn (DeliveryProfile $record) => ! $record->is_active)
                    ->action(fn (DeliveryProfile $record) => $record->update(['is_active' => true])),

                Action::make('detach')
                    ->label('فصل عن الشركة')
                    ->icon('heroicon-m-arrow-right-start-on-rectangle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn (DeliveryProfile $record) => $record->update(['company_id' => null])),
            ])
            ->toolbarActions([]);
    }
}
