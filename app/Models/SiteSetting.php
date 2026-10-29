<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'hero_image',
        'hero_badge',
        'hero_title',
        'hero_title_accent',
        'hero_subtitle',
    ];

    /**
     * إعدادات الموقع (سجل واحد).
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([], []);
    }

    public function heroImageUrl(): string
    {
        return $this->hero_image
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->hero_image)
            : asset('images/hero.jpg');
    }

    public function heroBadgeText(): string
    {
        return $this->hero_badge ?: 'جودة تختارها كل يوم';
    }

    public function heroTitleText(): string
    {
        return $this->hero_title ?: 'كل ما تحب،';
    }

    public function heroTitleAccentText(): string
    {
        return $this->hero_title_accent ?: 'أقرب إليك.';
    }

    public function heroSubtitleText(): string
    {
        return $this->hero_subtitle ?: 'تسوّق من أفضل المتاجر المحلية في غزة، واستمتع بمنتجات طازجة تصل إلى بابك بعناية.';
    }
}
