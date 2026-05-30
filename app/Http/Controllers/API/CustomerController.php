<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerRequest;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * REST API for customers. Customer management (list, create, delete, editing
 * any account) is administrative only; a customer may view and update their own
 * profile. All of this is expressed by the CustomerPolicy. The linked user is
 * exposed without any credentials.
 */
class CustomerController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
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
            ->when($request->filled('blocked'), fn ($query) => $query->where('is_blocked', $request->boolean('blocked')))
            ->latest()
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return CustomerResource::collection($customers);
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $this->authorize('create', Customer::class);

        $customer = Customer::create($request->validated());

        return CustomerResource::make($customer->load('user'))
            ->response()
            ->setStatusCode(JsonResponse::HTTP_CREATED);
    }

    public function show(Customer $customer): CustomerResource
    {
        $this->authorize('view', $customer);

        return CustomerResource::make(
            $customer->load('user')->loadCount(['addresses', 'orders'])
        );
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): CustomerResource
    {
        $this->authorize('update', $customer);

        $customer->update($request->validated());

        return CustomerResource::make($customer->load('user'));
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $this->authorize('delete', $customer);

        $customer->delete();

        return response()->json(status: JsonResponse::HTTP_NO_CONTENT);
    }
}

