<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Storefront catalogue: only products visible to customers (active and in
     * an active catalog), with name/SKU search and a category filter.
     */
    public function index(Request $request): View
    {
        $products = Product::query()
            ->visible()
            ->with('categories')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $term = $request->string('search')->value();

                $query->where(function ($query) use ($term): void {
                    $query->where('name', 'like', '%'.$term.'%')
                        ->orWhere('sku', 'like', '%'.$term.'%');
                });
            })
            ->when($request->filled('category'), function ($query) use ($request): void {
                $query->whereHas('categories', fn ($category) => $category
                    ->where('categories.id', $request->integer('category'))
                    ->where('is_active', true));
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('customer.products.index', [
            'products' => $products,
            'categories' => Category::query()->active()->orderBy('name')->get(),
        ]);
    }

    /**
     * Product detail page. Hidden (inactive / non-active-catalog) products are
     * never reachable, even by direct slug.
     */
    public function show(Product $product): View
    {
        abort_unless(
            Product::query()->visible()->whereKey($product->getKey())->exists(),
            404,
        );

        $product->load('categories');

        return view('customer.products.show', compact('product'));
    }
}

