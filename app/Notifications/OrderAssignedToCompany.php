<?php

namespace App\Notifications;

use App\Models\Order;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderAssignedToCompany extends Notification
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
            'title' => 'طلب جديد لشركتك '.$this->order->order_number,
            'body' => $this->order->deliveryZone?->name.' — '.Money::format((float) $this->order->total),
            'icon' => 'heroicon-o-truck',
            'color' => 'info',
            'actions' => [
                [
                    'name' => 'view',
                    'label' => 'إسناد الطلب',
                    'url' => '/company/orders/'.$this->order->id.'/edit',
                    'color' => 'primary',
                ],
            ],
        ];
    }
}
