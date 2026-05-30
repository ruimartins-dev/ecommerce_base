<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer;

use App\Models\Address;
use App\Models\Customer;
use App\Models\Order;
use App\Services\CartService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the checkout confirmation. The route is already behind auth +
 * role:customer; here we additionally enforce (via the OrderPolicy) that the
 * user may place orders, that the chosen address belongs to that customer, and
 * that the cart is not empty. No checkout business rules leak into the
 * controller.
 */
class StoreCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Order::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $customer = $this->customer();

        return [
            // The scoped "exists" rule is what prevents a customer from
            // selecting another customer's address: the row must match both the
            // id AND their customer_id.
            'address_id' => [
                'required',
                'integer',
                Rule::exists('addresses', 'id')->where(
                    fn ($query) => $query->where('customer_id', $customer?->getKey())
                ),
            ],
        ];
    }

    /**
     * Block confirmation when the cart has emptied out (e.g. all products were
     * removed/pruned after reaching the checkout page).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (app(CartService::class)->isEmpty()) {
                $validator->errors()->add('cart', __('O carrinho está vazio'));
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'address_id.required' => __('Selecione um endereço de entrega.'),
            'address_id.exists' => __('Endereço inválido.'),
        ];
    }

    /**
     * The customer profile of the authenticated user.
     */
    public function customer(): ?Customer
    {
        return $this->user()?->customer;
    }

    /**
     * The validated, customer-owned address for the order.
     */
    public function address(): Address
    {
        return Address::query()->findOrFail($this->integer('address_id'));
    }
}

