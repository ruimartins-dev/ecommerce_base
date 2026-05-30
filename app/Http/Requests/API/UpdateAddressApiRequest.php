<?php

declare(strict_types=1);

namespace App\Http\Requests\API;

use App\Models\Address;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates address updates through the API. Ownership is enforced by the
 * AddressPolicy; here we additionally pin the customer_id so a non-admin can
 * never re-assign their address to a different customer.
 */
class UpdateAddressApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $user = $this->user();

        $merge = ['is_default' => $this->boolean('is_default')];

        if ($user !== null && ! $user->isAdmin()) {
            /** @var Address $address */
            $address = $this->route('address');
            // The owning customer is immutable for non-admins.
            $merge['customer_id'] = $address->customer_id;
        }

        $this->merge($merge);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')],
            'label' => ['nullable', 'string', 'max:255'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'vat_number' => ['nullable', 'string', 'max:255'],
            'is_default' => ['boolean'],
        ];
    }
}

