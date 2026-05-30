<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

/**
 * Authorization rules for customer profiles.
 *
 * Administrators bypass every check via the Gate::before hook, so these rules
 * describe what a customer may do with their *own* profile only.
 */
class CustomerPolicy
{
    /**
     * Listing every customer is an administrative concern only.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Creating customer profiles is an administrative concern only (admins
     * bypass via the Gate::before hook).
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * A customer may view their own profile.
     */
    public function view(User $user, Customer $customer): bool
    {
        return $this->owns($user, $customer);
    }

    /**
     * A customer may update their own profile.
     */
    public function update(User $user, Customer $customer): bool
    {
        return $this->owns($user, $customer);
    }

    /**
     * Deleting customer profiles is an administrative concern only (admins
     * bypass via the Gate::before hook).
     */
    public function delete(User $user, Customer $customer): bool
    {
        return false;
    }

    /**
     * Determine whether the user owns the given customer profile.
     */
    protected function owns(User $user, Customer $customer): bool
    {
        return $user->customer !== null
            && $user->customer->id === $customer->id;
    }
}

