<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Notifications\Notification;

/**
 * Persistent customer notification produced when an order changes status.
 *
 * Delivered on the "database" channel only — it is dispatched from inside an
 * already-queued job ({@see \App\Jobs\NotifyCustomerOrderStatusChangedJob}), so
 * the notification itself does not need to implement ShouldQueue. The result is
 * a row in the `notifications` table the customer can later consume.
 */
class OrderStatusChangedNotification extends Notification
{
    public function __construct(
        public readonly Order $order,
        public readonly OrderStatusEnum $from,
        public readonly OrderStatusEnum $to,
    ) {
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'order_id' => $this->order->getKey(),
            'order_number' => $this->order->order_number,
            'from' => $this->from->value,
            'to' => $this->to->value,
            'message' => __('Order :number is now :status.', [
                'number' => $this->order->order_number,
                'status' => $this->to->label(),
            ]),
        ];
    }
}

