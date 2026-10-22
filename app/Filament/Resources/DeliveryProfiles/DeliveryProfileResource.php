<?php

namespace App\Filament\Resources\DeliveryProfiles;

use App\Filament\Resources\DeliveryProfiles\Pages\CreateDeliveryProfile;
use App\Filament\Resources\DeliveryProfiles\Pages\EditDeliveryProfile;
use App\Filament\Resources\DeliveryProfiles\Pages\ListDeliveryProfiles;
use App\Models\DeliveryProfile;
use App\Models\DeliveryZone;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DeliveryProfileResource extends Resource
{
    protected static ?string $model = DeliveryProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?string $modelLabel = 'ملف توصيل';

    protected static ?string $pluralModelLabel = 'طلبات التوصيل';

    protected static string|\UnitEnum|null $navigationGroup = 'التوصيل';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $count = DeliveryProfile::query()->where('is_active', false)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('المستخدم')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('type')
                    ->label('النوع')
                    ->options([
                        DeliveryProfile::TYPE_DRIVER => 'مندوب توصيل',
                        DeliveryProfile::TYPE_MANAGER => 'مدير شركة',
                    ])
                    ->required(),

                Select::make('company_id')
                    ->label('الشركة')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('بدون شركة (مستقل)'),

                Select::make('zone_id')
                    ->label('منطقة العمل')
                    ->options(fn () => DeliveryZone::query()->regions()->pluck('name', 'id'))
                    ->searchable(),

                TextInput::make('address')
                    ->label('العنوان')
                    ->maxLength(255),

                TextInput::make('vehicle_type')
                    ->label('نوع المركبة')
                    ->maxLength(30)
                    ->placeholder('دراجة / سيارة / مشي'),

                Toggle::make('is_active')
                    ->label('حساب مفعّل')
                    ->helperText('لا يستطيع استلام الطلبات قبل التفعيل.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('is_active')
            ->columns([
                TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable()
                    ->description(fn (DeliveryProfile $record) => $record->user?->email),

                TextColumn::make('user.phone')
                    ->label('الجوال')
                    ->toggleable(),

                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === DeliveryProfile::TYPE_MANAGER ? 'مدير شركة' : 'مندوب')
                    ->color(fn (string $state) => $state === DeliveryProfile::TYPE_MANAGER ? 'info' : 'gray'),

                TextColumn::make('company.name')
                    ->label('الشركة')
                    ->placeholder('مستقل'),

                TextColumn::make('zone.name')
                    ->label('المنطقة')
                    ->placeholder('—'),

                TextColumn::make('is_active')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (bool $state) => $state ? 'مفعّل' : 'قيد المراجعة')
                    ->color(fn (bool $state) => $state ? 'success' : 'warning'),

                TextColumn::make('created_at')
                    ->label('تاريخ التقديم')
                    ->dateTime('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('النوع')
                    ->options([
                        DeliveryProfile::TYPE_DRIVER => 'مندوب',
                        DeliveryProfile::TYPE_MANAGER => 'مدير شركة',
                    ]),

                SelectFilter::make('company_id')
                    ->label('الشركة')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->trueLabel('المفعّلون')
                    ->falseLabel('قيد المراجعة'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('اعتماد')
                    ->icon('heroicon-m-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('اعتماد الحساب')
                    ->modalDescription('سيصبح هذا الحساب قادرًا على استلام الطلبات.')
                    ->visible(fn (DeliveryProfile $record) => ! $record->is_active)
                    ->action(fn (DeliveryProfile $record) => $record->update(['is_active' => true])),

                Action::make('suspend')
                    ->label('تعليق')
                    ->icon('heroicon-m-pause')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (DeliveryProfile $record) => $record->is_active)
                    ->action(fn (DeliveryProfile $record) => $record->update(['is_active' => false])),

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
            'index' => ListDeliveryProfiles::route('/'),
            'create' => CreateDeliveryProfile::route('/create'),
            'edit' => EditDeliveryProfile::route('/{record}/edit'),
        ];
    }
}
