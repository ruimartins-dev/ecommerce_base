<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\ProductCreated;
use App\Events\ProductUpdated;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Encapsulates product persistence side-effects: image storage on the public
 * disk and synchronisation of the category/catalog pivots. Keeps the controller
 * thin and the image lifecycle (replace/delete) in one place.
 */
class ProductService
{
    private const IMAGE_DIRECTORY = 'products';

    /**
     * Create a product from validated data.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product
    {
        $product = new Product($this->attributes($data));

        if (($image = $data['image'] ?? null) instanceof UploadedFile) {
            $product->image_path = $image->store(self::IMAGE_DIRECTORY, 'public');
        }

        $product->save();

        $this->syncRelations($product, $data);

        ProductCreated::dispatch($product, Auth::id());

        return $product;
    }

    /**
     * Update a product from validated data.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product
    {
        $product->fill($this->attributes($data));

        if (($image = $data['image'] ?? null) instanceof UploadedFile) {
            $this->deleteImage($product);
            $product->image_path = $image->store(self::IMAGE_DIRECTORY, 'public');
        }

        $product->save();

        $this->syncRelations($product, $data);

        ProductUpdated::dispatch($product, Auth::id());

        return $product;
    }

    /**
     * Remove a product together with its stored image.
     */
    public function delete(Product $product): void
    {
        $this->deleteImage($product);

        $product->delete();
    }

    /**
     * Map validated input to the product's fillable columns.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data): array
    {
        return [
            'sku' => $data['sku'],
            'slug' => $data['slug'],
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'stock' => $data['stock'],
            'low_stock_threshold' => $data['low_stock_threshold'],
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }

    /**
     * Sync the many-to-many category and catalog assignments.
     *
     * @param  array<string, mixed>  $data
     */
    private function syncRelations(Product $product, array $data): void
    {
        $product->categories()->sync($data['categories'] ?? []);
        $product->catalogs()->sync($data['catalogs'] ?? []);
    }

    /**
     * Delete the product's current image from disk, if any.
     */
    private function deleteImage(Product $product): void
    {
        if ($product->image_path !== null && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }
    }
}

