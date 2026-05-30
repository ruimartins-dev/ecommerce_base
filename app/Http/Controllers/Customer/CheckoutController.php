<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Exceptions\CheckoutException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCheckoutRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Drives the (payment-less) checkout flow: review cart → pick address →
 * confirm. The controller is intentionally thin — all order-creation logic
 * lives in {@see CheckoutService} and all validation in
 * {@see StoreCheckoutRequest}.
 */
class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly CheckoutService $checkout,
    ) {
    }

    /**
     * Show the checkout review page: cart lines, totals and the customer's own
     * addresses (default preselected).
     */
    public function index(): View|RedirectResponse
    {
        $this->authorize('create', Order::class);

        if ($this->cart->isEmpty()) {
            return redirect()
                ->route('customer.cart.index')
                ->with('error', __('O carrinho está vazio'));
        }

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $customer = $user->customer;

        $addresses = $customer
            ?->addresses()
            ->orderByDesc('is_default')
            ->orderBy('label')
            ->get();

        return view('customer.checkout.index', [
            'items' => $this->cart->items(),
            'subtotal' => $this->cart->total(),
            'addresses' => $addresses,
            'defaultAddressId' => $customer?->default_address_id,
        ]);
    }

    /**
     * Confirm the order. Delegates to the service; a domain failure keeps the
     * cart and redirects back with the error, while success clears the cart and
     * lands the customer on their order history.
     */
    public function store(StoreCheckoutRequest $request): RedirectResponse
    {
        $this->authorize('create', Order::class);

        try {
            $this->checkout->place(
                $request->customer(),
                $request->address(),
            );
        } catch (CheckoutException $exception) {
            return redirect()
                ->route('customer.cart.index')
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('customer.orders.index')
            ->with('success', __('Encomenda criada com sucesso'));
    }
}



