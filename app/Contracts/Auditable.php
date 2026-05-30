<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Marks a domain event as something that should produce an audit-trail entry.
 *
 * The {@see \App\Listeners\RecordAuditLog} listener consumes every event that
 * implements this contract and pushes an {@see \App\Jobs\RecordAuditLogJob} onto
 * the queue, so persisting the log never blocks the originating HTTP request.
 */
interface Auditable
{
    /**
     * Machine-friendly action key, e.g. "order.status_changed".
     */
    public function auditAction(): string;

    /**
     * Fully-qualified class name of the affected entity.
     */
    public function auditEntityType(): string;

    /**
     * Primary key of the affected entity (string for forward compatibility).
     */
    public function auditEntityId(): string;

    /**
     * Id of the user who triggered the action, or null for system actions.
     */
    public function auditUserId(): ?int;

    /**
     * Arbitrary contextual payload persisted as JSON.
     *
     * @return array<string, mixed>|null
     */
    public function auditMetadata(): ?array;
}

