<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Address;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

/**
 * Encapsulates address persistence while guaranteeing the "single default
 * address per customer" invariant across both the addresses table and the
 * customer's `default_address_id` pointer.
 */
class AddressService
{
    /**
     * Create an address from validated data.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Address
    {
        return DB::transaction(function () use ($data): Address {
            $address = Address::create($this->attributes($data));

            if ($address->is_default) {
                $this->promoteToDefault($address);
            }

            return $address;
        });
    }

    /**
     * Update an address from validated data.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Address $address, array $data): Address
    {
        return DB::transaction(function () use ($address, $data): Address {
            $address->update($this->attributes($data));

            if ($address->is_default) {
                $this->promoteToDefault($address);
            } elseif ($address->customer->default_address_id === $address->id) {
                // The address was unset as default; clear the customer pointer.
                $address->customer->update(['default_address_id' => null]);
            }

            return $address;
        });
    }

    /**
     * Delete an address, clearing the customer pointer if it was the default.
     */
    public function delete(Address $address): void
    {
        DB::transaction(function () use ($address): void {
            if ($address->customer->default_address_id === $address->id) {
                $address->customer->update(['default_address_id' => null]);
            }

            $address->delete();
        });
    }

    /**
     * Make the given address the customer's sole default address.
     */
    private function promoteToDefault(Address $address): void
    {
        Address::query()
            ->where('customer_id', $address->customer_id)
            ->whereKeyNot($address->getKey())
            ->update(['is_default' => false]);

        $address->customer->update(['default_address_id' => $address->id]);
    }

    /**
     * Map validated input to the address columns.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data): array
    {
        return [
            'customer_id' => $data['customer_id'],
            'label' => $data['label'] ?? null,
            'recipient_name' => $data['recipient_name'],
            'company_name' => $data['company_name'] ?? null,
            'address_line_1' => $data['address_line_1'],
            'address_line_2' => $data['address_line_2'] ?? null,
            'postal_code' => $data['postal_code'],
            'city' => $data['city'],
            'country' => $data['country'],
            'vat_number' => $data['vat_number'] ?? null,
            'is_default' => (bool) ($data['is_default'] ?? false),
        ];
    }
}

