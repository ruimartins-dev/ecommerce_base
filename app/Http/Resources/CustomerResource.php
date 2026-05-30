<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a customer profile. The linked user is exposed without
 * any sensitive credentials.
 *
 * @mixin Customer
 */
class CustomerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'vat_number' => $this->vat_number,
            'phone' => $this->phone,
            'is_blocked' => $this->is_blocked,
            'default_address_id' => $this->default_address_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'addresses' => AddressResource::collection($this->whenLoaded('addresses')),
            'addresses_count' => $this->whenCounted('addresses'),
            'orders_count' => $this->whenCounted('orders'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

