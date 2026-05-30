<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

/**
 * Authorization rules for categories.
 *
 * Reading the category tree is a catalog-browsing concern, so any authenticated
 * user may list and view categories (e.g. external API integrations and the
 * storefront). Writing categories is administrative only; administrators are
 * granted every ability globally through the Gate::before hook, so the write
 * methods below deny all other users.
 */
class CategoryPolicy
{
    /**
     * Any authenticated user may browse the category tree.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Any authenticated user may view an individual category.
     */
    public function view(User $user, Category $category): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Category $category): bool
    {
        return false;
    }

    public function delete(User $user, Category $category): bool
    {
        return false;
    }
}

