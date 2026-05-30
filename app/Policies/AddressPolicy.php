<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Address;
use App\Models\User;

/**
 * Authorization rules for addresses.
 *
 * Administrators manage every customer address and bypass these checks via the
 * Gate::before hook. For non-admins (API customers) access is ownership based:
 * a customer may only see and mutate addresses belonging to their own customer
 * profile. The index query is additionally scoped in the controller so a
 * customer never receives another account's addresses.
 */
class AddressPolicy
{
    /**
     * Any authenticated user may list addresses; the controller scopes the
     * result set to the caller's own addresses for non-admins.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Address $address): bool
    {
        return $this->owns($user, $address);
    }

    /**
     * Any authenticated user may create an address; the request forces the
     * customer_id to the caller's own profile for non-admins.
     */
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Address $address): bool
    {
        return $this->owns($user, $address);
    }

    public function delete(User $user, Address $address): bool
    {
        return $this->owns($user, $address);
    }

    /**
     * Determine whether the user owns the given address (through their
     * customer profile).
     */
    protected function owns(User $user, Address $address): bool
    {
        return $user->customer !== null
            && $user->customer->id === $address->customer_id;
    }
}

