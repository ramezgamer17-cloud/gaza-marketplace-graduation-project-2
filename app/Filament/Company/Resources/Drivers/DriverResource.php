<?php

namespace App\Filament\Company\Resources\Drivers;

use App\Filament\Company\Resources\Drivers\Pages\ListDrivers;
use App\Models\DeliveryProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DriverResource extends Resource
{
    protected static ?string $model = DeliveryProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $modelLabel = 'مندوب';

    protected static ?string $pluralModelLabel = 'مناديب الشركة';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        $companyId = auth()->user()?->deliveryProfile?->company_id;

        return parent::getEloquentQuery()
            ->where('company_id', $companyId ?? 0)
            ->where('type', DeliveryProfile::TYPE_DRIVER);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('المندوب')
                    ->searchable()
                    ->description(fn (DeliveryProfile $record) => $record->user?->email),

                TextColumn::make('user.phone')
                    ->label('الجوال')
                    ->searchable(),

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
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->trueLabel('المفعّلون')
                    ->falseLabel('قيد المراجعة'),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDrivers::route('/'),
        ];
    }
}
