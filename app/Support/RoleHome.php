<?php

namespace App\Support;

use App\Models\User;

class RoleHome
{
    /**
     * الصفحة التي يُوجَّه إليها المستخدم بعد تسجيل الدخول حسب دوره وحالته.
     */
    public static function url(?User $user): string
    {
        if (! $user) {
            return '/';
        }

        if ($user->hasRole('admin')) {
            return '/admin';
        }

        if ($user->hasRole('vendor')) {
            return '/vendor';
        }

        if ($user->hasRole('delivery_manager')) {
            $profile = $user->deliveryProfile;

            if (! $profile?->is_active || ! $profile?->company?->is_active) {
                return '/pending';
            }

            return '/company';
        }

        if ($user->hasRole('delivery')) {
            $profile = $user->deliveryProfile;

            if (! $profile?->is_active) {
                return '/pending';
            }

            return '/delivery';
        }

        return '/';
    }
}
