<?php

namespace App\Filament\Resources\VendorProfiles;

use App\Filament\Resources\VendorProfiles\Pages\CreateVendorProfile;
use App\Filament\Resources\VendorProfiles\Pages\EditVendorProfile;
use App\Filament\Resources\VendorProfiles\Pages\ListVendorProfiles;
use App\Models\VendorProfile;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
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
use Filament\Tables\Table;

class VendorProfileResource extends Resource
{
    protected static ?string $model = VendorProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?string $recordTitleAttribute = 'store_name';

    protected static ?string $modelLabel = 'متجر';

    protected static ?string $pluralModelLabel = 'المتاجر';

    protected static string|\UnitEnum|null $navigationGroup = 'المتاجر';

    protected static ?int $navigationSort = 1;

    public static function storeTypes(): array
    {
        return [
            'restaurant' => 'مطعم',
            'supermarket' => 'سوبرماركت',
            'vegetables' => 'خضار وفواكه',
            'electronics' => 'أدوات كهربائية',
            'plumbing' => 'سباكة',
            'clothing' => 'ملابس',
            'pharmacy' => 'صيدلية',
            'bakery' => 'مخبز',
            'other' => 'أخرى',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('صاحب المتجر')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('store_name')
                    ->label('اسم المتجر')
                    ->required()
                    ->maxLength(255),

                TextInput::make('slug')
                    ->label('الرابط (slug)')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('اتركه فارغًا ليُولّد تلقائيًا.'),

                Select::make('store_type')
                    ->label('نوع المتجر')
                    ->options(fn () => static::storeTypes())
                    ->searchable()
                    ->required(),

                Textarea::make('description')
                    ->label('وصف المتجر')
                    ->rows(3)
                    ->columnSpanFull(),

                FileUpload::make('logo')
                    ->label('الشعار')
                    ->image()
                    ->disk('public')
                    ->directory('vendors/logos')
                    ->imageEditor(),

                FileUpload::make('cover')
                    ->label('صورة الغلاف')
                    ->image()
                    ->disk('public')
                    ->directory('vendors/covers')
                    ->imageEditor(),

                ColorPicker::make('theme_color')
                    ->label('اللون الأساسي للمتجر')
                    ->default('#10B981'),

                TextInput::make('phone')
                    ->label('جوال المتجر')
                    ->tel()
                    ->maxLength(20),

                TextInput::make('address')
                    ->label('العنوان')
                    ->maxLength(255),

                TextInput::make('delivery_time')
                    ->label('مدة تجهيز الطلب (دقيقة)')
                    ->numeric()
                    ->minValue(0),

                Toggle::make('is_active')
                    ->label('متجر مفعّل')
                    ->default(true),

                Toggle::make('is_featured')
                    ->label('متجر مميز'),
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

                TextColumn::make('store_name')
                    ->label('المتجر')
                    ->searchable()
                    ->sortable()
                    ->description(fn (VendorProfile $record) => $record->user?->name),

                TextColumn::make('store_type')
                    ->label('النوع')
                    ->formatStateUsing(fn (?string $state) => static::storeTypes()[$state] ?? $state)
                    ->badge(),

                IconColumn::make('is_active')
                    ->label('مفعّل')
                    ->boolean(),

                IconColumn::make('is_featured')
                    ->label('مميز')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('store_type')
                    ->label('نوع المتجر')
                    ->options(fn () => static::storeTypes()),

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
            'index' => ListVendorProfiles::route('/'),
            'create' => CreateVendorProfile::route('/create'),
            'edit' => EditVendorProfile::route('/{record}/edit'),
        ];
    }
}
