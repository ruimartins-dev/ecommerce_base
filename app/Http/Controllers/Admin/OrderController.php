<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Order::class);

        $orders = Order::query()
            ->with('customer.user')
            ->when($request->filled('search'), fn ($query) => $query->where('order_number', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'statuses' => OrderStatusEnum::cases(),
        ]);
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load(['customer.user', 'address', 'items.product']);

        return view('admin.orders.show', [
            'order' => $order,
            'allowedStatuses' => $order->status->allowedTransitions(),
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order, OrderStatusService $service): RedirectResponse
    {
        $this->authorize('update', $order);

        $service->transition($order, OrderStatusEnum::from($request->validated()['status']));

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', __('Order status updated.'));
    }
}

