<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function (User $user, int $id): bool {
    return $user->id === $id;
});

/*
|--------------------------------------------------------------------------
| Private order channel (Phase 8 - realtime order status)
|--------------------------------------------------------------------------
| A subscription to "orders.{orderId}" is only authorized when the
| authenticated user owns the order, or is an administrator. Any other
| customer is rejected, so order events never leak across tenants.
*/
Broadcast::channel('orders.{orderId}', function (User $user, int $orderId): bool {
    $order = Order::query()->with('customer')->find($orderId);

    if ($order === null) {
        return false;
    }

    // Admins may observe any order; customers only their own.
    return $user->isAdmin() || $order->customer?->user_id === $user->id;
});
