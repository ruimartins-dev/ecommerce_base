<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Order matters: roles -> users/customers -> catalog/category -> products.
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            AdminSeeder::class,
            CustomerSeeder::class,
            CatalogSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
}
