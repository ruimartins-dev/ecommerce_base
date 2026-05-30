<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * API representation of a product. Exposes the computed stock state and
 * availability flags, plus categories/catalogs when eager-loaded.
 *
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'stock' => $this->stock,
            'low_stock_threshold' => $this->low_stock_threshold,
            'is_active' => $this->is_active,
            'is_out_of_stock' => $this->is_out_of_stock,
            'is_low_stock' => $this->is_low_stock,
            'image_url' => $this->image_path
                ? Storage::disk('public')->url($this->image_path)
                : null,
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'catalogs' => CatalogResource::collection($this->whenLoaded('catalogs')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

