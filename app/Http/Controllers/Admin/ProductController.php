<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Catalog;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::query()
            ->when($request->filled('sku'), fn ($query) => $query->where('sku', 'like', '%'.$request->string('sku').'%'))
            ->when($request->filled('name'), fn ($query) => $query->where('name', 'like', '%'.$request->string('name').'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->string('status') === 'active'))
            ->when($request->string('stock')->value() === 'out', fn ($query) => $query->where('stock', '<=', 0))
            ->when($request->string('stock')->value() === 'in', fn ($query) => $query->where('stock', '>', 0))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'product' => new Product(),
            'categories' => Category::orderBy('name')->get(),
            'catalogs' => Catalog::orderBy('name')->get(),
            'selectedCategories' => [],
            'selectedCatalogs' => [],
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->products->create($request->validated());

        return redirect()
            ->route('admin.products.index')
            ->with('success', __('Product created successfully.'));
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'catalogs' => Catalog::orderBy('name')->get(),
            'selectedCategories' => $product->categories()->pluck('categories.id')->all(),
            'selectedCatalogs' => $product->catalogs()->pluck('catalogs.id')->all(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->products->update($product, $request->validated());

        return redirect()
            ->route('admin.products.index')
            ->with('success', __('Product updated successfully.'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $this->products->delete($product);

        return redirect()
            ->route('admin.products.index')
            ->with('success', __('Product deleted successfully.'));
    }

    /**
     * Toggle the active state of a product.
     */
    public function toggleActive(Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $product->update(['is_active' => ! $product->is_active]);

        return back()->with('success', __('Product status updated.'));
    }
}

