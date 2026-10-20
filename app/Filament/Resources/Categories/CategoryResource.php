<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'تصنيف';

    protected static ?string $pluralModelLabel = 'التصنيفات';

    protected static string|\UnitEnum|null $navigationGroup = 'الكتالوج';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم التصنيف')
                    ->required()
                    ->maxLength(255),

                Select::make('parent_id')
                    ->label('التصنيف الأب')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('بدون — تصنيف رئيسي'),

                TextInput::make('slug')
                    ->label('الرابط (slug)')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('اتركه فارغًا ليُولّد تلقائيًا.'),

                FileUpload::make('image')
                    ->label('صورة التصنيف')
                    ->image()
                    ->disk('public')
                    ->directory('categories'),

                TextInput::make('icon')
                    ->label('الأيقونة')
                    ->maxLength(255)
                    ->helperText('اسم أيقونة Heroicon أو مسار صورة صغير.'),

                TextInput::make('sort_order')
                    ->label('ترتيب العرض')
                    ->numeric()
                    ->default(0),

                Toggle::make('is_active')
                    ->label('تصنيف مفعّل')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('التصنيف')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Category $record) => $record->parent?->name),

                TextColumn::make('products_count')
                    ->label('عدد المنتجات')
                    ->counts('products')
                    ->badge(),

                TextColumn::make('children_count')
                    ->label('التصنيفات الفرعية')
                    ->counts('children')
                    ->badge()
                    ->color('info'),

                TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('مفعّل')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->trueLabel('المفعّلة')
                    ->falseLabel('المعطّلة'),
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
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}
