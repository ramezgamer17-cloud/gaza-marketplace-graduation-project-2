<?php

namespace App\Services;

use App\Models\DeliveryCompany;
use App\Models\Order;

class DeliveryAssignment
{
    /**
     * إسناد الطلب تلقائيًا لشركة التوصيل المطابقة لمنطقة التوصيل.
     * الأولوية: شركة نشطة في نفس المنطقة، ثم الأقل طلبات نشطة.
     */
    public function assignCompany(Order $order): ?DeliveryCompany
    {
        if ($order->delivery_company_id) {
            return $order->deliveryCompany;
        }

        if (! $order->delivery_zone_id) {
            return null;
        }

        $companies = DeliveryCompany::query()
            ->where('is_active', true)
            ->where('zone_id', $order->delivery_zone_id)
            ->get();

        if ($companies->isEmpty()) {
            return null;
        }

        $company = $companies
            ->sortBy(fn (DeliveryCompany $company) => $company->orders()
                ->whereNotIn('status', [Order::STATUS_DELIVERED, Order::STATUS_CANCELLED])
                ->count())
            ->first();

        $order->update(['delivery_company_id' => $company->id]);

        return $company;
    }

    /**
     * إسناد الطلب تلقائيًا لمندوب متفرّغ في نفس المنطقة.
     * الأولوية: مندوبو الشركة المتفرغون → المندوبون المستقلون المتفرغون.
     * يعيد المندوب المُسند أو null إذا لم يتوفر مندوب متفرغ.
     */
    public function assignDriver(Order $order): ?\App\Models\User
    {
        if ($order->delivery_user_id) {
            return $order->deliveryUser;
        }

        if (! $order->delivery_zone_id) {
            return null;
        }

        $drivers = \App\Models\DeliveryProfile::query()
            ->where('type', \App\Models\DeliveryProfile::TYPE_DRIVER)
            ->where('is_active', true)
            ->where('zone_id', $order->delivery_zone_id)
            ->with('user')
            ->get()
            ->filter(fn (\App\Models\DeliveryProfile $profile) => $profile->user !== null);

        if ($drivers->isEmpty()) {
            return null;
        }

        $activeStatuses = [Order::STATUS_READY, Order::STATUS_SHIPPED, Order::STATUS_PREPARING];

        $busy = fn (int $userId) => Order::query()
            ->where('delivery_user_id', $userId)
            ->whereIn('status', $activeStatuses)
            ->count();

        $free = $drivers->filter(fn (\App\Models\DeliveryProfile $profile) => $busy($profile->user_id) === 0);

        $companyDriver = $free->first(fn (\App\Models\DeliveryProfile $profile) => $profile->company_id === $order->delivery_company_id);
        $independentDriver = $free->first(fn (\App\Models\DeliveryProfile $profile) => $profile->company_id === null);

        $chosen = $companyDriver ?? $independentDriver;

        if (! $chosen) {
            return null;
        }

        $order->update(['delivery_user_id' => $chosen->user_id]);

        return $chosen->user;
    }

    /**
     * إشعار مدير الشركة بالطلب المُسند.
     */
    public function notifyCompanyManager(Order $order): void
    {
        $manager = $order->deliveryCompany?->manager;

        if (! $manager) {
            return;
        }

        $manager->notify(new \App\Notifications\OrderAssignedToCompany($order));
    }
}
