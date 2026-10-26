<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::updateOrCreate(
            ['slug' => 'aroma-diffusers'],
            [
                'name' => 'Aroma Diffusers',
                'is_active' => true,
            ]
        );
    }
}
