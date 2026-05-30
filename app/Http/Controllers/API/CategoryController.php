<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * REST API for categories. The hierarchy is browsable by any authenticated
 * user; writes are administrative only (CategoryPolicy). Circular parent
 * assignment is prevented by the reused UpdateCategoryRequest, which applies
 * the NotSelfOrDescendant rule.
 */
class CategoryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Category::class);

        $categories = Category::query()
            ->with('parent')
            ->withCount(['children', 'products'])
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('active'), fn ($query) => $query->where('is_active', $request->boolean('active')))
            ->when($request->boolean('roots'), fn ($query) => $query->root())
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', Category::class);

        $category = Category::create($request->validated());

        return CategoryResource::make($category)
            ->response()
            ->setStatusCode(JsonResponse::HTTP_CREATED);
    }

    public function show(Category $category): CategoryResource
    {
        $this->authorize('view', $category);

        return CategoryResource::make(
            $category->load(['parent', 'children'])->loadCount('products')
        );
    }

    public function update(UpdateCategoryRequest $request, Category $category): CategoryResource
    {
        $this->authorize('update', $category);

        $category->update($request->validated());

        return CategoryResource::make($category->load('parent'));
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->authorize('delete', $category);

        $category->delete();

        return response()->json(status: JsonResponse::HTTP_NO_CONTENT);
    }
}

