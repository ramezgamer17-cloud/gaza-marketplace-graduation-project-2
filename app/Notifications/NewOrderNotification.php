<?php

namespace App\Notifications;

use App\Models\Order;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $isVendor = $notifiable instanceof \App\Models\User && $notifiable->id === $this->order->vendor_id;

        return [
            'title' => 'طلب جديد '.$this->order->order_number,
            'body' => ($isVendor ? 'من ' : 'الزبون ').$this->order->customer_name.' — '.Money::format((float) $this->order->total),
            'icon' => 'heroicon-o-shopping-cart',
            'color' => 'success',
            'actions' => [
                [
                    'name' => 'view',
                    'label' => $isVendor ? 'عرض الطلب' : 'إدارة الطلب',
                    'url' => $isVendor
                        ? '/vendor/orders/'.$this->order->id.'/edit'
                        : '/admin/orders/'.$this->order->id.'/edit',
                    'color' => 'primary',
                ],
            ],
        ];
    }
}
