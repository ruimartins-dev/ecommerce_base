<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Browse visible products belonging to a category (including its
     * descendant categories, so a parent shows everything beneath it).
     */
    public function show(Category $category): View
    {
        abort_unless($category->is_active, 404);

        $categoryIds = array_merge([$category->getKey()], $category->descendantIds());

        $products = Product::query()
            ->visible()
            ->with('categories')
            ->whereHas('categories', fn ($query) => $query->whereIn('categories.id', $categoryIds))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('customer.categories.show', [
            'category' => $category,
            'products' => $products,
            'categories' => Category::query()->active()->orderBy('name')->get(),
        ]);
    }
}

