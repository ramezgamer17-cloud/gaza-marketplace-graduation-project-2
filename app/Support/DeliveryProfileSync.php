<?php

namespace App\Support;

use App\Models\DeliveryProfile;
use App\Models\User;

class DeliveryProfileSync
{
    /**
     * تجهيز ملف التوصيل تلقائيًا حسب دور المستخدم.
     */
    public static function sync(User $user): void
    {
        $isManager = $user->hasRole('delivery_manager');
        $isDriver = $user->hasRole('delivery');

        if (! $isManager && ! $isDriver) {
            return;
        }

        $type = $isManager ? DeliveryProfile::TYPE_MANAGER : DeliveryProfile::TYPE_DRIVER;

        $profile = DeliveryProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'type' => $type,
                'address' => $user->address,
                'is_active' => (bool) $user->is_active,
            ]
        );

        if ($profile->type !== $type) {
            $profile->update(['type' => $type]);
        }
    }
}
