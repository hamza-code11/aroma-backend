<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BannerCms;

class BannerCmsSeeder extends Seeder
{
    public function run(): void
    {
        BannerCms::updateOrCreate([], [
            'image' => null,
            'cards' => [
                [
                    'id' => 'b-1',
                    'label' => 'Signature Collection',
                    'title' => 'Pure Essence Crafted To Linger',
                    'description' => 'Each fragrance is blended from carefully selected ingredients to leave a lasting impression.',
                ],
                [
                    'id' => 'b-2',
                    'label' => 'The Craft',
                    'title' => 'Rare Ingredients Patiently Aged',
                    'description' => 'Oud, amber and saffron are sourced with care and matured slowly.',
                ],
                [
                    'id' => 'b-3',
                    'label' => 'The Experience',
                    'title' => 'Made To Be Remembered',
                    'description' => 'From a quiet morning to a long evening, find the scent that moves with you.',
                ],
            ],
        ]);
    }
}
