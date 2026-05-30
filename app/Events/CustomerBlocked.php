<?php

declare(strict_types=1);

namespace App\Events;

use App\Contracts\Auditable;
use App\Models\Customer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when an administrator blocks a customer account.
 */
class CustomerBlocked implements Auditable
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Customer $customer,
        public readonly ?int $causerId = null,
    ) {
    }

    public function auditAction(): string
    {
        return 'customer.blocked';
    }

    public function auditEntityType(): string
    {
        return Customer::class;
    }

    public function auditEntityId(): string
    {
        return (string) $this->customer->getKey();
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
            'company_name' => $this->customer->company_name,
        ];
    }
}

