<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Support\SolarFloodLightSeoCatalog;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (SolarFloodLightSeoCatalog::primaryCategories() as $slug => $attributes) {
            Category::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $attributes['name'],
                    'meta_description' => $attributes['meta_description'],
                    'description' => $attributes['description'],
                    'parent_id' => null,
                ]
            );
        }
    }
}
