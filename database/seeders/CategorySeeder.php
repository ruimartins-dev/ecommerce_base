<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Seed a nested category tree (two levels deep).
     *
     * @var array<string, list<string>>
     */
    private array $tree = [
        'Industrial Equipment' => ['Power Tools', 'Hand Tools', 'Compressors'],
        'Electrical' => ['Cables', 'Lighting', 'Switchgear'],
        'Safety & Workwear' => ['Gloves', 'Helmets', 'High-Visibility Clothing'],
        'Office Supplies' => ['Paper', 'Writing Instruments'],
    ];

    /**
     * Seed the categories.
     */
    public function run(): void
    {
        $sort = 0;

        foreach ($this->tree as $parentName => $children) {
            $parent = Category::firstOrCreate(
                ['slug' => Str::slug($parentName)],
                [
                    'parent_id' => null,
                    'name' => $parentName,
                    'description' => "{$parentName} product range.",
                    'is_active' => true,
                    'sort_order' => $sort++,
                ],
            );

            $childSort = 0;

            foreach ($children as $childName) {
                Category::firstOrCreate(
                    ['slug' => Str::slug($parentName.' '.$childName)],
                    [
                        'parent_id' => $parent->id,
                        'name' => $childName,
                        'description' => "{$childName} within {$parentName}.",
                        'is_active' => true,
                        'sort_order' => $childSort++,
                    ],
                );
            }
        }
    }
}

