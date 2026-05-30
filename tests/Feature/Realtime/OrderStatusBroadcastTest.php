<?php

declare(strict_types=1);

namespace Tests\Feature\Realtime;

use App\Enums\OrderStatusEnum;
use App\Enums\RoleEnum;
use App\Events\OrderStatusChanged;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderStatusService;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Phase 8 - realtime order status updates.
 *
 * Verifies the broadcast contract (private channel, event alias, minimal
 * payload) and that channel authorization only ever lets the owning customer
 * (or an admin) subscribe to an order channel.
 */
class OrderStatusBroadcastTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->role(RoleEnum::Admin)->create();
    }

    /**
     * The phpunit suite runs the "null" broadcaster, whose /broadcasting/auth
     * short-circuits without ever invoking the channel callback. To genuinely
     * exercise the authorization rules we switch to the reverb (pusher) driver,
     * which validates the channel and signs the response locally (no network).
     *
     * Channels are registered on the broadcaster that was the default at boot,
     * so after switching drivers we re-load routes/channels.php to register
     * them on the freshly selected broadcaster.
     */
    private function useRealtimeBroadcaster(): void
    {
        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'test-key',
            'broadcasting.connections.reverb.secret' => 'test-secret',
            'broadcasting.connections.reverb.app_id' => 'test-id',
        ]);

        require base_path('routes/channels.php');
    }

    public function test_event_is_broadcastable(): void
    {
        $order = Order::factory()->create();

        $event = new OrderStatusChanged(
            $order,
            OrderStatusEnum::Pending,
            OrderStatusEnum::Confirmed,
        );

        $this->assertInstanceOf(ShouldBroadcast::class, $event);
    }

    public function test_event_broadcasts_on_the_private_order_channel(): void
    {
        $order = Order::factory()->create();

        $event = new OrderStatusChanged(
            $order,
            OrderStatusEnum::Pending,
            OrderStatusEnum::Confirmed,
        );

        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        // PrivateChannel prefixes the name with "private-".
        $this->assertSame('private-orders.'.$order->id, $channels[0]->name);
    }

    public function test_event_uses_a_stable_broadcast_alias(): void
    {
        $order = Order::factory()->create();

        $event = new OrderStatusChanged(
            $order,
            OrderStatusEnum::Pending,
            OrderStatusEnum::Confirmed,
        );

        $this->assertSame('order.status.changed', $event->broadcastAs());
    }

    public function test_broadcast_payload_is_minimal_and_correct(): void
    {
        $order = Order::factory()->create(['order_number' => 'ORD-20260530-0001']);

        $event = new OrderStatusChanged(
            $order,
            OrderStatusEnum::Pending,
            OrderStatusEnum::Processing,
        );

        $payload = $event->broadcastWith();

        $this->assertSame([
            'order_id',
            'order_number',
            'status',
            'status_label',
            'updated_at',
        ], array_keys($payload));

        $this->assertSame($order->id, $payload['order_id']);
        $this->assertSame('ORD-20260530-0001', $payload['order_number']);
        $this->assertSame('processing', $payload['status']);
        $this->assertSame('Processing', $payload['status_label']);

        // Defensive: the socket payload must never leak financial data.
        $this->assertArrayNotHasKey('total', $payload);
        $this->assertArrayNotHasKey('subtotal', $payload);
    }

    public function test_transition_dispatches_the_broadcastable_event(): void
    {
        Event::fake([OrderStatusChanged::class]);

        $order = Order::factory()->status(OrderStatusEnum::Pending)->create();

        app(OrderStatusService::class)->transition($order, OrderStatusEnum::Confirmed);

        Event::assertDispatched(
            OrderStatusChanged::class,
            fn (OrderStatusChanged $event): bool => $event->order->is($order)
                && $event->to === OrderStatusEnum::Confirmed,
        );
    }

    public function test_owner_can_authorize_their_order_channel(): void
    {
        $this->useRealtimeBroadcaster();

        $order = Order::factory()->create();

        $this->actingAs($order->customer->user)
            ->postJson('/broadcasting/auth', [
                'socket_id' => '123.456',
                'channel_name' => 'private-orders.'.$order->id,
            ])
            ->assertOk();
    }

    public function test_admin_can_authorize_any_order_channel(): void
    {
        $this->useRealtimeBroadcaster();

        $order = Order::factory()->create();

        $this->actingAs($this->admin())
            ->postJson('/broadcasting/auth', [
                'socket_id' => '123.456',
                'channel_name' => 'private-orders.'.$order->id,
            ])
            ->assertOk();
    }

    public function test_customer_cannot_authorize_another_customers_order_channel(): void
    {
        $this->useRealtimeBroadcaster();

        $ownOrder = Order::factory()->create();
        $otherOrder = Order::factory()->create();

        $this->actingAs($ownOrder->customer->user)
            ->postJson('/broadcasting/auth', [
                'socket_id' => '123.456',
                'channel_name' => 'private-orders.'.$otherOrder->id,
            ])
            ->assertForbidden();
    }

    public function test_authorization_is_denied_for_a_missing_order(): void
    {
        $this->useRealtimeBroadcaster();

        $order = Order::factory()->create();

        $this->actingAs($order->customer->user)
            ->postJson('/broadcasting/auth', [
                'socket_id' => '123.456',
                'channel_name' => 'private-orders.999999',
            ])
            ->assertForbidden();
    }
}

