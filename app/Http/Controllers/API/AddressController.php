<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\StoreAddressApiRequest;
use App\Http\Requests\API\UpdateAddressApiRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * REST API for addresses. Access is ownership based (AddressPolicy): a customer
 * only ever sees and mutates their own addresses, administrators manage all.
 * The "single default address per customer" invariant is preserved by reusing
 * the AddressService.
 */
class AddressController extends Controller
{
    public function __construct(private readonly AddressService $addresses)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Address::class);

        $user = $request->user();

        $addresses = Address::query()
            ->with('customer.user')
            // Non-admins are restricted to their own customer's addresses.
            ->when(! $user->isAdmin(), fn ($query) => $query->where('customer_id', $user->customer?->id))
            // Admins may optionally narrow by customer.
            ->when($user->isAdmin() && $request->filled('customer'), fn ($query) => $query->where('customer_id', $request->integer('customer')))
            ->latest()
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return AddressResource::collection($addresses);
    }

    public function store(StoreAddressApiRequest $request): JsonResponse
    {
        $this->authorize('create', Address::class);

        $address = $this->addresses->create($request->validated());

        return AddressResource::make($address->load('customer'))
            ->response()
            ->setStatusCode(JsonResponse::HTTP_CREATED);
    }

    public function show(Address $address): AddressResource
    {
        $this->authorize('view', $address);

        return AddressResource::make($address->load('customer'));
    }

    public function update(UpdateAddressApiRequest $request, Address $address): AddressResource
    {
        $this->authorize('update', $address);

        $address = $this->addresses->update($address, $request->validated());

        return AddressResource::make($address->load('customer'));
    }

    public function destroy(Address $address): JsonResponse
    {
        $this->authorize('delete', $address);

        $this->addresses->delete($address);

        return response()->json(status: JsonResponse::HTTP_NO_CONTENT);
    }
}

