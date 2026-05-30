<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\AddToCartRequest;
use App\Http\Requests\Customer\UpdateCartRequest;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart)
    {
    }

    /**
     * Show the cart contents and running total.
     */
    public function index(): View
    {
        return view('customer.cart.index', [
            'items' => $this->cart->items(),
            'total' => $this->cart->total(),
        ]);
    }

    /**
     * Add a (validated) product/quantity to the cart.
     */
    public function store(AddToCartRequest $request): RedirectResponse
    {
        /** @var Product $product */
        $product = $request->product();

        $this->cart->add($product, (int) $request->integer('quantity'));

        return back()->with('success', __('Produto adicionado ao carrinho'));
    }

    /**
     * Set an absolute quantity for a cart line.
     */
    public function update(UpdateCartRequest $request, Product $product): RedirectResponse
    {
        $this->cart->update($product, (int) $request->integer('quantity'));

        return redirect()
            ->route('customer.cart.index')
            ->with('success', __('Carrinho atualizado'));
    }

    /**
     * Remove a single product from the cart.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $this->cart->remove($product);

        return redirect()
            ->route('customer.cart.index')
            ->with('success', __('Produto removido'));
    }

    /**
     * Empty the cart.
     */
    public function clear(): RedirectResponse
    {
        $this->cart->clear();

        return redirect()
            ->route('customer.cart.index')
            ->with('success', __('Carrinho esvaziado'));
    }
}

