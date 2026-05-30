<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\OrderStatusEnum;
use App\Events\OrderStatusChanged;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * Focused tests for the order status state-machine service, isolated from the
 * HTTP/validation layer. The legal-transition rules live in OrderStatusEnum; the
 * service enforces them, stamps timestamps and fans out the domain event.
 */
class OrderStatusServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): OrderStatusService
    {
        return app(OrderStatusService::class);
    }

    public function test_valid_transition_updates_status_and_dispatches_event(): void
    {
        Event::fake([OrderStatusChanged::class]);

        $order = Order::factory()->status(OrderStatusEnum::Pending)->create();

        $result = $this->service()->transition($order, OrderStatusEnum::Confirmed);

        $this->assertSame(OrderStatusEnum::Confirmed, $result->status);
        $this->assertSame(OrderStatusEnum::Confirmed, $order->fresh()->status);

        Event::assertDispatched(
            OrderStatusChanged::class,
            fn (OrderStatusChanged $event): bool => $event->order->is($order)
                && $event->from === OrderStatusEnum::Pending
                && $event->to === OrderStatusEnum::Confirmed,
        );
    }

    public function test_leaving_pending_stamps_placed_at_when_missing(): void
    {
        Event::fake([OrderStatusChanged::class]);

        $order = Order::factory()->status(OrderStatusEnum::Pending)->create(['placed_at' => null]);

        $this->service()->transition($order, OrderStatusEnum::Confirmed);

        $this->assertNotNull($order->fresh()->placed_at);
    }

    public function test_same_status_is_a_noop_and_dispatches_no_event(): void
    {
        Event::fake([OrderStatusChanged::class]);

        $order = Order::factory()->status(OrderStatusEnum::Confirmed)->create();

        $result = $this->service()->transition($order, OrderStatusEnum::Confirmed);

        $this->assertTrue($result->is($order));
        $this->assertSame(OrderStatusEnum::Confirmed, $order->fresh()->status);
        Event::assertNotDispatched(OrderStatusChanged::class);
    }

    public function test_illegal_transition_throws_and_leaves_order_untouched(): void
    {
        Event::fake([OrderStatusChanged::class]);

        $order = Order::factory()->status(OrderStatusEnum::Completed)->create();

        try {
            $this->service()->transition($order, OrderStatusEnum::Pending);
            $this->fail('Expected an InvalidArgumentException for an illegal transition.');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('Cannot transition order from completed to pending', $e->getMessage());
        }

        // The persisted status must be unchanged and no event may have fired.
        $this->assertSame(OrderStatusEnum::Completed, $order->fresh()->status);
        Event::assertNotDispatched(OrderStatusChanged::class);
    }

    public function test_cancelled_orders_cannot_be_reopened(): void
    {
        $order = Order::factory()->status(OrderStatusEnum::Cancelled)->create();

        $this->expectException(InvalidArgumentException::class);

        $this->service()->transition($order, OrderStatusEnum::Processing);
    }
}

