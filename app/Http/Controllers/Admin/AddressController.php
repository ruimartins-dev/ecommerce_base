<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAddressRequest;
use App\Http\Requests\Admin\UpdateAddressRequest;
use App\Models\Address;
use App\Models\Customer;
use App\Services\AddressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function __construct(private readonly AddressService $addresses)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Address::class);

        $addresses = Address::query()
            ->with('customer.user')
            ->when($request->filled('customer'), fn ($query) => $query->where('customer_id', $request->integer('customer')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%'.$request->string('search').'%';
                $query->where('recipient_name', 'like', $term)
                    ->orWhere('city', 'like', $term)
                    ->orWhere('postal_code', 'like', $term);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.addresses.index', [
            'addresses' => $addresses,
            'customers' => $this->customerOptions(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Address::class);

        return view('admin.addresses.create', [
            'address' => new Address(),
            'customers' => $this->customerOptions(),
        ]);
    }

    public function store(StoreAddressRequest $request): RedirectResponse
    {
        $this->authorize('create', Address::class);

        $this->addresses->create($request->validated());

        return redirect()
            ->route('admin.addresses.index')
            ->with('success', __('Address created successfully.'));
    }

    public function edit(Address $address): View
    {
        $this->authorize('update', $address);

        return view('admin.addresses.edit', [
            'address' => $address,
            'customers' => $this->customerOptions(),
        ]);
    }

    public function update(UpdateAddressRequest $request, Address $address): RedirectResponse
    {
        $this->authorize('update', $address);

        $this->addresses->update($address, $request->validated());

        return redirect()
            ->route('admin.addresses.index')
            ->with('success', __('Address updated successfully.'));
    }

    public function destroy(Address $address): RedirectResponse
    {
        $this->authorize('delete', $address);

        $this->addresses->delete($address);

        return redirect()
            ->route('admin.addresses.index')
            ->with('success', __('Address deleted successfully.'));
    }

    /**
     * Customers eligible to own an address, eager-loaded with their user.
     *
     * @return \Illuminate\Support\Collection<int, Customer>
     */
    private function customerOptions(): \Illuminate\Support\Collection
    {
        return Customer::query()->with('user')->orderBy('company_name')->get();
    }
}

