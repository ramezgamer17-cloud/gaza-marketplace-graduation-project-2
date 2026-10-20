<?php

namespace App\Support;

use Illuminate\Support\Str;

class Slug
{
    /**
     * توليد slug يدعم الحروف العربية بدل تحويلها إلى ASCII.
     *
     * مثال: "سوبرماركت غزة" -> "سوبرماركت-غزة-a1b2"
     */
    public static function make(?string $value, bool $unique = true): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return $unique ? Str::lower(Str::random(8)) : '';
        }

        // استبدال أي شيء ليس حرفًا أو رقمًا بشرطة
        $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', $value) ?? '';
        $slug = trim($slug, '-');
        $slug = mb_strtolower($slug, 'UTF-8');

        if ($unique) {
            $slug .= '-'.Str::lower(Str::random(4));
        }

        return $slug;
    }
}
