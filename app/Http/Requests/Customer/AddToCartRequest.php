<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates an "add to cart" request. The route is already restricted to
 * authenticated customers, so authorize() simply returns true and all
 * business validation (visibility, stock, accumulated quantity) lives here
 * rather than in the controller.
 */
class AddToCartRequest extends FormRequest
{
    private ?Product $product = null;

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
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Add cross-field checks once the basic rules pass: the product must be
     * visible in the storefront and there must be enough stock for the
     * requested quantity plus whatever is already in the cart.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $product = $this->product();

            if ($product === null) {
                return; // The "exists" rule already reported the problem.
            }

            if (! Product::query()->visible()->whereKey($product->getKey())->exists()) {
                $validator->errors()->add('product_id', __('Este produto não está disponível.'));

                return;
            }

            if ($product->is_out_of_stock) {
                $validator->errors()->add('quantity', __('Stock insuficiente'));

                return;
            }

            $requested = (int) $this->integer('quantity');
            $alreadyInCart = app(CartService::class)->quantityFor((int) $product->getKey());

            if ($requested + $alreadyInCart > $product->stock) {
                $validator->errors()->add('quantity', __('Stock insuficiente'));
            }
        });
    }

    /**
     * The resolved product for the request (memoised).
     */
    public function product(): ?Product
    {
        return $this->product ??= Product::query()->find($this->integer('product_id'));
    }
}

