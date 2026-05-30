<?php

declare(strict_types=1);

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates address creation through the API while guaranteeing ownership:
 * a non-admin user may only create addresses for their own customer profile,
 * so the customer_id is forced to their own id and can never be spoofed.
 * Administrators may target any customer.
 */
class StoreAddressApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $user = $this->user();

        $merge = ['is_default' => $this->boolean('is_default')];

        // Non-admins can never address another customer's account.
        if ($user !== null && ! $user->isAdmin()) {
            $merge['customer_id'] = $user->customer?->id;
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

