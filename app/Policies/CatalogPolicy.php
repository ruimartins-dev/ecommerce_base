<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Catalog;
use App\Models\User;

/**
 * Authorization rules for catalogs.
 *
 * Catalog management is an administrative concern only. Administrators bypass
 * every check via the Gate::before hook in {@see \App\Providers\AppServiceProvider},
 * so each ability deliberately denies every non-admin user.
 */
class CatalogPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Catalog $catalog): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Catalog $catalog): bool
    {
        return false;
    }

    public function delete(User $user, Catalog $catalog): bool
    {
        return false;
    }
}

