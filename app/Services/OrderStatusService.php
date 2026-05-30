<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Events\OrderStatusChanged;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

/**
 * Applies validated order status transitions. The legal transition graph lives
 * in {@see OrderStatusEnum} so the rules are defined exactly once.
 */
class OrderStatusService
{
    /**
     * Transition the order to the given status.
     *
     * @throws InvalidArgumentException when the transition is not permitted.
     */
    public function transition(Order $order, OrderStatusEnum $target): Order
    {
        $current = $order->status;

        if ($current === $target) {
            return $order;
        }

        if (! $current->canTransitionTo($target)) {
            throw new InvalidArgumentException(
                "Cannot transition order from {$current->value} to {$target->value}."
            );
        }

        $order->status = $target;

        // Stamp the moment the order left the pending state, if not already set.
        if ($target !== OrderStatusEnum::Pending && $order->placed_at === null) {
            $order->placed_at = now();
        }

        $order->save();

        // Persist first, then fan out asynchronous side-effects (customer
        // notification + audit trail) via a single domain event. The HTTP
        // request returns immediately; the queued listeners/jobs do the work.
        OrderStatusChanged::dispatch($order, $current, $target, Auth::id());

        return $order;
    }
}

