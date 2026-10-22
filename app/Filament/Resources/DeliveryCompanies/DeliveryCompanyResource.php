<?php

namespace App\Filament\Resources\DeliveryCompanies;

use App\Filament\Resources\DeliveryCompanies\Pages\CreateDeliveryCompany;
use App\Filament\Resources\DeliveryCompanies\Pages\EditDeliveryCompany;
use App\Filament\Resources\DeliveryCompanies\Pages\ListDeliveryCompanies;
use App\Models\DeliveryCompany;
use App\Models\DeliveryZone;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DeliveryCompanyResource extends Resource
{
    protected static ?string $model = DeliveryCompany::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'شركة توصيل';

    protected static ?string $pluralModelLabel = 'شركات التوصيل';

    protected static string|\UnitEnum|null $navigationGroup = 'التوصيل';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم الشركة')
                    ->required()
                    ->maxLength(255),

                TextInput::make('phone')
                    ->label('هاتف الشركة')
                    ->tel()
                    ->maxLength(20),

                TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->email()
                    ->maxLength(255),

                Select::make('zone_id')
                    ->label('منطقة العمل')
                    ->options(fn () => DeliveryZone::query()->regions()->pluck('name', 'id'))
                    ->searchable(),

                TextInput::make('address')
                    ->label('العنوان')
                    ->maxLength(255)
                    ->columnSpanFull(),

                FileUpload::make('logo')
                    ->label('الشعار')
                    ->image()
                    ->disk('public')
                    ->directory('delivery/companies'),

                Select::make('manager_id')
                    ->label('مدير الشركة')
                    ->options(fn () => User::query()
                        ->whereHas('deliveryProfile', fn ($query) => $query->where('type', 'manager'))
                        ->orderBy('name')
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->placeholder('بدون مدير'),

                Toggle::make('is_active')
                    ->label('شركة مفعّلة')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('logo')
                    ->label('الشعار')
                    ->disk('public')
                    ->circular(),

                TextColumn::make('name')
                    ->label('الشركة')
                    ->searchable()
                    ->sortable()
                    ->description(fn (DeliveryCompany $record) => $record->phone),

                TextColumn::make('zone.name')
                    ->label('المنطقة')
                    ->badge()
                    ->placeholder('—'),

                TextColumn::make('manager.name')
                    ->label('المدير')
                    ->placeholder('بدون مدير'),

                TextColumn::make('drivers_count')
                    ->label('المناديب')
                    ->counts('drivers')
                    ->badge(),

                TextColumn::make('orders_count')
                    ->label('الطلبات')
                    ->counts('orders')
                    ->badge()
                    ->color('info'),

                IconColumn::make('is_active')
                    ->label('مفعّلة')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('zone_id')
                    ->label('المنطقة')
                    ->relationship('zone', 'name'),

                TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->trueLabel('المفعّلة')
                    ->falseLabel('قيد المراجعة'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeliveryCompanies::route('/'),
            'create' => CreateDeliveryCompany::route('/create'),
            'edit' => EditDeliveryCompany::route('/{record}/edit'),
        ];
    }
}
