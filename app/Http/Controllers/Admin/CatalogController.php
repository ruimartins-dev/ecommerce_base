<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignCatalogProductsRequest;
use App\Http\Requests\Admin\StoreCatalogRequest;
use App\Http\Requests\Admin\UpdateCatalogRequest;
use App\Models\Catalog;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Catalog::class);

        $catalogs = Catalog::query()
            ->withCount('products')
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->string('status') === 'active'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.catalogs.index', compact('catalogs'));
    }

    public function create(): View
    {
        $this->authorize('create', Catalog::class);

        return view('admin.catalogs.create', ['catalog' => new Catalog()]);
    }

    public function store(StoreCatalogRequest $request): RedirectResponse
    {
        $this->authorize('create', Catalog::class);

        Catalog::create($request->validated());

        return redirect()
            ->route('admin.catalogs.index')
            ->with('success', __('Catalog created successfully.'));
    }

    public function edit(Catalog $catalog): View
    {
        $this->authorize('update', $catalog);

        return view('admin.catalogs.edit', compact('catalog'));
    }

    public function update(UpdateCatalogRequest $request, Catalog $catalog): RedirectResponse
    {
        $this->authorize('update', $catalog);

        $catalog->update($request->validated());

        return redirect()
            ->route('admin.catalogs.index')
            ->with('success', __('Catalog updated successfully.'));
    }

    public function destroy(Catalog $catalog): RedirectResponse
    {
        $this->authorize('delete', $catalog);

        $catalog->delete();

        return redirect()
            ->route('admin.catalogs.index')
            ->with('success', __('Catalog deleted successfully.'));
    }

    /**
     * Toggle the active state of a catalog.
     */
    public function toggleActive(Catalog $catalog): RedirectResponse
    {
        $this->authorize('update', $catalog);

        $catalog->update(['is_active' => ! $catalog->is_active]);

        return back()->with('success', __('Catalog status updated.'));
    }

    /**
     * Show the product assignment screen for a catalog.
     */
    public function editProducts(Catalog $catalog): View
    {
        $this->authorize('update', $catalog);

        $products = Product::query()->orderBy('name')->get();
        $assigned = $catalog->products()->pluck('products.id')->all();

        return view('admin.catalogs.products', compact('catalog', 'products', 'assigned'));
    }

    /**
     * Persist the catalog's product assignments.
     */
    public function updateProducts(AssignCatalogProductsRequest $request, Catalog $catalog): RedirectResponse
    {
        $this->authorize('update', $catalog);

        $catalog->products()->sync($request->validated()['products'] ?? []);

        return redirect()
            ->route('admin.catalogs.index')
            ->with('success', __('Catalog products updated.'));
    }
}

