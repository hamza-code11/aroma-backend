<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HeroCms;

class HeroCmsSeeder extends Seeder
{
    public function run(): void
    {
        HeroCms::updateOrCreate([], [
            'label' => 'Luxury Aroma Collection',
            'title' => 'Fragrances That Stay',
            'description' => 'Experience the pure essence of nature with our premium aroma diffusers. Handcrafted to bring freshness and serenity to your space.',
            'primary_btn_label' => 'Explore Collection',
            'primary_btn_link' => '/shop',
            'secondary_btn_label' => 'How It Works',
            'secondary_btn_link' => '/about',
        ]);
    }
}
