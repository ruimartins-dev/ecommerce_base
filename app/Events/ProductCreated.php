<?php

declare(strict_types=1);

namespace App\Events;

use App\Contracts\Auditable;
use App\Models\Product;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired after a product is created through the admin backoffice.
 */
class ProductCreated implements Auditable
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Product $product,
        public readonly ?int $causerId = null,
    ) {
    }

    public function auditAction(): string
    {
        return 'product.created';
    }

    public function auditEntityType(): string
    {
        return Product::class;
    }

    public function auditEntityId(): string
    {
        return (string) $this->product->getKey();
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
            'sku' => $this->product->sku,
            'name' => $this->product->name,
        ];
    }
}

