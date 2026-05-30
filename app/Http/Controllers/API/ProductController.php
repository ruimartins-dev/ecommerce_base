<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * REST API for products. Reads are available to any authenticated user (the
 * storefront catalog) while writes are administrative only, enforced through
 * the ProductPolicy. Validation and persistence reuse the existing admin Form
 * Requests and the ProductService, so no business rule is duplicated.
 */
class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products)
    {
    }

    /**
     * Paginated, filterable product listing.
     *
     * Filters: search (name/sku/description), sku, name, category (id or slug),
     * active. Non-admin callers only ever see active products.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Product::class);

        $isAdmin = $request->user()->isAdmin();

        $products = Product::query()
            ->with(['categories', 'catalogs'])
            // Inactive products are never exposed to non-admin integrations.
            ->when(! $isAdmin, fn ($query) => $query->active())
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%'.$request->string('search').'%';
                $query->where(fn ($q) => $q
                    ->where('name', 'like', $term)
                    ->orWhere('sku', 'like', $term)
                    ->orWhere('description', 'like', $term));
            })
            ->when($request->filled('sku'), fn ($query) => $query->where('sku', 'like', '%'.$request->string('sku').'%'))
            ->when($request->filled('name'), fn ($query) => $query->where('name', 'like', '%'.$request->string('name').'%'))
            ->when($request->filled('category'), function ($query) use ($request) {
                $category = $request->string('category')->value();
                $query->whereHas('categories', fn ($q) => $q
                    ->where('categories.slug', $category)
                    ->orWhere('categories.id', $category));
            })
            // The active filter is only honoured for administrators; non-admins
            // are already constrained to active products above.
            ->when($isAdmin && $request->filled('active'), fn ($query) => $query->where('is_active', $request->boolean('active')))
            ->latest()
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        $product = $this->products->create($request->validated());

        return ProductResource::make($product->load(['categories', 'catalogs']))
            ->response()
            ->setStatusCode(JsonResponse::HTTP_CREATED);
    }

    public function show(Request $request, Product $product): ProductResource
    {
        $this->authorize('view', $product);

        // Hide inactive products from non-admins even on direct access.
        abort_unless($product->is_active || $request->user()->isAdmin(), JsonResponse::HTTP_NOT_FOUND);

        return ProductResource::make($product->load(['categories', 'catalogs']));
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $this->authorize('update', $product);

        $product = $this->products->update($product, $request->validated());

        return ProductResource::make($product->load(['categories', 'catalogs']));
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $this->products->delete($product);

        return response()->json(status: JsonResponse::HTTP_NO_CONTENT);
    }
}

