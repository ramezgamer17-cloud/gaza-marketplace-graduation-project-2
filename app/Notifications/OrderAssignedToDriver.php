<?php

namespace App\Notifications;

use App\Models\Order;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderAssignedToDriver extends Notification
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
        return [
            'title' => 'طلب جديد بانتظارك: '.$this->order->order_number,
            'body' => $this->order->shipping_address.' — '.Money::format((float) $this->order->total),
            'icon' => 'heroicon-o-truck',
            'color' => 'success',
            'actions' => [
                [
                    'name' => 'view',
                    'label' => 'عرض الطلب',
                    'url' => '/delivery/orders/'.$this->order->id.'/edit',
                    'color' => 'primary',
                ],
            ],
        ];
    }
}
