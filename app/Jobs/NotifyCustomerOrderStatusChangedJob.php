<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Asynchronously notifies the customer that their order changed status.
 *
 * Runs on the Redis queue worker, so the admin HTTP request that triggered the
 * status change is never blocked by notification delivery. The "meaningful"
 * side-effect is a persisted database notification on the customer's user.
 */
class NotifyCustomerOrderStatusChangedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of attempts before the job is moved to the failed_jobs table.
     */
    public int $tries = 3;

    /**
     * Progressive back-off (seconds) between retries.
     *
     * @var list<int>
     */
    public array $backoff = [10, 30, 60];

    public function __construct(
        public readonly int $orderId,
        public readonly string $fromStatus,
        public readonly string $toStatus,
    ) {
    }

    public function handle(): void
    {
        $order = Order::query()->with('customer.user')->find($this->orderId);

        $user = $order?->customer?->user;

        if ($user === null) {
            // Order or customer was removed before the job ran — nothing to do.
            return;
        }

        $user->notify(new OrderStatusChangedNotification(
            $order,
            OrderStatusEnum::from($this->fromStatus),
            OrderStatusEnum::from($this->toStatus),
        ));
    }

    /**
     * Called by the queue worker after the final attempt fails. The application
     * keeps running; the failure is logged and the job lands in failed_jobs.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Failed to notify customer of order status change.', [
            'order_id' => $this->orderId,
            'from' => $this->fromStatus,
            'to' => $this->toStatus,
            'exception' => $exception->getMessage(),
        ]);
    }
}

