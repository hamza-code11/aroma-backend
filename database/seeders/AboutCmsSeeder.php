<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AboutCms;

class AboutCmsSeeder extends Seeder
{
    public function run(): void
    {
        AboutCms::updateOrCreate([], [
            'hero' => [
                'eyebrow' => 'Our Story',
                'title' => 'Scent, crafted for',
                'titleHighlight' => 'every room',
                'lead' => 'i.Shine Aroma is a fragrance house built on a simple belief — that a room should feel like it has always smelled this way.',
                'primaryCta' => ['label' => 'Shop the Collection', 'href' => '/shop'],
                'secondaryCta' => ['label' => 'Contact Us', 'href' => '/contact'],
            ],
            'stats' => [
                ['value' => '12K+', 'label' => 'Happy Customers'],
                ['value' => '40+', 'label' => 'Signature Blends'],
                ['value' => '4.9', 'label' => 'Average Rating'],
                ['value' => '6', 'label' => 'Years of Craft'],
            ],
            'banner_image' => null,
            'story' => [
                'heading' => 'A house built on scent',
                'body' => [
                    'What began as a small evening ritual grew into something we couldn\'t ignore.',
                    'Every i.Shine Aroma blend is developed in small batches.',
                    'Today, our fragrances live in living rooms and bedrooms across the country.',
                ],
                'image' => null, // Static image path daal sakte hain
            ],
            'mission_vision' => [
                'mission' => [
                    'title' => 'Our Mission',
                    'text' => 'To craft premium, long-lasting fragrances that transform everyday spaces into experiences.',
                ],
                'vision' => [
                    'title' => 'Our Vision',
                    'text' => 'To become the most trusted fragrance house in the region.',
                ],
            ],
            'cta' => [
                'title' => 'Find the scent that belongs in your space.',
                'text' => 'Explore the full collection and bring home a fragrance crafted with intention.',
                'cta' => ['label' => 'Shop Now', 'href' => '/shop'],
            ],
        ]);
    }
}
