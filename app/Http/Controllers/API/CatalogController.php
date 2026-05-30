<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCatalogRequest;
use App\Http\Requests\Admin\UpdateCatalogRequest;
use App\Http\Resources\CatalogResource;
use App\Models\Catalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * REST API for catalogs. Catalog management is an administrative concern, so
 * every ability is gated by the CatalogPolicy (non-admins receive 403).
 * Validation reuses the existing admin Form Requests.
 */
class CatalogController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Catalog::class);

        $catalogs = Catalog::query()
            ->withCount('products')
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('active'), fn ($query) => $query->where('is_active', $request->boolean('active')))
            ->latest()
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return CatalogResource::collection($catalogs);
    }

    public function store(StoreCatalogRequest $request): JsonResponse
    {
        $this->authorize('create', Catalog::class);

        $catalog = Catalog::create($request->validated());

        return CatalogResource::make($catalog)
            ->response()
            ->setStatusCode(JsonResponse::HTTP_CREATED);
    }

    public function show(Catalog $catalog): CatalogResource
    {
        $this->authorize('view', $catalog);

        return CatalogResource::make($catalog->loadCount('products'));
    }

    public function update(UpdateCatalogRequest $request, Catalog $catalog): CatalogResource
    {
        $this->authorize('update', $catalog);

        $catalog->update($request->validated());

        return CatalogResource::make($catalog);
    }

    public function destroy(Catalog $catalog): JsonResponse
    {
        $this->authorize('delete', $catalog);

        $catalog->delete();

        return response()->json(status: JsonResponse::HTTP_NO_CONTENT);
    }
}

