<?php

declare(strict_types=1);

namespace Tests\Unit\Products;

use App\Models\Product;
use Tests\TestCase;

/**
 * The stock-availability accessors are pure, computed attributes (never stored)
 * and underpin both the storefront "out of stock" badge and the API resource.
 * They are exercised here without touching the database.
 */
class ProductAvailabilityTest extends TestCase
{
    private function product(int $stock, int $threshold = 5): Product
    {
        return new Product([
            'stock' => $stock,
            'low_stock_threshold' => $threshold,
        ]);
    }

    public function test_zero_stock_is_out_of_stock(): void
    {
        $this->assertTrue($this->product(0)->is_out_of_stock);
    }

    public function test_negative_stock_is_also_out_of_stock(): void
    {
        // Defensive: should never persist, but the accessor must not report a
        // negative-stock product as available.
        $this->assertTrue($this->product(-3)->is_out_of_stock);
    }

    public function test_positive_stock_is_not_out_of_stock(): void
    {
        $this->assertFalse($this->product(1)->is_out_of_stock);
    }

    public function test_stock_at_or_below_threshold_is_low_stock(): void
    {
        $this->assertTrue($this->product(5, 5)->is_low_stock);
        $this->assertTrue($this->product(3, 5)->is_low_stock);
        $this->assertTrue($this->product(1, 5)->is_low_stock);
    }

    public function test_stock_above_threshold_is_not_low_stock(): void
    {
        $this->assertFalse($this->product(6, 5)->is_low_stock);
        $this->assertFalse($this->product(100, 5)->is_low_stock);
    }

    public function test_out_of_stock_is_never_reported_as_low_stock(): void
    {
        // 0 stock is "out of stock", not "low stock" — the two states are
        // mutually exclusive.
        $product = $this->product(0, 5);

        $this->assertTrue($product->is_out_of_stock);
        $this->assertFalse($product->is_low_stock);
    }
}

