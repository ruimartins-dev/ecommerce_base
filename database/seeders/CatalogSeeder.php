<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Catalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CatalogSeeder extends Seeder
{
    /**
     * Seed a set of B2B catalogs.
     */
    public function run(): void
    {
        $catalogs = [
            [
                'name' => 'General B2B Catalog',
                'description' => 'Standard wholesale catalog available to all active customers.',
                'is_active' => true,
                'starts_at' => Carbon::now()->subMonth(),
                'ends_at' => null,
            ],
            [
                'name' => 'Seasonal Promotions',
                'description' => 'Time-limited promotional pricing for selected products.',
                'is_active' => true,
                'starts_at' => Carbon::now()->startOfMonth(),
                'ends_at' => Carbon::now()->addMonths(2),
            ],
            [
                'name' => 'Clearance',
                'description' => 'Discontinued and end-of-life stock.',
                'is_active' => false,
                'starts_at' => null,
                'ends_at' => null,
            ],
        ];

        foreach ($catalogs as $catalog) {
            Catalog::firstOrCreate(['name' => $catalog['name']], $catalog);
        }
    }
}

