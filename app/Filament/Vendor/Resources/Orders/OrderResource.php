<?php

namespace App\Filament\Vendor\Resources\Orders;

use App\Filament\Vendor\Resources\Orders\Pages\EditOrder;
use App\Filament\Vendor\Resources\Orders\Pages\ListOrders;
use App\Filament\Vendor\Resources\Orders\RelationManagers\ItemsRelationManager;
use App\Models\Order;
use BackedEnum;
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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?string $recordTitleAttribute = 'order_number';

    protected static ?string $modelLabel = 'طلب';

    protected static ?string $pluralModelLabel = 'طلبات متجري';

    protected static string|\UnitEnum|null $navigationGroup = 'الطلبات';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('vendor_id', auth()->id());
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()
            ->where('vendor_id', auth()->id())
            ->where('status', Order::STATUS_PENDING)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function statusColors(): array
    {
        return [
            Order::STATUS_PENDING => 'warning',
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
                    ->label('حالة الطلب')
                    ->options([
                        Order::STATUS_PENDING => 'قيد الانتظار',
                        Order::STATUS_CONFIRMED => 'مؤكد',
                        Order::STATUS_PREPARING => 'قيد التحضير',
                        Order::STATUS_SHIPPED => 'في الطريق',
                        Order::STATUS_DELIVERED => 'تم التوصيل',
                        Order::STATUS_CANCELLED => 'ملغي',
                    ])
                    ->required(),

                Textarea::make('notes')
                    ->label('ملاحظات للزبون')
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
                    ->searchable()
                    ->description(fn (Order $record) => $record->customer_phone),

                TextColumn::make('items_count')
                    ->label('العناصر')
                    ->counts('items')
                    ->badge(),

                TextColumn::make('total')
                    ->label('الإجمالي')
                    ->formatStateUsing(fn ($state) => \App\Support\Money::format($state))
                    ->sortable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Order::statusLabels()[$state] ?? $state)
                    ->color(fn (string $state) => static::statusColors()[$state] ?? 'gray'),

                TextColumn::make('shipping_address')
                    ->label('عنوان التوصيل')
                    ->limit(35)
                    ->tooltip(fn (Order $record) => $record->shipping_address)
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('حالة الطلب')
                    ->options(Order::statusLabels()),
            ])
            ->recordActions([
                EditAction::make()->label('إدارة'),
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
