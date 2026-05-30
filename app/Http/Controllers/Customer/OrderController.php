<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * The authenticated customer's own order history.
     */
    public function index(): View
    {
        $this->authorize('viewAny', Order::class);

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $customer = $user->customer;

        $orders = Order::query()
            ->where('customer_id', $customer?->getKey())
            ->withCount('items')
            ->latest('placed_at')
            ->latest()
            ->paginate(10);

        return view('customer.orders.index', compact('orders'));
    }

    /**
     * A single order. Ownership is enforced by the OrderPolicy; the view relies
     * exclusively on the stored snapshots, never on live product data.
     */
    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load(['items', 'address']);

        return view('customer.orders.show', compact('order'));
    }
}


