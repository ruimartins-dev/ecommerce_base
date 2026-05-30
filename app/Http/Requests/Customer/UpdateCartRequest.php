<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer;

use App\Models\Product;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates an absolute quantity update for an existing cart line. The product
 * is resolved through route-model binding ({product} bound by slug), so we only
 * need to validate the quantity against that product's stock.
 */
class UpdateCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $product = $this->route('product');

            if ($product instanceof Product && $this->integer('quantity') > $product->stock) {
                $validator->errors()->add('quantity', __('Stock insuficiente'));
            }
        });
    }
}

