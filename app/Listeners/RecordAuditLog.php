<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Contracts\Auditable;
use App\Jobs\RecordAuditLogJob;

/**
 * Generic audit listener: handles every event implementing {@see Auditable}
 * and queues the persistence work so the audit trail never blocks the request.
 */
class RecordAuditLog
{
    public function handle(Auditable $event): void
    {
        RecordAuditLogJob::dispatch(
            $event->auditAction(),
            $event->auditEntityType(),
            $event->auditEntityId(),
            $event->auditUserId(),
            $event->auditMetadata(),
        );
    }
}

