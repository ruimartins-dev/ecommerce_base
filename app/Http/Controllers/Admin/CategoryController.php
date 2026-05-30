<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Category::class);

        $categories = Category::query()
            ->with('parent')
            ->withCount(['children', 'products'])
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->string('status') === 'active'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        $this->authorize('create', Category::class);

        return view('admin.categories.create', [
            'category' => new Category(),
            'parents' => $this->parentOptions(),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', Category::class);

        Category::create($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('success', __('Category created successfully.'));
    }

    public function edit(Category $category): View
    {
        $this->authorize('update', $category);

        return view('admin.categories.edit', [
            'category' => $category,
            'parents' => $this->parentOptions($category),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $category->update($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('success', __('Category updated successfully.'));
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', __('Category deleted successfully.'));
    }

    /**
     * Candidate parent categories, excluding the category itself and its
     * descendants to keep the picker free of cycle-producing options.
     *
     * @return \Illuminate\Support\Collection<int, Category>
     */
    private function parentOptions(?Category $category = null): \Illuminate\Support\Collection
    {
        $excluded = $category
            ? array_merge([$category->id], $category->descendantIds())
            : [];

        return Category::query()
            ->when($excluded, fn ($query) => $query->whereNotIn('id', $excluded))
            ->orderBy('name')
            ->get();
    }
}

