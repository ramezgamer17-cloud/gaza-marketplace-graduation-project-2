<?php

namespace App\Support;

class Money
{
    /**
     * تنسيق المبالغ بالشيكل بصيغة عربية مقروءة
     * (رمز ₪ غير مدعوم في خط Cairo فيظهر مربعًا فارغًا).
     */
    public static function format(float|int|string|null $value, bool $withCurrency = true): string
    {
        $formatted = number_format((float) $value, 2);

        return $withCurrency ? $formatted.' شيكل' : $formatted;
    }
}
