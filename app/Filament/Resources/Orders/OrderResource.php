<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Models\Order;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?string $recordTitleAttribute = 'order_number';

    protected static ?string $modelLabel = 'طلب';

    protected static ?string $pluralModelLabel = 'الطلبات';

    protected static string|\UnitEnum|null $navigationGroup = 'الطلبات';

    protected static ?int $navigationSort = 1;

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
                TextInput::make('order_number')
                    ->label('رقم الفاتورة')
                    ->disabled()
                    ->dehydrated(false),

                Select::make('status')
                    ->label('حالة الطلب')
                    ->options(Order::statusLabels())
                    ->required(),

                Select::make('delivery_company_id')
                    ->label('شركة التوصيل')
                    ->relationship('deliveryCompany', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->placeholder('بدون شركة'),

                Select::make('delivery_user_id')
                    ->label('موظف التوصيل')
                    ->options(fn (Get $get) => User::query()
                        ->whereHas('deliveryProfile', function ($query) use ($get) {
                            $query->where('type', 'driver')
                                ->when($get('delivery_company_id'), fn ($q, $companyId) => $q->where('company_id', $companyId));
                        })
                        ->orderBy('name')
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->placeholder('لم يُسند بعد'),

                Select::make('payment_status')
                    ->label('حالة الدفع')
                    ->options([
                        'unpaid' => 'غير مدفوع',
                        'paid' => 'مدفوع',
                        'refunded' => 'مُسترد',
                        'failed' => 'فشل',
                    ])
                    ->required(),

                TextInput::make('total')
                    ->label('الإجمالي')
                    ->suffix(' شيكل')
                    ->disabled()
                    ->dehydrated(false),

                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->rows(3)
                    ->columnSpanFull(),
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
                    ->searchable(),

                TextColumn::make('vendor.name')
                    ->label('المتجر')
                    ->searchable()
                    ->description(fn (Order $record) => $record->vendor?->vendorProfile?->store_name),

                TextColumn::make('items_count')
                    ->label('عناصر')
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

                TextColumn::make('payment_status')
                    ->label('الدفع')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'paid' => 'مدفوع',
                        'unpaid' => 'غير مدفوع',
                        'refunded' => 'مُسترد',
                        'failed' => 'فشل',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'paid' => 'success',
                        'unpaid' => 'warning',
                        default => 'danger',
                    }),

                TextColumn::make('deliveryCompany.name')
                    ->label('شركة التوصيل')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('deliveryUser.name')
                    ->label('المندوب')
                    ->placeholder('—')
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

                SelectFilter::make('vendor_id')
                    ->label('المتجر')
                    ->relationship('vendor', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('payment_status')
                    ->label('حالة الدفع')
                    ->options([
                        'unpaid' => 'غير مدفوع',
                        'paid' => 'مدفوع',
                        'refunded' => 'مُسترد',
                        'failed' => 'فشل',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('إدارة'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\Orders\RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}
