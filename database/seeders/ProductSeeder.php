<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Catalog;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed products and attach them to categories and catalogs.
     */
    public function run(): void
    {
        $leafCategories = Category::query()->whereNotNull('parent_id')->get();
        $catalogs = Catalog::all();

        if ($leafCategories->isEmpty() || $catalogs->isEmpty()) {
            return;
        }

        $generalCatalog = $catalogs->firstWhere('name', 'General B2B Catalog') ?? $catalogs->first();

        // In-stock, active products spread across the catalog.
        Product::factory()
            ->count(40)
            ->active()
            ->create()
            ->each(function (Product $product) use ($leafCategories, $catalogs, $generalCatalog): void {
                $product->categories()->sync(
                    $leafCategories->random(fake()->numberBetween(1, 2))->pluck('id')->all(),
                );

                $product->catalogs()->sync(
                    collect([$generalCatalog->id])
                        ->merge($catalogs->random(fake()->numberBetween(0, 1))->pluck('id'))
                        ->unique()
                        ->all(),
                );
            });

        // Explicitly out-of-stock products (future UI will show "Esgotado").
        Product::factory()
            ->count(8)
            ->active()
            ->outOfStock()
            ->create()
            ->each(function (Product $product) use ($leafCategories, $generalCatalog): void {
                $product->categories()->sync(
                    $leafCategories->random(1)->pluck('id')->all(),
                );
                $product->catalogs()->sync([$generalCatalog->id]);
            });

        // A few low-stock products to exercise the low-stock threshold.
        Product::factory()
            ->count(6)
            ->active()
            ->create(['stock' => 2, 'low_stock_threshold' => 5])
            ->each(function (Product $product) use ($leafCategories, $generalCatalog): void {
                $product->categories()->sync(
                    $leafCategories->random(1)->pluck('id')->all(),
                );
                $product->catalogs()->sync([$generalCatalog->id]);
            });
    }
}

