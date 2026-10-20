<?php

namespace App\Support;

use App\Models\User;

class RoleHome
{
    /**
     * الصفحة التي يُوجَّه إليها المستخدم بعد تسجيل الدخول حسب دوره.
     */
    public static function url(?User $user): string
    {
        if (! $user) {
            return '/';
        }

        return match (true) {
            $user->hasRole('admin') => '/admin',
            $user->hasRole('vendor') => '/vendor',
            $user->hasRole('delivery') => '/delivery',
            default => '/',
        };
    }
}
