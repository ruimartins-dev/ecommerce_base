<?php

declare(strict_types=1);

namespace Tests\Feature\Async;

use App\Enums\OrderStatusEnum;
use App\Enums\RoleEnum;
use App\Events\OrderStatusChanged;
use App\Jobs\NotifyCustomerOrderStatusChangedJob;
use App\Jobs\RecordAuditLogJob;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderStatusChangedNotification;
use App\Services\OrderStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

/**
 * Covers the primary Phase 7 async use case: an order status change fans out to
 * a queued customer notification and a queued audit-log write via an event and
 * its listeners. The phpunit suite runs the queue with the "sync" driver, so
 * end-to-end assertions exercise the real job code paths.
 */
class OrderStatusAsyncTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->role(RoleEnum::Admin)->create();
    }

    public function test_changing_status_dispatches_the_domain_event(): void
    {
        Event::fake([OrderStatusChanged::class]);

        $order = Order::factory()->status(OrderStatusEnum::Pending)->create();

        app(OrderStatusService::class)->transition($order, OrderStatusEnum::Confirmed);

        Event::assertDispatched(
            OrderStatusChanged::class,
            fn (OrderStatusChanged $event): bool => $event->order->is($order)
                && $event->from === OrderStatusEnum::Pending
                && $event->to === OrderStatusEnum::Confirmed,
        );
    }

    public function test_no_event_is_dispatched_when_status_is_unchanged(): void
    {
        Event::fake([OrderStatusChanged::class]);

        $order = Order::factory()->status(OrderStatusEnum::Confirmed)->create();

        app(OrderStatusService::class)->transition($order, OrderStatusEnum::Confirmed);

        Event::assertNotDispatched(OrderStatusChanged::class);
    }

    public function test_status_change_queues_notification_and_audit_jobs(): void
    {
        Queue::fake();

        $order = Order::factory()->status(OrderStatusEnum::Pending)->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.orders.status.update', $order), [
                'status' => OrderStatusEnum::Confirmed->value,
            ])
            ->assertRedirect(route('admin.orders.show', $order));

        Queue::assertPushed(NotifyCustomerOrderStatusChangedJob::class, function (NotifyCustomerOrderStatusChangedJob $job) use ($order): bool {
            return $job->orderId === $order->id
                && $job->toStatus === OrderStatusEnum::Confirmed->value;
        });

        Queue::assertPushed(RecordAuditLogJob::class, function (RecordAuditLogJob $job) use ($order): bool {
            return $job->action === 'order.status_changed'
                && $job->entityId === (string) $order->id;
        });
    }

    public function test_customer_receives_a_notification_when_the_job_runs(): void
    {
        Notification::fake();

        $order = Order::factory()->status(OrderStatusEnum::Pending)->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.orders.status.update', $order), [
                'status' => OrderStatusEnum::Confirmed->value,
            ]);

        Notification::assertSentTo(
            $order->customer->user,
            OrderStatusChangedNotification::class,
        );
    }

    public function test_a_persistent_database_notification_is_created_end_to_end(): void
    {
        $order = Order::factory()->status(OrderStatusEnum::Pending)->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.orders.status.update', $order), [
                'status' => OrderStatusEnum::Confirmed->value,
            ]);

        $this->assertDatabaseHas('notifications', [
            'type' => OrderStatusChangedNotification::class,
            'notifiable_id' => $order->customer->user_id,
            'notifiable_type' => User::class,
        ]);
    }

    public function test_audit_log_is_persisted_when_status_changes(): void
    {
        $order = Order::factory()->status(OrderStatusEnum::Pending)->create();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->patch(route('admin.orders.status.update', $order), [
                'status' => OrderStatusEnum::Confirmed->value,
            ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'order.status_changed',
            'entity_type' => Order::class,
            'entity_id' => (string) $order->id,
            'user_id' => $admin->id,
        ]);
    }

    public function test_notification_job_is_safe_when_the_order_is_missing(): void
    {
        Notification::fake();

        // The order no longer exists by the time the worker runs the job.
        (new NotifyCustomerOrderStatusChangedJob(999999, 'pending', 'confirmed'))->handle();

        Notification::assertNothingSent();
    }

    public function test_failed_handler_does_not_throw(): void
    {
        $job = new NotifyCustomerOrderStatusChangedJob(1, 'pending', 'confirmed');

        // Simulates the worker calling failed() after exhausting retries.
        $job->failed(new RuntimeException('redis unavailable'));

        $this->assertTrue(true);
    }

    public function test_redis_queue_and_failed_jobs_are_configured(): void
    {
        $this->assertSame('redis', config('queue.connections.redis.driver'));
        $this->assertSame('database-uuids', config('queue.failed.driver'));
    }

    public function test_jobs_declare_retry_support(): void
    {
        $notify = new NotifyCustomerOrderStatusChangedJob(1, 'pending', 'confirmed');
        $audit = new RecordAuditLogJob('test.action', Order::class, '1', null, null);

        $this->assertSame(3, $notify->tries);
        $this->assertNotEmpty($notify->backoff);
        $this->assertSame(3, $audit->tries);
    }
}

