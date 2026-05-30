<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Policies\CustomerPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    private array $policies = [
        Product::class => ProductPolicy::class,
        Order::class => OrderPolicy::class,
        Customer::class => CustomerPolicy::class,
        Catalog::class => CatalogPolicy::class,
        Category::class => CategoryPolicy::class,
        Address::class => AddressPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        $this->registerAdminBypass();
        $this->registerAuthenticatedRedirect();

        // Render Laravel's paginator with Bootstrap 5 markup so ->links()
        // matches the Bootstrap-based UI across the admin and customer areas.
        Paginator::useBootstrapFive();
    }

    /**
     * Map every model to its policy in one place.
     */
    private function registerPolicies(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }

    /**
     * Administrators implicitly pass every authorization check, so individual
     * policies never need to repeat admin handling. Returning null lets the
     * normal policy method decide for non-admin users.
     */
    private function registerAdminBypass(): void
    {
        Gate::before(static function (User $user): ?bool {
            return $user->isAdmin() ? true : null;
        });
    }

    /**
     * Send already-authenticated users who hit a guest-only route (e.g. the
     * login page) to their role-specific dashboard instead of a generic page.
     */
    private function registerAuthenticatedRedirect(): void
    {
        RedirectIfAuthenticated::redirectUsing(
            static fn (Request $request): string => route($request->user()->homeRoute())
        );
    }
}
