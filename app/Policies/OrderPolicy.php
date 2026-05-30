<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

/**
 * Authorization rules for orders.
 *
 * Administrators are granted every ability globally through the
 * {@see \App\Providers\AppServiceProvider} Gate::before hook, so the methods
 * below only need to describe the customer (owner) perspective.
 */
class OrderPolicy
{
    /**
     * Any authenticated customer may list their own orders. The controller is
     * responsible for scoping the query to the current customer.
     */
    public function viewAny(User $user): bool
    {
        return $user->isCustomer();
    }

    /**
     * A customer may only view an order that belongs to their own account.
     */
    public function view(User $user, Order $order): bool
    {
        return $this->owns($user, $order);
    }

    /**
     * Only customers (with a customer profile) may place orders.
     */
    public function create(User $user): bool
    {
        return $user->customer !== null;
    }

    /**
     * A customer may update an order they own (e.g. while still pending).
     */
    public function update(User $user, Order $order): bool
    {
        return $this->owns($user, $order);
    }

    /**
     * Determine whether the user owns the given order.
     */
    protected function owns(User $user, Order $order): bool
    {
        return $user->customer !== null
            && $user->customer->id === $order->customer_id;
    }
}

