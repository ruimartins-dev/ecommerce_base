<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\CustomerBlocked;
use App\Events\OrderStatusChanged;
use App\Events\ProductCreated;
use App\Events\ProductUpdated;
use App\Listeners\HandleOrderStatusChanged;
use App\Listeners\RecordAuditLog;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Explicit event → listener wiring for the asynchronous (Phase 7) workflows.
 *
 * Event auto-discovery is disabled on purpose: the mapping below is the single,
 * obvious source of truth for how domain events drive queued side-effects.
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Primary async use case: notify the customer + write an audit entry.
        OrderStatusChanged::class => [
            HandleOrderStatusChanged::class,
            RecordAuditLog::class,
        ],

        // Bonus async audit trail for other important admin actions.
        ProductCreated::class => [RecordAuditLog::class],
        ProductUpdated::class => [RecordAuditLog::class],
        CustomerBlocked::class => [RecordAuditLog::class],
    ];

    /**
     * Keep wiring explicit and predictable.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

