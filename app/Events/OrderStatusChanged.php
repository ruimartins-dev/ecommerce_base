<?php

declare(strict_types=1);

namespace App\Events;

use App\Contracts\Auditable;
use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired after an order successfully transitions from one status to another.
 *
 * This single domain event drives every side-effect of a status change so the
 * rules live in exactly one place:
 *   - asynchronous customer notification + audit trail (Phase 7 listeners), and
 *   - a realtime websocket broadcast to the order owner (Phase 8).
 *
 * Broadcasting is queued (ShouldBroadcast) on a *private* per-order channel, so
 * the admin HTTP request is never blocked and only the owning customer (or an
 * admin) can subscribe. The payload is deliberately minimal — no prices,
 * addresses or line items are ever exposed over the socket.
 */
class OrderStatusChanged implements Auditable, ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly OrderStatusEnum $from,
        public readonly OrderStatusEnum $to,
        public readonly ?int $causerId = null,
    ) {
    }

    /**
     * The private channel the event is broadcast on. Only the order owner (or
     * an admin) can authorize a subscription — see routes/channels.php.
     *
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('orders.'.$this->order->getKey()),
        ];
    }

    /**
     * Stable, framework-agnostic event name the frontend listens for.
     */
    public function broadcastAs(): string
    {
        return 'order.status.changed';
    }

    /**
     * Minimal, non-sensitive payload sent to the browser.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'order_id' => (int) $this->order->getKey(),
            'order_number' => $this->order->order_number,
            'status' => $this->to->value,
            'status_label' => $this->to->label(),
            'updated_at' => optional($this->order->updated_at)->toIso8601String(),
        ];
    }

    public function auditAction(): string
    {
        return 'order.status_changed';
    }

    public function auditEntityType(): string
    {
        return Order::class;
    }

    public function auditEntityId(): string
    {
        return (string) $this->order->getKey();
    }

    public function auditUserId(): ?int
    {
        return $this->causerId;
    }

    /**
     * @return array<string, mixed>
     */
    public function auditMetadata(): ?array
    {
        return [
            'order_number' => $this->order->order_number,
            'from' => $this->from->value,
            'to' => $this->to->value,
        ];
    }
}

