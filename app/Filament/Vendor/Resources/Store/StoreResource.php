<?php

namespace App\Filament\Vendor\Resources\Store;

use App\Filament\Vendor\Resources\Store\Pages\EditStore;
use App\Filament\Vendor\Resources\Store\Pages\ListStores;
use App\Models\VendorProfile;
use App\Models\DeliveryZone;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StoreResource extends Resource
{
    protected static ?string $model = VendorProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?string $recordTitleAttribute = 'store_name';

    protected static ?string $modelLabel = 'متجري';

    protected static ?string $pluralModelLabel = 'متجري';

    protected static ?string $slug = 'store';

    protected static string|\UnitEnum|null $navigationGroup = 'متجري';

    protected static ?int $navigationSort = 1;

    /**
     * كل بائع يرى متجره فقط.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('store_name')
                    ->label('اسم المتجر')
                    ->required()
                    ->maxLength(255),

                TextInput::make('store_type')
                    ->label('نوع المتجر')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'restaurant' => 'مطعم',
                        'supermarket' => 'سوبرماركت',
                        'vegetables' => 'خضار وفواكه',
                        'electronics' => 'أدوات كهربائية',
                        'plumbing' => 'سباكة',
                        'clothing' => 'ملابس',
                        'pharmacy' => 'صيدلية',
                        'bakery' => 'مخبز',
                        default => (string) $state,
                    })
                    ->helperText('يعدّله الأدمن فقط.'),

                Textarea::make('description')
                    ->label('وصف المتجر')
                    ->rows(3)
                    ->columnSpanFull(),

                FileUpload::make('logo')
                    ->label('صورة المتجر (الشعار)')
                    ->image()
                    ->avatar()
                    ->disk('public')
                    ->directory('vendors/logos')
                    ->imageEditor()
                    ->helperText('تظهر دائرية في صفحة متجرك.'),

                FileUpload::make('cover')
                    ->label('صورة الغلاف')
                    ->image()
                    ->disk('public')
                    ->directory('vendors/covers')
                    ->imageEditor()
                    ->helperText('الصورة العريضة أعلى صفحة متجرك.'),

                ColorPicker::make('theme_color')
                    ->label('لون المتجر'),

                TextInput::make('phone')
                    ->label('جوال المتجر')
                    ->tel()
                    ->maxLength(20),

                Select::make('zone_id')
                    ->label('المنطقة')
                    ->options(fn () => DeliveryZone::query()->regions()->pluck('name', 'id'))
                    ->searchable()
                    ->live()
                    ->required(),

                Select::make('area_id')
                    ->label('الحي / المدينة')
                    ->options(fn (Get $get) => DeliveryZone::query()
                        ->where('parent_id', $get('zone_id'))
                        ->orderBy('sort_order')
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                TextInput::make('address')
                    ->label('باقي العنوان')
                    ->maxLength(255)
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('delivery_time')
                    ->label('مدة تجهيز الطلب (دقيقة)')
                    ->numeric()
                    ->minValue(0)
                    ->helperText('رسوم التوصيل تحددها شركة التوصيل، وليست المتجر.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label('الشعار')
                    ->disk('public')
                    ->circular(),

                TextColumn::make('store_name')
                    ->label('اسم المتجر')
                    ->searchable(),

                TextColumn::make('address')
                    ->label('العنوان')
                    ->placeholder('—'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('تعديل المتجر'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStores::route('/'),
            'edit' => EditStore::route('/{record}/edit'),
        ];
    }
}
