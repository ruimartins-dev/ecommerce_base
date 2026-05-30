<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\AuditLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Asynchronously persists a single audit-trail entry.
 *
 * Decoupled from the originating action via an event + listener, so writing the
 * audit log never slows down the user-facing request.
 */
class RecordAuditLogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [10, 30, 60];

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public readonly string $action,
        public readonly string $entityType,
        public readonly ?string $entityId,
        public readonly ?int $userId,
        public readonly ?array $metadata = null,
    ) {
    }

    public function handle(): void
    {
        AuditLog::create([
            'action' => $this->action,
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
            'user_id' => $this->userId,
            'metadata' => $this->metadata,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Failed to persist audit log.', [
            'action' => $this->action,
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
            'exception' => $exception->getMessage(),
        ]);
    }
}

