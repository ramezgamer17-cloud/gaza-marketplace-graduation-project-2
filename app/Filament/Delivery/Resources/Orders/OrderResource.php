<?php

namespace App\Filament\Delivery\Resources\Orders;

use App\Filament\Delivery\Resources\Orders\Pages\EditOrder;
use App\Filament\Delivery\Resources\Orders\Pages\ListOrders;
use App\Filament\Delivery\Resources\Orders\RelationManagers\ItemsRelationManager;
use App\Models\Order;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $recordTitleAttribute = 'order_number';

    protected static ?string $modelLabel = 'طلب';

    protected static ?string $pluralModelLabel = 'طلباتي';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('delivery_user_id', auth()->id());
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()
            ->whereIn('status', [Order::STATUS_SHIPPED, Order::STATUS_PREPARING])
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function statusColors(): array
    {
        return [
            Order::STATUS_CONFIRMED => 'info',
            Order::STATUS_PREPARING => 'info',
            Order::STATUS_SHIPPED => 'primary',
            Order::STATUS_DELIVERED => 'success',
            Order::STATUS_CANCELLED => 'danger',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('status')
                    ->label('حالة التوصيل')
                    ->options([
                        Order::STATUS_CONFIRMED => 'مؤكد',
                        Order::STATUS_PREPARING => 'قيد التحضير',
                        Order::STATUS_SHIPPED => 'في الطريق',
                        Order::STATUS_DELIVERED => 'تم التوصيل',
                        Order::STATUS_CANCELLED => 'تعذّر التوصيل',
                    ])
                    ->required(),

                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order_number')
                    ->label('رقم الفاتورة')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('customer.name')
                    ->label('الزبون')
                    ->description(fn (Order $record) => $record->customer_phone),

                TextColumn::make('vendor.name')
                    ->label('المتجر')
                    ->description(fn (Order $record) => $record->vendor?->vendorProfile?->store_name),

                TextColumn::make('shipping_address')
                    ->label('عنوان التوصيل')
                    ->limit(40)
                    ->tooltip(fn (Order $record) => $record->shipping_address)
                    ->wrap(),

                TextColumn::make('total')
                    ->label('الإجمالي')
                    ->formatStateUsing(fn ($state) => \App\Support\Money::format($state))
                    ->sortable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Order::statusLabels()[$state] ?? $state)
                    ->color(fn (string $state) => static::statusColors()[$state] ?? 'gray'),

                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(Order::statusLabels()),
            ])
            ->recordActions([
                Action::make('start')
                    ->label('بدء التوصيل')
                    ->icon('heroicon-m-truck')
                    ->color('primary')
                    ->visible(fn (Order $record) => in_array($record->status, [Order::STATUS_CONFIRMED, Order::STATUS_PREPARING], true))
                    ->action(fn (Order $record) => $record->update(['status' => Order::STATUS_SHIPPED])),

                Action::make('deliver')
                    ->label('تم التوصيل')
                    ->icon('heroicon-m-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => $record->status === Order::STATUS_SHIPPED)
                    ->action(fn (Order $record) => $record->update([
                        'status' => Order::STATUS_DELIVERED,
                        'delivered_at' => now(),
                    ])),

                EditAction::make()->label('تفاصيل'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}
