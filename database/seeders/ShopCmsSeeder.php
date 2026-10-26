<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShopCms;

class ShopCmsSeeder extends Seeder
{
    public function run(): void
    {
        ShopCms::updateOrCreate([], [
            'eyebrow' => 'The Collection',
            'title' => 'Find your signature scent',
            'description' => 'Explore our premium diffusers and fragrances, crafted for every room in your home.',
            'banner_image' => null, 
        ]);
    }
}
