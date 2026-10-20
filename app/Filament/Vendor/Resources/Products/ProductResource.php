<?php

namespace App\Filament\Vendor\Resources\Products;

use App\Filament\Vendor\Resources\Products\Pages\CreateProduct;
use App\Filament\Vendor\Resources\Products\Pages\EditProduct;
use App\Filament\Vendor\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use App\Models\VendorSection;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'منتج';

    protected static ?string $pluralModelLabel = 'منتجاتي';

    protected static string|\UnitEnum|null $navigationGroup = 'متجري';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم المنتج')
                    ->required()
                    ->maxLength(255),

                Select::make('section_id')
                    ->label('القسم داخل متجري')
                    ->options(fn () => VendorSection::query()
                        ->where('user_id', auth()->id())
                        ->orderBy('sort_order')
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->placeholder('بدون قسم')
                    ->helperText('أنشئ الأقسام من صفحة «أقسام متجري».'),

                Select::make('category_id')
                    ->label('التصنيف العام')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Textarea::make('description')
                    ->label('وصف المنتج')
                    ->rows(4)
                    ->columnSpanFull(),

                TextInput::make('price')
                    ->label('السعر')
                    ->numeric()
                    ->suffix(' شيكل')
                    ->required()
                    ->minValue(0),

                TextInput::make('compare_at_price')
                    ->label('السعر قبل الخصم')
                    ->numeric()
                    ->suffix(' شيكل')
                    ->minValue(0),

                TextInput::make('stock')
                    ->label('الكمية المتوفرة')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),

                FileUpload::make('thumbnail')
                    ->label('الصورة الرئيسية')
                    ->image()
                    ->disk('public')
                    ->directory('products')
                    ->imageEditor(),

                FileUpload::make('images')
                    ->label('صور إضافية')
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->disk('public')
                    ->directory('products')
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->label('معروض في المتجر')
                    ->default(true),

                Toggle::make('is_featured')
                    ->label('منتج مميز')
                    ->helperText('يظهر في «مختارات المتاجر» بالصفحة الرئيسية.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('المنتج')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('section.name')
                    ->label('القسم')
                    ->badge()
                    ->placeholder('—'),

                TextColumn::make('price')
                    ->label('السعر')
                    ->formatStateUsing(fn ($state) => \App\Support\Money::format($state))
                    ->sortable(),

                TextColumn::make('stock')
                    ->label('المخزون')
                    ->badge()
                    ->color(fn (int $state) => match (true) {
                        $state === 0 => 'danger',
                        $state < 5 => 'warning',
                        default => 'success',
                    })
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('معروض')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('section_id')
                    ->label('القسم')
                    ->options(fn () => VendorSection::query()
                        ->where('user_id', auth()->id())
                        ->pluck('name', 'id')),

                TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->trueLabel('المعروضة')
                    ->falseLabel('المخفية'),
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
