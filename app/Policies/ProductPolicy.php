<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

/**
 * Authorization rules for products.
 *
 * The catalog is readable by any authenticated user (customers browse it),
 * while write operations are reserved for administrators. Admins are granted
 * those abilities globally through the Gate::before hook, so the write methods
 * deliberately deny everyone else here.
 */
class ProductPolicy
{
    /**
     * Any authenticated user may browse the catalog.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Any authenticated user may view an individual product.
     */
    public function view(User $user, Product $product): bool
    {
        return true;
    }

    /**
     * Creating products is an administrative ability only.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Updating products is an administrative ability only.
     */
    public function update(User $user, Product $product): bool
    {
        return false;
    }

    /**
     * Deleting products is an administrative ability only.
     */
    public function delete(User $user, Product $product): bool
    {
        return false;
    }
}

