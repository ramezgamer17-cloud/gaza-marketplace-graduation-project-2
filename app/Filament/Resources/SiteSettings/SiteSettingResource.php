<?php

namespace App\Filament\Resources\SiteSettings;

use App\Filament\Resources\SiteSettings\Pages\EditSiteSetting;
use App\Filament\Resources\SiteSettings\Pages\ListSiteSettings;
use App\Models\SiteSetting;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $modelLabel = 'إعدادات السوق';

    protected static ?string $pluralModelLabel = 'إعدادات السوق';

    protected static string|\UnitEnum|null $navigationGroup = 'الإعدادات';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('hero_image')
                    ->label('صورة الغلاف الرئيسية (الهيرو)')
                    ->image()
                    ->disk('public')
                    ->directory('site')
                    ->imageEditor()
                    ->helperText('تظهر بعرض الشاشة كاملة في أعلى الصفحة الرئيسية. اتركها فارغة لاستخدام الصورة الافتراضية.'),

                TextInput::make('hero_badge')
                    ->label('نص الشارة العلوية')
                    ->maxLength(255)
                    ->placeholder('جودة تختارها كل يوم'),

                TextInput::make('hero_title')
                    ->label('العنوان (السطر الأول)')
                    ->maxLength(255)
                    ->placeholder('كل ما تحب،'),

                TextInput::make('hero_title_accent')
                    ->label('العنوان (السطر الملوّن)')
                    ->maxLength(255)
                    ->placeholder('أقرب إليك.'),

                TextInput::make('hero_subtitle')
                    ->label('الوصف')
                    ->maxLength(500)
                    ->columnSpanFull(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSiteSettings::route('/'),
            'edit' => EditSiteSetting::route('/{record}/edit'),
        ];
    }
}
