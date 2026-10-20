<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'منتج';

    protected static ?string $pluralModelLabel = 'المنتجات';

    protected static string|\UnitEnum|null $navigationGroup = 'الكتالوج';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('البائع')
                    ->relationship('vendor', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('category_id')
                    ->label('التصنيف')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                TextInput::make('name')
                    ->label('اسم المنتج')
                    ->required()
                    ->maxLength(255),

                TextInput::make('slug')
                    ->label('الرابط (slug)')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('اتركه فارغًا ليُولّد تلقائيًا.'),

                Textarea::make('description')
                    ->label('الوصف')
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
                    ->label('الكمية في المخزون')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),

                TextInput::make('sku')
                    ->label('رمز المنتج (SKU)')
                    ->maxLength(50),

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
                    ->label('منتج مفعّل')
                    ->default(true),

                Toggle::make('is_featured')
                    ->label('منتج مميز'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('الصورة')
                    ->disk('public')
                    ->height(40),

                TextColumn::make('name')
                    ->label('المنتج')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('vendor.name')
                    ->label('البائع')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('category.name')
                    ->label('التصنيف')
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
                    ->label('مفعّل')
                    ->boolean(),

                IconColumn::make('is_featured')
                    ->label('مميز')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('أُضيف في')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('التصنيف')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('user_id')
                    ->label('البائع')
                    ->relationship('vendor', 'name')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->trueLabel('المفعّلة')
                    ->falseLabel('المعطّلة'),

                TernaryFilter::make('is_featured')
                    ->label('مميز')
                    ->trueLabel('المميزة فقط')
                    ->falseLabel('غير المميزة'),

                TrashedFilter::make()
                    ->label('المحذوفة'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
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

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
