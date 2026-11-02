<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderStatusChanged extends Notification
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
        $label = Order::statusLabels()[$this->order->status] ?? $this->order->status;

        return [
            'title' => 'تحديث على طلبك '.$this->order->order_number,
            'body' => 'الحالة الجديدة: '.$label,
            'icon' => 'heroicon-o-clipboard-document-check',
            'color' => 'info',
            'actions' => [
                [
                    'name' => 'view',
                    'label' => 'تتبّع الطلب',
                    'url' => '/orders/'.$this->order->id.'/tracking',
                    'color' => 'primary',
                ],
            ],
        ];
    }
}
