<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the customer dashboard with a snapshot of recent orders.
     */
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $customer = $user->customer;

        /** @var Collection<int, \App\Models\Order> $recentOrders */
        $recentOrders = $customer
            ? $customer->orders()->latest('placed_at')->latest()->take(5)->get()
            : collect();

        return view('customer.dashboard', compact('recentOrders'));
    }
}

