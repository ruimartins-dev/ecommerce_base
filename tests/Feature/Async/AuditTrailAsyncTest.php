<?php

declare(strict_types=1);

namespace Tests\Feature\Async;

use App\Enums\RoleEnum;
use App\Events\ProductCreated;
use App\Jobs\RecordAuditLogJob;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Covers the bonus async audit-logging pipeline shared by several domain events
 * (product create/update, customer blocked, order status changed).
 */
class AuditTrailAsyncTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->role(RoleEnum::Admin)->create();
    }

    public function test_auditable_event_queues_the_audit_job(): void
    {
        Queue::fake();

        ProductCreated::dispatch(Product::factory()->create(), null);

        Queue::assertPushed(RecordAuditLogJob::class);
    }

    public function test_product_created_event_is_persisted_to_the_audit_trail(): void
    {
        $product = Product::factory()->create();

        ProductCreated::dispatch($product, null);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'product.created',
            'entity_type' => Product::class,
            'entity_id' => (string) $product->id,
        ]);
    }

    public function test_blocking_a_customer_writes_an_audit_log(): void
    {
        $admin = $this->admin();
        $customer = Customer::factory()->create(['is_blocked' => false]);

        $this->actingAs($admin)
            ->patch(route('admin.customers.toggle-block', $customer))
            ->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'customer.blocked',
            'entity_type' => Customer::class,
            'entity_id' => (string) $customer->id,
            'user_id' => $admin->id,
        ]);
    }

    public function test_unblocking_a_customer_does_not_create_a_block_audit_log(): void
    {
        $admin = $this->admin();
        $customer = Customer::factory()->blocked()->create();

        $this->actingAs($admin)
            ->patch(route('admin.customers.toggle-block', $customer));

        $this->assertDatabaseMissing('audit_logs', [
            'action' => 'customer.blocked',
            'entity_id' => (string) $customer->id,
        ]);
    }

    public function test_record_audit_log_job_persists_metadata(): void
    {
        (new RecordAuditLogJob('test.action', Order::class, '42', null, ['from' => 'a', 'to' => 'b']))->handle();

        $log = \App\Models\AuditLog::query()->where('action', 'test.action')->firstOrFail();

        $this->assertSame(Order::class, $log->entity_type);
        $this->assertSame('42', $log->entity_id);
        $this->assertSame(['from' => 'a', 'to' => 'b'], $log->metadata);
    }

    public function test_admin_can_view_the_audit_log_page(): void
    {
        (new RecordAuditLogJob('product.created', Product::class, '1', null, null))->handle();

        $this->actingAs($this->admin())
            ->get(route('admin.audit-logs.index'))
            ->assertOk()
            ->assertSee('product.created');
    }
}

