<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\RoleEnum;
use App\Events\CustomerBlocked;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerRequest;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Customer::class);

        $customers = Customer::query()
            ->with('user')
            ->withCount(['addresses', 'orders'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%'.$request->string('search').'%';
                $query->where('company_name', 'like', $term)
                    ->orWhere('vat_number', 'like', $term)
                    ->orWhereHas('user', fn ($q) => $q->where('name', 'like', $term)->orWhere('email', 'like', $term));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('is_blocked', $request->string('status') === 'blocked'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function create(): View
    {
        $this->authorize('create', Customer::class);

        return view('admin.customers.create', [
            'customer' => new Customer(),
            'users' => $this->linkableUsers(),
        ]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $this->authorize('create', Customer::class);

        Customer::create($request->validated());

        return redirect()
            ->route('admin.customers.index')
            ->with('success', __('Customer created successfully.'));
    }

    public function edit(Customer $customer): View
    {
        $this->authorize('update', $customer);

        $customer->load('user');

        return view('admin.customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $this->authorize('update', $customer);

        $customer->update($request->validated());

        return redirect()
            ->route('admin.customers.index')
            ->with('success', __('Customer updated successfully.'));
    }

    /**
     * Block or unblock a customer account.
     */
    public function toggleBlock(Customer $customer): RedirectResponse
    {
        $this->authorize('update', $customer);

        $customer->update(['is_blocked' => ! $customer->is_blocked]);

        // Blocking a customer is an important action -> async audit trail.
        if ($customer->is_blocked) {
            CustomerBlocked::dispatch($customer, Auth::id());
        }

        return back()->with('success', $customer->is_blocked
            ? __('Customer blocked.')
            : __('Customer unblocked.'));
    }

    /**
     * Users that hold the customer role and do not yet own a customer profile.
     *
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function linkableUsers(): \Illuminate\Support\Collection
    {
        return User::query()
            ->whereHas('role', fn ($query) => $query->where('slug', RoleEnum::Customer->value))
            ->whereDoesntHave('customer')
            ->orderBy('name')
            ->get();
    }
}

