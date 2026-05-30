<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An immutable audit-trail entry describing an important domain action. Records
 * are written asynchronously by {@see \App\Jobs\RecordAuditLogJob} and never
 * updated, hence the disabled UPDATED_AT timestamp.
 */
class AuditLog extends Model
{
    /**
     * Audit entries are append-only: track creation time only.
     */
    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'action',
        'entity_type',
        'entity_id',
        'user_id',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /**
     * The user who triggered the action (nullable for system actions).
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to a particular action key.
     *
     * @param  Builder<AuditLog>  $query
     */
    public function scopeAction(Builder $query, string $action): void
    {
        $query->where('action', $action);
    }
}

