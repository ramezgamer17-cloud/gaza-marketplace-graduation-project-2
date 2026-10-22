<?php

namespace App\Filament\Company\Resources\Orders;

use App\Filament\Company\Resources\Orders\Pages\EditOrder;
use App\Filament\Company\Resources\Orders\Pages\ListOrders;
use App\Models\DeliveryProfile;
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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $recordTitleAttribute = 'order_number';

    protected static ?string $modelLabel = 'طلب';

    protected static ?string $pluralModelLabel = 'التوصيلات';

    protected static ?int $navigationSort = 1;

    public static function companyId(): ?int
    {
        return auth()->user()?->deliveryProfile?->company_id;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('delivery_company_id', static::companyId() ?? 0);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->whereNull('delivery_user_id')->count();

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
                Select::make('delivery_user_id')
                    ->label('إسناد إلى مندوب')
                    ->options(fn () => DeliveryProfile::query()
                        ->where('company_id', static::companyId())
                        ->where('type', DeliveryProfile::TYPE_DRIVER)
                        ->with('user')
                        ->get()
                        ->pluck('user.name', 'user_id'))
                    ->searchable()
                    ->placeholder('لم يُسند بعد'),

                Select::make('status')
                    ->label('حالة الطلب')
                    ->options([
                        Order::STATUS_CONFIRMED => 'مؤكد',
                        Order::STATUS_PREPARING => 'قيد التحضير',
                        Order::STATUS_SHIPPED => 'في الطريق',
                        Order::STATUS_DELIVERED => 'تم التوصيل',
                        Order::STATUS_CANCELLED => 'ملغي',
                    ])
                    ->required(),

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
                    ->description(fn (Order $record) => $record->customer_phone),

                TextColumn::make('shipping_address')
                    ->label('عنوان التوصيل')
                    ->limit(35)
                    ->tooltip(fn (Order $record) => $record->shipping_address),

                TextColumn::make('deliveryUser.name')
                    ->label('المندوب')
                    ->placeholder('بانتظار الإسناد')
                    ->badge()
                    ->color(fn (Order $record) => $record->delivery_user_id ? 'success' : 'warning'),

                TextColumn::make('total')
                    ->label('الإجمالي')
                    ->formatStateUsing(fn ($state) => \App\Support\Money::format($state))
                    ->sortable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Order::statusLabels()[$state] ?? $state),

                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(Order::statusLabels()),

                SelectFilter::make('assigned')
                    ->label('الإسناد')
                    ->options([
                        'unassigned' => 'بانتظار الإسناد',
                        'assigned' => 'مُسند',
                    ])
                    ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                        'unassigned' => $query->whereNull('delivery_user_id'),
                        'assigned' => $query->whereNotNull('delivery_user_id'),
                        default => $query,
                    }),
            ])
            ->recordActions([
                EditAction::make()->label('إدارة'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}
