<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Jobs\NotifyCustomerOrderStatusChangedJob;

/**
 * Reacts to {@see OrderStatusChanged} by pushing the customer-notification work
 * onto the queue. The listener stays intentionally thin: it contains no business
 * logic, it only decouples the event from the asynchronous job.
 */
class HandleOrderStatusChanged
{
    public function handle(OrderStatusChanged $event): void
    {
        NotifyCustomerOrderStatusChangedJob::dispatch(
            (int) $event->order->getKey(),
            $event->from->value,
            $event->to->value,
        );
    }
}

